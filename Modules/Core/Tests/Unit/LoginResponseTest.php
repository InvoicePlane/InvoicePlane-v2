<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Core\Filament\Responses\LoginResponse;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class LoginResponseTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_sets_current_company_id_after_login(): void
    {
        $this->markTestIncomplete();

        // Arrange: create a user and attach a company
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        Auth::login($user);

        // Act: call LoginResponse
        $response = (new LoginResponse())->toResponse(request());

        // Assert: current_company_id is set in session
        $this->assertEquals($company->id, session('current_company_id'));
    }

    #[Test]
    public function it_aborts_if_no_company_found(): void
    {
        $this->markTestIncomplete();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $user = User::factory()->create();
        Auth::login($user);

        (new LoginResponse())->toResponse(request());
    }
}
