<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\CreatesApplication;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

/**
 * Whole-codebase regression guard for the class of bug found repeatedly this
 * session: a DB column is NOT NULL / unique / length-limited, but the
 * Filament form field for it has no matching ->required()/->unique()/
 * ->maxLength() rule — so the UI's own client-side validation passes and the
 * write blows up as an unhandled SQL 500 instead of a form error. Walks
 * every resource registered in the admin and company panels, resolves its
 * real (live, closure-evaluated) form schema, and cross-checks each field
 * against the actual DB column it writes to. Existing, deliberate gaps are
 * recorded in KNOWN_GAPS below rather than silently skipped, so drift there
 * is a one-line diff, not a silent hole.
 */
class FormDbConstraintAuditTest extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * "ResourceClass:field" => reason. Only add an entry here with a real
     * reason — this list is itself reviewed by this test's own diff, so it
     * can't grow silently.
     */
    private const array KNOWN_GAPS = [
        // Numbering's company scoping is a known architectural gap (see
        // project memory / CLAUDE.md) — the composite (company_id, type,
        // year, month) uniqueness on numberings isn't mirrored in the form
        // because company_id itself isn't reliably form-driven there.
        'Modules\Core\Filament\Admin\Resources\Numberings\NumberingResource:type' => 'composite uniqueness spans company_id, which the admin panel does not reliably scope per the known BelongsToCompany gap',
        // Deliberate: "Add Team Member" looks up an EXISTING user by email
        // on purpose — a uniqueness rule here would reject every valid
        // email, since a real user's email is by definition already taken.
        // See the comment on this field in ListCompanyUsers.php.
        'Modules\Core\Filament\Company\Resources\CompanyUsers\CompanyUserResource:email' => 'this field intentionally looks up an existing user, uniqueness would break its entire purpose',
    ];

    private User $superAdmin;

    private User $companyUser;

    private Company $company;

    /** @var list<string> */
    private array $violations = [];

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();

        /** @var Company $company */
        $company       = Company::factory()->create();
        $this->company = $company;

        /** @var User $superAdmin */
        $superAdmin       = User::factory()->create();
        $this->superAdmin = $superAdmin;
        $this->superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

        $this->companyUser = User::factory()->withCompany([
            'search_code' => 'AUDIT1',
            'name'        => 'Audit Co',
        ])->create();
        $this->companyUser->assignRole(UserRole::CUSTOMER_ADMIN->value);

        $this->withoutExceptionHandling();
    }

    #[Test]
    public function every_form_field_matches_its_db_column_constraints(): void
    {
        $this->auditPanel('admin', $this->superAdmin, null);
        $this->auditPanel('company', $this->companyUser, Company::query()->where('search_code', 'AUDIT1')->firstOrFail());

        $this->assertEmpty(
            $this->violations,
            "Form/DB constraint mismatches found:\n" . implode("\n", $this->violations)
        );
    }

    private function auditPanel(string $panelId, User $actingAs, ?Company $tenant): void
    {
        $panel = Filament::getPanel($panelId);
        Filament::setCurrentPanel($panel);

        if ($tenant) {
            Filament::setTenant($tenant, true);
            session(['current_company_id' => $tenant->id]);
        }

        foreach ($panel->getResources() as $resourceClass) {
            $this->auditResource($resourceClass, $actingAs, $tenant);
        }
    }

    private function auditResource(string $resourceClass, User $actingAs, ?Company $tenant): void
    {
        $pages     = $resourceClass::getPages();
        $indexPage = $pages['index'] ?? null;

        if ( ! $indexPage) {
            return;
        }

        $model = $resourceClass::getModel();
        $table = (new $model())->getTable();

        if ( ! DbSchema::hasTable($table)) {
            return;
        }

        $params = $tenant ? ['tenant' => Str::lower($tenant->search_code)] : [];

        try {
            $component = Livewire::actingAs($actingAs)->test($indexPage->getPage(), $params);
        } catch (Throwable $e) {
            // A resource that can't even boot its index page isn't this
            // test's concern (covered by that resource's own tests) — skip
            // rather than false-alarm on an unrelated failure.
            return;
        }

        // 'create' so conditionally-required fields (e.g. ->required(fn
        // ($context) => $context === 'create')) evaluate the way they
        // really do on the form that actually inserts a row.
        $schema = $resourceClass::form(Schema::make($component->instance()))
            ->model($model)
            ->operation('create');

        /** @var array<string, Component> $fields */
        $fields = [];
        $this->collectFields($schema->getComponents(), $fields);

        $columns = collect(DbSchema::getColumns($table))->keyBy('name');
        $indexes = collect(DbSchema::getIndexes($table));

        foreach ($fields as $name => $field) {
            $column = $columns->get($name);

            if ( ! $column) {
                continue;
            }

            $gapKey = "{$resourceClass}:{$name}";

            if (in_array($gapKey, array_keys(self::KNOWN_GAPS), true)) {
                continue;
            }

            $this->checkRequired($resourceClass, $name, $field, $column);
            $this->checkMaxLength($resourceClass, $name, $field, $column);
        }

        $this->checkUniqueIndexes($resourceClass, $fields, $indexes);
    }

    /**
     * @param array<int, Component>    $components
     * @param array<string, Component> $fields
     */
    private function collectFields(array $components, array &$fields): void
    {
        foreach ($components as $component) {
            if (method_exists($component, 'getName') && method_exists($component, 'getValidationRules')) {
                try {
                    $name = $component->getName();
                } catch (Throwable) {
                    $name = null;
                }

                if (is_string($name) && $name !== '') {
                    $fields[$name] = $component;
                }
            }

            if (method_exists($component, 'getChildComponents')) {
                try {
                    $this->collectFields($component->getChildComponents(), $fields);
                } catch (Throwable) {
                    // Component needs context this audit doesn't provide
                    // (e.g. a Repeater bound to an unset relationship) —
                    // not this test's concern.
                }
            }
        }
    }

    private function checkRequired(string $resourceClass, string $name, Component $field, array $column): void
    {
        if ($column['nullable']) {
            return;
        }

        if ($column['default'] !== null || $column['auto_increment']) {
            return;
        }

        if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
            return;
        }

        // Disabled / non-dehydrated fields aren't user-editable input — any
        // value they end up with is driven programmatically (JS reactivity,
        // a service-layer computation, ->dehydrateStateUsing, etc.), which
        // is a different bug class than "a user typed something the DB
        // constraint rejects." Not this audit's concern.
        if (method_exists($field, 'isDisabled') && $field->isDisabled()) {
            return;
        }

        if (method_exists($field, 'isDehydrated') && ! $field->isDehydrated()) {
            return;
        }

        if (method_exists($field, 'isRequired') && $field->isRequired()) {
            return;
        }

        $rules = $this->getRuleStrings($field);

        if (in_array('required', $rules, true)) {
            return;
        }

        $this->violations[] = "{$resourceClass}::form() field '{$name}' — DB column is NOT NULL with no default, but the field has no ->required().";
    }

    private function checkMaxLength(string $resourceClass, string $name, Component $field, array $column): void
    {
        if ( ! method_exists($field, 'getMaxLength')) {
            return;
        }

        if (($column['type_name'] ?? null) !== 'varchar') {
            return;
        }

        if ( ! preg_match('/varchar\((\d+)\)/', $column['type'] ?? '', $m)) {
            return;
        }

        $dbLength = (int) $m[1];

        // Laravel's default string() length — only flag columns where the
        // length was deliberately shortened, that's the meaningful signal.
        if ($dbLength >= 255) {
            return;
        }

        $formMax = $field->getMaxLength();

        if ($formMax !== null && $formMax <= $dbLength) {
            return;
        }

        $this->violations[] = "{$resourceClass}::form() field '{$name}' — DB column is varchar({$dbLength}), but the field has no ->maxLength({$dbLength}) (or a looser one).";
    }

    /**
     * @param array<string, Component>                   $fields
     * @param \Illuminate\Support\Collection<int, array> $indexes
     */
    private function checkUniqueIndexes(string $resourceClass, array $fields, $indexes): void
    {
        foreach ($indexes as $index) {
            if ( ! $index['unique'] || $index['primary']) {
                continue;
            }

            $indexedFields = array_filter(
                $index['columns'],
                fn (string $col) => isset($fields[$col])
            );

            if ($indexedFields === []) {
                continue; // none of this unique index's columns are form-editable
            }

            $anyGuarded = false;

            foreach ($indexedFields as $col) {
                if (in_array("{$resourceClass}:{$col}", array_keys(self::KNOWN_GAPS), true)) {
                    $anyGuarded = true;

                    continue 2;
                }

                if ($this->hasUniqueRule($fields[$col])) {
                    $anyGuarded = true;

                    break;
                }
            }

            if ($anyGuarded) {
                continue;
            }

            $cols               = implode(',', $index['columns']);
            $editable           = implode(',', $indexedFields);
            $this->violations[] = "{$resourceClass}::form() — DB has a unique index on ({$cols}), and its form-editable column(s) ({$editable}) have no ->unique() on any of them.";
        }
    }

    private function hasUniqueRule(Component $field): bool
    {
        foreach ($this->getRuleStrings($field, true) as $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                return true;
            }

            if (is_string($rule) && str_starts_with($rule, 'unique:')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, mixed>
     */
    private function getRuleStrings(Component $field, bool $raw = false): array
    {
        if ( ! method_exists($field, 'getValidationRules')) {
            return [];
        }

        try {
            $rules = $field->getValidationRules();
        } catch (Throwable) {
            return [];
        }

        if ($raw) {
            return $rules;
        }

        return array_values(array_filter($rules, 'is_string'));
    }
}
