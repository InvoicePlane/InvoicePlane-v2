<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\NoteResource\Pages\CreateNote;
use Modules\Core\Filament\Resources\NoteResource\Pages\EditNote;
use Modules\Core\Filament\Resources\NoteResource\Pages\ListNotes;
use Modules\Core\Models\Note;
use Modules\Core\Tests\AbstractTestCase;

class NotesTest extends TestCase
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
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('smoke')]
    /**
     * @group smoke
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\NoteResource
     */
    public function it_lists_notes(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListNotes::class)
            ->assertSuccessful();
    }

    // endregion

    // region crud
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\NoteResource
     *
     * @payload
     * []
     */
    public function it_creates_a_note(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateNote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\NoteResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_note_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateNote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\NoteResource
     *
     * @payload
     * []
     */
    public function it_updates_a_note(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Note::factory()->create();

        $payload = [
        ];

        Livewire::test(EditNote::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\NoteResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_note_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Note::factory()->create();

        $payload = [
        ];

        Livewire::test(EditNote::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\NoteResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_note(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Note::factory()->create();

        Livewire::test(ListNotes::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('notes', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
