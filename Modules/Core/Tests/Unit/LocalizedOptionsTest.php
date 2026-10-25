<?php

namespace Modules\Core\Tests\Unit;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Modules\Core\Filament\Company\Pages\Auth\EditProfile;
use Modules\Core\Support\LocalizedOptions;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('intl')]
class LocalizedOptionsTest extends AbstractTestCase
{
    #[Test]
    public function profile_language_choices_follow_locale_changes_without_changing_values(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('company'));
        app()->setLocale('en');
        $page     = new EditProfile();
        $language = collect($page->form(Schema::make($page))->getFlatComponents(withHidden: true))
            ->first(fn ($component) => $component instanceof Select && $component->getName() === 'language');
        $english = $language->getOptions();

        app()->setLocale('de');

        $this->assertSame('Deutsch', $language->getOptions()['de']);
        $this->assertSame('Englisch', $language->getOptions()['en']);
        $this->assertSame(array_keys($english), array_keys($language->getOptions()));
        app()->setLocale('en');
        $this->assertSame($english, $language->getOptions());
    }

    #[Test]
    public function configured_language_keys_and_unknown_labels_are_preserved(): void
    {
        $original = ['de' => 'German', 'en' => 'English', 'fr' => 'French', 'zz' => 'Custom language', 'bad-code' => 'Private label'];
        config(['languages' => $original]);
        app()->setLocale('de');

        $this->assertSame([
            'de' => 'Deutsch', 'en' => 'Englisch', 'fr' => 'Französisch',
            'zz' => 'Custom language', 'bad-code' => 'Private label',
        ], LocalizedOptions::languages());
        $this->assertSame($original, LocalizedOptions::languages('en'));
    }

    #[Test]
    public function country_names_are_localized_without_changing_codes_or_unknown_labels(): void
    {
        $original = ['DE' => 'Germany', 'US' => 'United States of America (the)', 'AT' => 'Austria', 'XX' => 'Custom region', 'ZZ' => 'Private region', 'invalid' => 'Fallback'];
        config(['countries' => $original]);

        $this->assertSame([
            'DE' => 'Deutschland', 'US' => 'Vereinigte Staaten', 'AT' => 'Österreich',
            'XX' => 'Custom region', 'ZZ' => 'Private region', 'invalid' => 'Fallback',
        ], LocalizedOptions::countries('de'));
        $this->assertSame($original, LocalizedOptions::countries('en'));
    }

    #[Test]
    public function empty_option_catalogues_remain_empty(): void
    {
        config(['countries' => [], 'languages' => []]);

        $this->assertSame([], LocalizedOptions::countries('de'));
        $this->assertSame([], LocalizedOptions::languages('de'));
    }
}
