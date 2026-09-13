import { registerRequiredFieldOmissionTests } from './required-field-helpers.js';

// mind-the-gap-again: real frontend counterpart to EmailTemplateResource's
// (company panel) PHPUnit "it_fails_to_create_X_without_required_Y" tests.
// See required-field-helpers.js.
//
// No hand-written list/create tests exist yet for this resource on the
// company panel — only the required-field-omission check below. Add them
// here, alongside this one, when that coverage is written; don't split
// required-field checks back out into a separate file.
registerRequiredFieldOmissionTests('Core', {
  'company/email-templates': ['body'],
});
