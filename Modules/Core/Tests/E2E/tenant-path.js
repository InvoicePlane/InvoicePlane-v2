import { E2E_TENANT } from './config.js';

/**
 * Build a tenant-scoped company panel path.
 *
 * Company panel resources/pages live under `{tenant:search_code}/...`
 * (see routes/CompanyPanelProvider); only the login route sits at the root.
 *
 *   tenantPath('/invoices')        -> '/ivplv2/invoices'
 *   tenantPath('/invoices/create') -> '/ivplv2/invoices/create'
 */
export function tenantPath(path = '') {
  const cleanPath = path.startsWith('/') ? path : `/${path}`;

  return `/${E2E_TENANT}${cleanPath}`;
}
