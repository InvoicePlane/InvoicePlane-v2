<?php

namespace Modules\Clients\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ViewRelation;
use Modules\Clients\Filament\Company\Resources\Relations\RelationManagers\NotesRelationManager;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class NotesRelationManagerTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_edits_a_client_note(): void
    {
        /* Arrange */
        $client = Relation::factory()->for($this->company)->customer()->create();
        $note   = $client->notes()->create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'noted_at'   => now(),
            'is_private' => false,
            'title'      => 'Client note',
            'content'    => 'Original note',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(NotesRelationManager::class, [
                'ownerRecord' => $client,
                'pageClass'   => ViewRelation::class,
            ])
            ->callTableAction('edit', $note, data: ['content' => 'Updated note']);

        /* Assert */
        $component->assertHasNoTableActionErrors();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'content' => 'Updated note']);
    }

    #[Test]
    public function it_deletes_a_client_note(): void
    {
        /* Arrange */
        $client = Relation::factory()->for($this->company)->customer()->create();
        $note   = $client->notes()->create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'noted_at'   => now(),
            'is_private' => false,
            'title'      => 'Client note',
            'content'    => 'Delete me',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(NotesRelationManager::class, [
                'ownerRecord' => $client,
                'pageClass'   => ViewRelation::class,
            ])
            ->callTableAction('delete', $note);

        /* Assert */
        $component->assertHasNoTableActionErrors();
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }
}
