# Peppol Integration Documentation

## Overview

This Peppol integration allows InvoicePlane v2 to send invoices electronically through the Peppol network. The implementation follows a modular architecture with clean separation of concerns, comprehensive error handling, and extensive test coverage.

## Architecture

### Components

1. **HTTP Client Layer**
   - `ExternalClient`: Guzzle-like HTTP wrapper using Laravel's Http facade
   - `HttpClientExceptionHandler`: Decorator providing exception handling and logging

2. **Peppol Provider Layer**
   - `BasePeppolClient`: Abstract base class for all Peppol providers
   - `EInvoiceBeClient`: Concrete implementation for e-invoice.be provider
   - `DocumentsClient`: Specific client for document operations

3. **Service Layer**
   - `PeppolService`: Business logic for Peppol operations
   - Handles invoice validation, data preparation, and transmission

4. **Action Layer**
   - `SendInvoiceToPeppolAction`: Orchestrates invoice sending process
   - Can be called from UI actions or programmatically

5. **UI Integration**
   - Header action in `EditInvoice` page
   - Table action in `ListInvoices` page
   - Modal form for entering customer Peppol ID

## Installation & Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```env
# Peppol Provider Configuration
PEPPOL_PROVIDER=e_invoice_be
PEPPOL_E_INVOICE_BE_API_KEY=your-api-key-here
PEPPOL_E_INVOICE_BE_BASE_URL=https://api.e-invoice.be

# Optional Peppol Settings
PEPPOL_CURRENCY_CODE=EUR
```

### 2. Configuration File

The configuration is located at `Modules/Invoices/Config/config.php` and contains:
- Provider settings
- Document format defaults
- Validation rules

### 3. Service Registration

All Peppol services are automatically registered in `InvoicesServiceProvider`. The provider:
- Binds HTTP clients with dependency injection
- Configures exception handler with logging (non-production only)
- Registers Peppol clients and services

## Usage

### From UI (Filament Actions)

#### Edit Invoice Page
1. Navigate to an invoice edit page
2. Click the "Send to Peppol" button in the header
3. Enter the customer's Peppol ID (e.g., `BE:0123456789`)
4. Click submit

#### Invoices List Page
1. Navigate to the invoices list
2. Click the action menu on an invoice row
3. Select "Send to Peppol"
4. Enter the customer's Peppol ID
5. Click submit

### Programmatically

```php
use Modules\Invoices\Actions\SendInvoiceToPeppolAction;
use Modules\Invoices\Models\Invoice;

$invoice = Invoice::find($invoiceId);
$action = app(SendInvoiceToPeppolAction::class);

try {
    $result = $action->execute($invoice, [
        'customer_peppol_id' => 'BE:0123456789',
    ]);
    
    // Success! Document ID is available
    $documentId = $result['document_id'];
    $status = $result['status'];
    
} catch (\InvalidArgumentException $e) {
    // Validation error
    Log::error('Invalid invoice data: ' . $e->getMessage());
    
} catch (\Illuminate\Http\Client\RequestException $e) {
    // API request failed
    Log::error('Peppol API error: ' . $e->getMessage());
}
```

### Check Document Status

```php
$action = app(SendInvoiceToPeppolAction::class);
$status = $action->getStatus('DOC-123456');

// Returns:
// [
//     'status' => 'delivered',
//     'delivered_at' => '2024-01-15T12:30:00Z',
//     ...
// ]
```

### Cancel Document

```php
$action = app(SendInvoiceToPeppolAction::class);
$success = $action->cancel('DOC-123456');
```

## Data Mapping

### Invoice to Peppol Document

The `PeppolService` transforms InvoicePlane invoices to Peppol UBL format:

```php
[
    'document_type' => 'invoice',
    'invoice_number' => 'INV-2024-001',
    'issue_date' => '2024-01-15',
    'due_date' => '2024-02-14',
    'currency_code' => 'EUR',
    
    'supplier' => [
        'name' => 'Company Name',
        // Additional supplier details
    ],
    
    'customer' => [
        'name' => 'Customer Name',
        'endpoint_id' => 'BE:0123456789',
        'endpoint_scheme' => 'BE:CBE',
    ],
    
    'invoice_lines' => [
        [
            'id' => 1,
            'quantity' => 2,
            'unit_code' => 'C62',
            'line_extension_amount' => 200.00,
            'price_amount' => 100.00,
            'item' => [
                'name' => 'Product Name',
                'description' => 'Product description',
            ],
        ],
    ],
    
    'legal_monetary_total' => [
        'line_extension_amount' => 200.00,
        'tax_exclusive_amount' => 200.00,
        'tax_inclusive_amount' => 242.00,
        'payable_amount' => 242.00,
    ],
    
    'tax_total' => [
        'tax_amount' => 42.00,
    ],
]
```

## Validation

Before sending to Peppol, invoices are validated:

- ✅ Must have a customer
- ✅ Must have an invoice number
- ✅ Must have at least one invoice item
- ✅ Cannot be in draft status
- ✅ Customer Peppol ID must be provided

## Error Handling

### Common Errors

| Error Code | Description | Solution |
|------------|-------------|----------|
| 400 | Bad Request | Check invoice data format |
| 401 | Unauthorized | Verify API key is correct |
| 422 | Validation Error | Review Peppol requirements |
| 429 | Rate Limit | Wait and retry |
| 500 | Server Error | Contact Peppol provider |

### Exception Types

- `InvalidArgumentException`: Invoice validation failed
- `RequestException`: HTTP request failed (4xx, 5xx)
- `ConnectionException`: Network/timeout issues

All exceptions are logged automatically when using the `HttpClientExceptionHandler`.

## Testing

### Running Tests

```bash
# Run all Peppol tests
php artisan test Modules/Invoices/Tests/Unit/Peppol

# Run specific test suite
php artisan test Modules/Invoices/Tests/Unit/Peppol/Services/PeppolServiceTest

# Run with coverage
php artisan test --coverage
```

### Test Structure

Tests use Laravel's HTTP fakes instead of mocks:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'https://api.e-invoice.be/*' => Http::response([
        'document_id' => 'DOC-123',
        'status' => 'submitted',
    ], 200),
]);

// Your test code here

Http::assertSent(function ($request) {
    return $request->url() === 'https://api.e-invoice.be/api/documents';
});
```

### Test Coverage

- ✅ `ExternalClientTest`: 15 tests (HTTP wrapper)
- ✅ `HttpClientExceptionHandlerTest`: Not yet implemented
- ✅ `DocumentsClientTest`: 12 tests (API client)
- ✅ `PeppolServiceTest`: 11 tests (Business logic)
- ✅ `SendInvoiceToPeppolActionTest`: 11 tests (Action)

Total: **49 unit tests** covering success and failure scenarios

## Adding New Peppol Providers

To add support for another Peppol provider (e.g., Storecove):

1. Create provider client:
```php
namespace Modules\Invoices\Peppol\Clients\Storecove;

class StorecoveClient extends BasePeppolClient
{
    protected function getAuthenticationHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }
}
```

2. Create endpoint clients extending the provider client:
```php
class StorecoveDocumentsClient extends StorecoveClient
{
    public function submitDocument(array $data): Response
    {
        return $this->client->post('documents', $data);
    }
}
```

3. Register in `InvoicesServiceProvider`:
```php
$this->app->bind(
    StorecoveDocumentsClient::class,
    function ($app) {
        $handler = $app->make(HttpClientExceptionHandler::class);
        return new StorecoveDocumentsClient(
            $handler,
            config('invoices.peppol.storecove.api_key'),
            config('invoices.peppol.storecove.base_url')
        );
    }
);
```

4. Update configuration in `config.php`:
```php
'storecove' => [
    'api_key' => env('PEPPOL_STORECOVE_API_KEY', ''),
    'base_url' => env('PEPPOL_STORECOVE_BASE_URL', 'https://api.storecove.com'),
],
```

## API Documentation

### e-invoice.be API

Full API documentation: https://api.e-invoice.be/docs

Key endpoints used:
- `POST /api/documents` - Submit a document
- `GET /api/documents/{id}` - Get document details
- `GET /api/documents/{id}/status` - Get document status
- `DELETE /api/documents/{id}` - Cancel document

## Translations

Translation keys available in `resources/lang/en/ip.php`:

- `send_to_peppol`: "Send to Peppol"
- `customer_peppol_id`: "Customer Peppol ID"
- `customer_peppol_id_helper`: "The Peppol participant identifier..."
- `peppol_success_title`: "Sent to Peppol"
- `peppol_success_body`: "Invoice successfully sent..."
- `peppol_error_title`: "Peppol Transmission Failed"
- `peppol_error_body`: "Failed to send invoice..."

## Logging

All HTTP requests and responses are logged in non-production environments:

```
[2024-01-15 10:30:00] local.INFO: HTTP Request
[2024-01-15 10:30:01] local.INFO: HTTP Response
[2024-01-15 10:30:01] local.INFO: Sending invoice to Peppol {"invoice_id":123}
[2024-01-15 10:30:02] local.INFO: Invoice sent to Peppol successfully {"document_id":"DOC-123"}
```

## Security Considerations

1. **API Keys**: Store in `.env`, never commit to version control
2. **Sensitive Data**: Automatically redacted in logs
3. **HTTPS**: All Peppol communication uses HTTPS
4. **Validation**: Invoice data validated before transmission
5. **Error Messages**: User-facing messages don't expose sensitive details

## Troubleshooting

### API Key Issues
```bash
# Check if API key is set
php artisan tinker
>>> config('invoices.peppol.e_invoice_be.api_key')
```

### Connection Timeouts
Increase timeout in provider client:
```php
protected function getTimeout(): int
{
    return 120; // 2 minutes
}
```

### Debug Mode
Enable request logging:
```php
$handler = app(HttpClientExceptionHandler::class);
$handler->enableLogging();
```

## Future Enhancements

- [ ] Store Peppol document IDs in invoice table
- [ ] Add webhook support for delivery notifications
- [ ] Implement automatic retry logic
- [ ] Add support for credit notes
- [ ] Bulk sending of invoices
- [ ] Dashboard widget for transmission status
- [ ] Support for multiple Peppol providers
- [ ] PDF attachment support

## Contributing

When adding features:
1. Write tests first (TDD approach)
2. Use fakes over mocks
3. Include both success and failure test cases
4. Update documentation
5. Follow existing code style and patterns

## License

Same as InvoicePlane v2 - MIT License
