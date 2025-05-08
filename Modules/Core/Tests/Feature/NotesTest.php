<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Note;
use Modules\Core\Tests\AbstractTestCase;

//use Modules\Core\Filament\Resources\NoteResource\Pages\CreateNote;
//use Modules\Core\Filament\Resources\NoteResource\Pages\EditNote;
//use Modules\Core\Filament\Resources\NoteResource\Pages\ListNotes;

class NotesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @group smoke
     *
     */
    public function it_lists_notes(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(ListNotes::class);

/** assert */
$component->assertSuccessful();
    }

    // endregion

    // region crud
    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     *
     * @payload
     * []
     */
    public function it_creates_a_note(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(CreateNote::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     *
     * @payload
     * []
     */
    public function it_fails_to_create_note_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(CreateNote::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     *
     * @payload
     * []
     */
    public function it_updates_a_note(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Note::factory()->create();

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(EditNote::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

/** assert */
$component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     *
     * @payload
     * []
     */
    public function it_fails_to_update_note_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $record = Note::factory()->create();

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(EditNote::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

/** assert */
$component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     *
     * @payload
     * []
     */
    public function it_deletes_a_note(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Note::factory()->create();

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(ListNotes::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('notes', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
