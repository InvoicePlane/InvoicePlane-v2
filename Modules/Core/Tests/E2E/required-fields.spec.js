import { registerRequiredFieldOmissionTests } from './required-field-helpers.js';

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests — for each field listed,
 * fills a valid create form except that one field and asserts the browser
 * rejects it. See required-field-helpers.js.
 *
 * Left off deliberately:
 * - company_id on every company-panel / tenant-scoped resource (injected).
 * - admin/companies:slug — read-only, auto-derived from name.
 * - admin/numberings:next_id — real user field, but after a missing-field
 *   submit the driver can't re-resolve its control; not worth a bespoke path.
 * - company/note-templates:template_body — RichEditor, no native control.
 * - company/company-users — its only typed field is email, and the "Add Team
 *   Member" modal's submit isn't reachable by the generic driver;
 *   name/password come from the looked-up user. company-users.spec.js covers it.
 */
registerRequiredFieldOmissionTests('Core', {
  'admin/companies': ['search_code', 'name'],
  'admin/numberings': ['type', 'name'],
  'admin/tax-rates': ['tax_rate_type', 'code', 'name'],
  'admin/users': ['name', 'email', 'password'],
  'admin/email-templates': ['body'],
  'company/email-templates': ['body'],
  'company/note-templates': ['template_title'],
});
