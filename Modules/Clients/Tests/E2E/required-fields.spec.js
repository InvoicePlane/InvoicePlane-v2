import { registerRequiredFieldOmissionTests } from '../../../Core/Tests/E2E/required-field-helpers.js';

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests. For every required DB
 * column of every Clients resource (Contacts, Relations), fills a fully valid
 * create form except that one field and asserts the browser genuinely
 * rejects the omission. See Core/Tests/E2E/required-field-helpers.js for
 * the full mechanism and the two real rejection paths it asserts.
 */
registerRequiredFieldOmissionTests('Clients');
