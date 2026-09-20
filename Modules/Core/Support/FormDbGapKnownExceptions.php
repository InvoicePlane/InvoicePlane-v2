<?php

namespace Modules\Core\Support;

/**
 * Single source of truth for deliberate "form field doesn't enforce a DB
 * constraint" exceptions. Shared by both halves of the mind-the-gap audit:
 * - backend: Modules\Core\Tests\Feature\FormDbConstraintAuditTest
 * - frontend: testrunner's find-form-gaps.js, fed via `php artisan
 *   mind-the-gap:export-schema`.
 *
 * Keeping one shared list means a deliberate exception only has to be
 * recorded once, and can't silently drift between the two audits.
 */
class FormDbGapKnownExceptions
{
    /**
     * "ResourceClass:field" => reason. Only add an entry here with a real
     * reason — this list is itself reviewed by both audits' own diffs, so
     * it can't grow silently.
     *
     * @var array<string, string>
     */
    public const array KNOWN_GAPS = [
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
}
