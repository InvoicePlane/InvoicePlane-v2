<?php

namespace Modules\Clients\Tests\Feature;

use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\Permission;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Core\Tests\Concerns\InteractsWithPermissions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * Per #503: authorization tests for the Relations (Clients) resource.
 *
 * Note: RelationResource::getPages() only registers 'index' -- there is no
 * routed Create/Edit/View page (CRUD happens via modal actions on the list,
 * see #402's investigation), so these tests exercise the actual
 * authorization gates (RelationResource::can*()) directly rather than the
 * issue's suggested Livewire::test(CreateInvoice::class) pattern, which
 * would target unreachable pages in this codebase. Import/export/duplicate
 * actions from the issue's checklist are not implemented in RelationsTable
 * yet, so there is nothing to test authorization for there.
 */
#[CoversClass(RelationResource::class)]
class RelationResourceAuthorizationTest extends AbstractCompanyPanelTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_allows_viewing_the_list_with_view_relations_permission(): void
    {
        $this->grantPermission(Permission::VIEW_RELATIONS);
        $this->assertTrue(RelationResource::canViewAny());
    }

    #[Test]
    public function it_blocks_viewing_the_list_without_view_relations_permission(): void
    {
        $this->withoutRelationsPermissions();

        $this->assertFalse(RelationResource::canViewAny());
    }

    #[Test]
    public function it_allows_creating_with_create_relations_permission(): void
    {
        $this->grantPermission(Permission::CREATE_RELATIONS);
        $this->assertTrue(RelationResource::canCreate());
    }

    #[Test]
    public function it_blocks_creating_without_create_relations_permission(): void
    {
        $this->withoutRelationsPermissions();

        $this->assertFalse(RelationResource::canCreate());
    }

    #[Test]
    public function it_allows_editing_with_edit_relations_permission(): void
    {
        $client = Relation::factory()->for($this->company)->create();
        $this->grantPermission(Permission::EDIT_RELATIONS);

        $this->assertTrue(RelationResource::canEdit($client));
    }

    #[Test]
    public function it_blocks_editing_without_edit_relations_permission(): void
    {
        $client = Relation::factory()->for($this->company)->create();
        $this->withoutRelationsPermissions();

        $this->assertFalse(RelationResource::canEdit($client));
    }

    #[Test]
    public function it_allows_deleting_with_delete_relations_permission(): void
    {
        $client = Relation::factory()->for($this->company)->create();
        $this->grantPermission(Permission::DELETE_RELATIONS);

        $this->assertTrue(RelationResource::canDelete($client));
    }

    #[Test]
    public function it_blocks_deleting_without_delete_relations_permission(): void
    {
        $client = Relation::factory()->for($this->company)->create();
        $this->withoutRelationsPermissions();

        $this->assertFalse(RelationResource::canDelete($client));
    }

    /**
     * AbstractCompanyPanelTestCase assigns the client_admin role by default,
     * which now includes Relations permissions -- strip it to test the
     * genuinely-unauthorized case.
     */
    private function withoutRelationsPermissions(): void
    {
        $this->user->syncRoles([]);
        $this->user->syncPermissions([]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
