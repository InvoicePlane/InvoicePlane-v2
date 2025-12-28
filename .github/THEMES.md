# Filament Theme Documentation

## Available Themes

InvoicePlane v2 comes with multiple pre-built themes that can be applied to any Filament panel:

1. **invoiceplane** - Default InvoicePlane theme using the primary color palette
2. **invoiceplane_blue** - Blue variant of the InvoicePlane theme
3. **nord** - Nord color scheme with Polar Night, Snow Storm, Frost, and Aurora colors
4. **orange** - Orange-themed interface using Tailwind orange colors
5. **reddit** - Reddit-inspired theme with the iconic Reddit orange (#FF4500)

## Theme Files Location

All themes are located in:
```
resources/css/filament/company/
```

Available theme files:
- `invoiceplane.css`
- `invoiceplane-blue.css`
- `nord.css`
- `orange.css`
- `reddit.css`

## How to Apply a Theme

### Changing Theme for a Panel

To apply a theme to a Filament panel, update the panel provider file and set the `viteTheme()` method:

```php
// Example: Modules/Core/Providers/CompanyPanelProvider.php

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('company')
        ->path('')
        ->viteTheme('resources/css/filament/company/nord.css')  // Change this line
        ->login()
        // ... other configuration
}
```

### Building the Themes

After changing a theme or modifying theme files, you need to rebuild the assets:

```bash
npm run build
```

For development with hot reload:
```bash
npm run dev
```

## Theme Structure

Each theme includes styling for:
- Topbar (background, navigation, logo)
- Sidebar (background, navigation items, active states)
- Form elements (checkboxes, inputs, labels)
- Modals and dialogs
- Tables and pagination
- Buttons and icons
- Breadcrumbs
- User menu

## Creating a Custom Theme

To create a new custom theme:

1. Create a new CSS file in `resources/css/filament/company/`:
   ```bash
   touch resources/css/filament/company/my-custom-theme.css
   ```

2. Copy the content from an existing theme (e.g., `invoiceplane.css`) as a starting point

3. Update the colors and styles to match your desired theme

4. Register the theme in `vite.config.js`:
   ```javascript
   input: [
       'resources/css/app.css',
       'resources/js/app.js',
       // ... existing themes
       'resources/css/filament/company/my-custom-theme.css'  // Add your theme
   ],
   ```

5. Build the assets:
   ```bash
   npm run build
   ```

6. Update your panel provider to use the new theme:
   ```php
   ->viteTheme('resources/css/filament/company/my-custom-theme.css')
   ```

## Nord Theme Colors

The Nord theme uses the following color palette:

- **Polar Night** - Dark backgrounds and UI elements
  - `--color-polarnight-800: #2e3440` (Primary dark background)
  - `--color-polarnight-700: #3b4252` (Secondary dark background)
  
- **Snow Storm** - Light text and highlights
  - `--color-snowstorm-600: #eceff4` (Primary light text)
  
- **Frost** - Primary accent colors
  - `--color-frost-500: #88c0d0` (Primary accent)
  - `--color-frost-700: #5e81ac` (Secondary accent)
  
- **Aurora** - Semantic colors
  - `--color-aurora-danger: #bf616a` (Error/danger)
  - `--color-aurora-warning: #ebcb8b` (Warning)
  - `--color-aurora-success: #a3be8c` (Success)

## Notes

- All themes are designed to work with Filament 4.0+
- Themes use Tailwind CSS utility classes where possible
- Custom CSS variables (like those in the Nord theme) are defined using the `@theme` directive
- Each theme is self-contained and can be switched independently per panel
