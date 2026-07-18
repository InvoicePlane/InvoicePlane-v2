<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Upload;
use Modules\Core\Services\UserService;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserService::class)]
class UserServiceAvatarTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_replaces_the_avatar_and_deletes_the_previous_file(): void
    {
        /* Arrange */
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.png', 'old-contents');
        Storage::disk('public')->put('avatars/new.png', 'new-contents');

        $service = app(UserService::class);
        $service->updateAvatar($this->user, 'avatars/old.png');

        /* Act */
        $upload = $service->updateAvatar($this->user, 'avatars/new.png');

        /* Assert */
        Storage::disk('public')->assertMissing('avatars/old.png');
        Storage::disk('public')->assertExists('avatars/new.png');

        $this->assertSame('avatars/new.png', $upload->upload_stored_name);
        $this->assertSame(1, Upload::query()
            ->where('uploadable_type', $this->user::class)
            ->where('uploadable_id', $this->user->id)
            ->where('file_description', 'avatar')
            ->count());
    }

    #[Test]
    public function it_removes_the_avatar_record_and_deletes_the_stored_file(): void
    {
        /* Arrange */
        Storage::fake('public');
        Storage::disk('public')->put('avatars/avatar.png', 'contents');

        $service = app(UserService::class);
        $service->updateAvatar($this->user, 'avatars/avatar.png');

        /* Act */
        $removed = $service->removeAvatar($this->user);

        /* Assert */
        $this->assertTrue($removed);
        Storage::disk('public')->assertMissing('avatars/avatar.png');
        $this->assertDatabaseMissing('uploads', [
            'uploadable_type'  => $this->user::class,
            'uploadable_id'    => $this->user->id,
            'file_description' => 'avatar',
        ]);
    }

    #[Test]
    public function it_treats_removing_a_nonexistent_avatar_as_a_no_op(): void
    {
        /* Act */
        $removed = app(UserService::class)->removeAvatar($this->user);

        /* Assert */
        $this->assertFalse($removed);
    }
}
