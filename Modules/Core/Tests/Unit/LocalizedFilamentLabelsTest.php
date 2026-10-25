<?php

namespace Modules\Core\Tests\Unit;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ViewRelation;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\InvoicesRelationManager;
use Modules\Clients\Models\Relation;
use Modules\Core\Filament\Company\Pages\MyCompanies;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Schemas\InvoiceItemForm;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Tables\InvoiceItemsTable;
use PHPUnit\Framework\Attributes\Test;

class LocalizedFilamentLabelsTest extends AbstractTestCase
{
    public static function enumFormOptions(): array
    {
        return [
            'recurring frequency' => [
                \Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Schemas\RecurringInvoiceForm::class,
                \Modules\Invoices\Enums\RecurringFrequency::class,
                'frequency', 'enums.recurring_frequency.daily', 'daily',
            ],
            'email template type' => [
                \Modules\Core\Filament\Admin\Resources\EmailTemplates\Schemas\EmailTemplateForm::class,
                \Modules\Core\Enums\EmailTemplateType::class,
                'type', 'enums.email_template_type.text', 'text',
            ],
        ];
    }

    #[Test]
    public function resource_labels_resolve_the_current_locale_without_changing_routes(): void
    {
        app('translator')->addLines(['fields.contact' => 'Kontaktperson-Test', 'fields.contacts' => 'Kontaktpersonen-Test'], 'de');
        app()->setLocale('en');
        $slug = ContactResource::getSlug();

        app()->setLocale('de');

        $this->assertSame('Kontaktperson-Test', ContactResource::getModelLabel());
        $this->assertSame('Kontaktpersonen-Test', ContactResource::getPluralModelLabel());
        $this->assertSame('Kontaktpersonen-Test', ContactResource::getNavigationLabel());
        $this->assertSame($slug, ContactResource::getSlug());
    }

    #[Test]
    public function item_form_labels_resolve_after_a_locale_change(): void
    {
        app('translator')->addLines(['ip.quantity' => 'Menge-Test', 'fields.company_id' => 'Unternehmens-ID-Test'], 'de');
        app()->setLocale('en');
        $fields = collect(InvoiceItemForm::configure(Schema::make())->getComponents(withHidden: true))->keyBy(fn ($field) => $field->getName());

        app()->setLocale('de');

        $this->assertSame('Menge-Test', $fields['quantity']->getLabel());
        $this->assertSame('Unternehmens-ID-Test', $fields['company_id']->getLabel());
    }

    #[Test]
    public function item_table_labels_resolve_after_a_locale_change(): void
    {
        app('translator')->addLines(['ip.quantity' => 'Menge-Test', 'fields.product_id' => 'Produkt-ID-Test'], 'de');
        app()->setLocale('en');
        $livewire = new \Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices();
        $table    = InvoiceItemsTable::configure(Table::make($livewire));

        app()->setLocale('de');

        $this->assertSame('Menge-Test', $table->getColumn('quantity')->getLabel());
        $this->assertSame('Produkt-ID-Test', $table->getColumn('product.id')->getLabel());
    }

    #[Test]
    public function relation_tabs_and_page_titles_use_the_current_locale(): void
    {
        app('translator')->addLines(['ip.invoices' => 'Rechnungen-Test', 'ip.my_companies' => 'Meine Unternehmen-Test'], 'de');
        app()->setLocale('en');
        $page = new MyCompanies();
        $slug = MyCompanies::getSlug();

        app()->setLocale('de');

        $this->assertSame('Rechnungen-Test', InvoicesRelationManager::getTitle(new Relation(), ViewRelation::class));
        $this->assertSame('Meine Unternehmen-Test', $page->getTitle());
        $this->assertSame('Meine Unternehmen-Test', MyCompanies::getNavigationLabel());
        $this->assertSame($slug, MyCompanies::getSlug());
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('enumFormOptions')]
    public function enum_options_use_translated_labels_without_changing_values(string $formClass, string $enumClass, string $fieldName, string $translationKey, string $value): void
    {
        app('translator')->addLines([$translationKey => 'Übersetzte Option-Test'], 'de');
        app()->setLocale('en');
        $field = collect($formClass::configure(Schema::make(new \Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices()))->getFlatComponents(withHidden: true))
            ->first(fn ($component) => $component instanceof \Filament\Forms\Components\Select && $component->getName() === $fieldName);

        app()->setLocale('de');
        $options = $field->getOptions();

        $this->assertSame(array_column($enumClass::cases(), 'value'), array_keys($options));
        $this->assertSame('Übersetzte Option-Test', $options[$value]);
    }
}
