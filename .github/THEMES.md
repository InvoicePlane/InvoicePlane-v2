# Filament Theme Documentation

## Available Themes

InvoicePlane v2 comes with multiple pre-built themes that can be applied to any Filament panel:

### 1. InvoicePlane (Default)
**File:** `invoiceplane.css`

The default InvoicePlane theme uses the primary color palette defined in the panel configuration. It provides a clean, professional interface that adapts to the primary colors set in your panel provider.

**Key Features:**
- Uses Filament's primary color system
- Flexible and customizable through panel color configuration
- Professional and clean design
- Good contrast for readability

### 2. InvoicePlane Blue
**File:** `invoiceplane-blue.css`

A blue variant of the InvoicePlane theme with a vibrant blue color scheme.

**Key Colors:**
- Primary: Blue-500 (#3B82F6)
- Sidebar: Blue-500
- Active states: Blue-700
- Hover states: Blue-500

**Best For:** Users who prefer a traditional blue business interface

### 3. Nord
**File:** `nord.css`

Based on the popular Nord color palette, this theme features cool, arctic-inspired colors with excellent contrast and readability.

**Key Colors:**
- **Polar Night** (Backgrounds): #2e3440, #3b4252
- **Snow Storm** (Text): #eceff4, #e5e9f0
- **Frost** (Accents): #88c0d0, #5e81ac
- **Aurora** (Semantic):
 - Danger: #bf616a (red)
 - Warning: #ebcb8b (yellow)
 - Success: #a3be8c (green)

**Best For:** Developers who prefer the Nord color scheme, or anyone preferring a cool, calming interface

### 4. Orange
**File:** `orange.css`

A vibrant orange theme using Tailwind's orange color palette.

**Key Colors:**
- Primary: Orange-500 (#F97316)
- Sidebar: Orange-500
- Active states: Orange-700
- Hover states: Orange-500

**Best For:** Creative professionals, agencies, or those wanting a warm, energetic interface

### 5. Reddit
**File:** `reddit.css`

Inspired by Reddit's iconic branding, this theme uses Reddit's signature orange.

**Key Colors:**
- Primary: #FF4500 (Reddit Orange)
- Sidebar: #FF4500
- Active states: #d93900 (darker orange)
- Hover states: #ff5722 (lighter orange)

**Best For:** Reddit enthusiasts or those wanting a bold, recognizable orange theme

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
 ->viteTheme('resources/css/filament/company/nord.css') // Change this line
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
 'resources/css/filament/company/my-custom-theme.css' // Add your theme
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

## Quick Reference: Switching Themes

To quickly switch themes for a panel, update the `viteTheme()` method in the appropriate panel provider:

**Admin Panel** (`Modules/Core/Providers/AdminPanelProvider.php`):
```php
->viteTheme('resources/css/filament/company/invoiceplane.css') // Default
->viteTheme('resources/css/filament/company/invoiceplane-blue.css') // Blue
->viteTheme('resources/css/filament/company/nord.css') // Nord
->viteTheme('resources/css/filament/company/orange.css') // Orange
->viteTheme('resources/css/filament/company/reddit.css') // Reddit
```

**Company Panel** (`Modules/Core/Providers/CompanyPanelProvider.php`):
```php
->viteTheme('resources/css/filament/company/invoiceplane.css') // Default
->viteTheme('resources/css/filament/company/invoiceplane-blue.css') // Blue
->viteTheme('resources/css/filament/company/nord.css') // Nord
->viteTheme('resources/css/filament/company/orange.css') // Orange
->viteTheme('resources/css/filament/company/reddit.css') // Reddit
```

**User Panel** (`Modules/Core/Providers/UserPanelProvider.php`):
```php
->viteTheme('resources/css/filament/company/invoiceplane.css') // Default
->viteTheme('resources/css/filament/company/invoiceplane-blue.css') // Blue
->viteTheme('resources/css/filament/company/nord.css') // Nord
->viteTheme('resources/css/filament/company/orange.css') // Orange
->viteTheme('resources/css/filament/company/reddit.css') // Reddit
```

After changing the theme, rebuild assets:
```bash
npm run build
```
