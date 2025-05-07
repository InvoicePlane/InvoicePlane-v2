<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Tests\Feature\WelcomeViewTest;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class WelcomeViewTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload []
     *
     * @arrange logged-in superadmin
     *
     * @act view welcome page
     *
     * @assert page renders with 200
     */
    public function it_shows_welcome_page(): void
    {
        $this->markTestIncomplete();

        Livewire::test(Welcome::class)
            ->actingAs($this->superAdmin())
            ->assertSuccessful();
    }
}
