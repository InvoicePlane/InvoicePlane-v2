import { registerRequiredFieldOmissionTests } from './required-field-helpers.js';

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests. For every required DB
 * column of every Core resource (Companies, Users, Email Templates, Tax
 * Rates, Numberings, Note Templates, Company Users), fills a fully valid
 * create form except that one field and asserts the browser genuinely
 * rejects the omission. See required-field-helpers.js for the full
 * mechanism and the two real rejection paths it asserts.
 */
registerRequiredFieldOmissionTests('Core');
