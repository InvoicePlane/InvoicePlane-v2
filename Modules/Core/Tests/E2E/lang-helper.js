import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const IP_LANG_PATH = path.resolve(__dirname, '../../../../resources/lang/en/ip.php');

let translationsCache = null;

/**
 * Parses resources/lang/en/ip.php into a JavaScript key-value map.
 * Matches PHP associative array entries: 'key' => 'value' or 'key' => "value"
 */
export function loadIpTranslations() {
  if (translationsCache) {
    return translationsCache;
  }

  const content = fs.readFileSync(IP_LANG_PATH, 'utf-8');
  const translations = {};

  // Match lines like: 'key' => 'value' or 'key' => "value"
  // Handles escaped quotes and multiline / single line strings.
  const regex = /'([a-zA-Z0-9_]+)'\s*=>\s*(?:'((?:\\'|[^'])*)'|"((?:\\"|[^"])*)")/g;
  let match;
  while ((match = regex.exec(content)) !== null) {
    const key = match[1];
    const value = (match[2] !== undefined ? match[2].replace(/\\'/g, "'") : match[3].replace(/\\"/g, '"'));
    translations[key] = value;
  }

  translationsCache = translations;
  return translations;
}

/**
 * Returns translated string for an ip key, e.g. trans('vat_id') => 'Vat id'
 */
export function trans(key) {
  const dict = loadIpTranslations();
  if (!(key in dict)) {
    throw new Error(`Translation key "ip.${key}" not found in resources/lang/en/ip.php`);
  }
  return dict[key];
}
