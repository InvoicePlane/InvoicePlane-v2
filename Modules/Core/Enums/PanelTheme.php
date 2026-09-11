<?php

namespace Modules\Core\Enums;

/**
 * The Filament panel themes a company can choose between, one case per
 * stylesheet in `resources/css/filament/company/`.
 *
 * This enum is the whitelist that stands between the `panel_theme` setting
 * row and `Panel::viteTheme()`: the stored value is a free-form string in the
 * `settings` table, and handing an arbitrary one to Vite would fail manifest
 * lookup at render time -- on every page of the panel, with no way back to
 * the settings form to undo it. Resolve through `fromValue()` so an unknown
 * or removed value degrades to the default instead.
 *
 * Adding a theme means: a stylesheet in `resources/css/filament/company/`, an
 * entry in `vite.config.js` (unbuilt entrypoints are not in the manifest), and
 * a case here.
 */
enum PanelTheme: string
{
    case BASE              = 'base';
    case INVOICEPLANE      = 'invoiceplane';
    case INVOICEPLANE_BLUE = 'invoiceplane-blue';
    case NORD              = 'nord';
    case ORANGE            = 'orange';
    case REDDIT            = 'reddit';

    /**
     * The theme applied when a company has never chosen one, and the fallback
     * for a stored value this enum no longer knows.
     */
    public static function default(): self
    {
        return self::BASE;
    }

    /**
     * Resolve a stored setting value, falling back to the default for null,
     * empty and unknown values.
     */
    public static function fromValue(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::default();
        }

        return self::tryFrom($value) ?? self::default();
    }

    /**
     * Options for a Filament `Select`, keyed by stored value.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Display label. These are theme names rather than UI copy, so they are
     * not translated -- same convention as ReportBand::getLabel().
     */
    public function label(): string
    {
        return match ($this) {
            self::BASE              => 'Base',
            self::INVOICEPLANE      => 'InvoicePlane',
            self::INVOICEPLANE_BLUE => 'InvoicePlane Blue',
            self::NORD              => 'Nord',
            self::ORANGE            => 'Orange',
            self::REDDIT            => 'Reddit',
        };
    }

    /**
     * One-line description of what the theme looks like, shown under the
     * option in the settings form.
     */
    public function description(): string
    {
        return match ($this) {
            self::BASE              => trans('ip.panel_theme_base_description'),
            self::INVOICEPLANE      => trans('ip.panel_theme_invoiceplane_description'),
            self::INVOICEPLANE_BLUE => trans('ip.panel_theme_invoiceplane_blue_description'),
            self::NORD              => trans('ip.panel_theme_nord_description'),
            self::ORANGE            => trans('ip.panel_theme_orange_description'),
            self::REDDIT            => trans('ip.panel_theme_reddit_description'),
        };
    }

    /**
     * The Vite entrypoint passed to `Panel::viteTheme()`.
     */
    public function viteEntrypoint(): string
    {
        return 'resources/css/filament/company/' . $this->value . '.css';
    }
}
