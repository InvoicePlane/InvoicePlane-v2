<?php

namespace Modules\Core\Tests\Unit\Middleware;

use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('unit')]
class EnsureUserCanAccessCompanyTest extends AbstractTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_allows_super_admin_to_access_any_company()
    {
        $this->markTestIncomplete('Verify super admin can access any company');

        // Arrange
        $middleware = new \App\Http\Middleware\EnsureUserCanAccessCompany();
        $request    = new \Illuminate\Http\Request();
        $company    = \Modules\Core\Models\Company::factory()->create();
        $user       = \Modules\Core\Models\User::factory()
            ->hasAttached($company)
            ->create();

        // Make user a super admin
        $user->assignRole(\Modules\Core\Enums\UserRole::SUPER_ADMIN);
        $this->actingAs($user);

        // Set up route with company parameter
        $request->setRouteResolver(function () use ($request, $company) {
            $route = new \Illuminate\Routing\Route('GET', '/{tenant}', ['middleware' => 'web']);
            $route->bind($request);
            $route->setParameter('tenant', $company->search_code);

            return $route;
        });

        // Act
        $response = $middleware->handle($request, function () {
            return new \Illuminate\Http\Response();
        });

        // Assert
        $this->assertNotEquals(403, $response?->getStatusCode() ?? 200);
    }

    #[Test]
    #[Group('unit')]
    public function it_denies_access_to_unauthorized_company_for_regular_users()
    {
        $this->markTestIncomplete('Verify regular users cannot access unauthorized companies');

        // Arrange
        $middleware = new \App\Http\Middleware\EnsureUserCanAccessCompany();
        $request    = new \Illuminate\Http\Request();
        $company    = \Modules\Core\Models\Company::factory()->create();
        $user       = \Modules\Core\Models\User::factory()->create();

        // Set up route with company parameter
        $request->setRouteResolver(function () use ($request, $company) {
            $route = new \Illuminate\Routing\Route('GET', '/{tenant}', ['middleware' => 'web']);
            $route->bind($request);
            $route->setParameter('tenant', $company->search_code);

            return $route;
        });

        // Act & Assert
        $response = $middleware->handle($request, function () {
            return new \Illuminate\Http\Response();
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_access_to_authorized_company_for_regular_users()
    {
        $this->markTestIncomplete('Verify regular users can access their authorized companies');

        // Arrange
        $middleware = new \App\Http\Middleware\EnsureUserCanAccessCompany();
        $request    = new \Illuminate\Http\Request();
        $company    = \Modules\Core\Models\Company::factory()->create();
        $user       = \Modules\Core\Models\User::factory()
            ->hasAttached($company)
            ->create();

        $this->actingAs($user);

        // Set up route with company parameter
        $request->setRouteResolver(function () use ($request, $company) {
            $route = new \Illuminate\Routing\Route('GET', '/{tenant}', ['middleware' => 'web']);
            $route->bind($request);
            $route->setParameter('tenant', $company->search_code);

            return $route;
        });

        // Act
        $response = $middleware->handle($request, function () {
            return new \Illuminate\Http\Response();
        });

        // Assert
        $this->assertNotEquals(403, $response?->getStatusCode() ?? 200);
        $this->assertEquals(session('current_company_id'), $company->id);
    }

    #[Test]
    #[Group('unit')]
    public function it_denies_access_to_unauthenticated_users()
    {
        $this->markTestIncomplete('Verify unauthenticated users are redirected to login');

        // Arrange
        $middleware = new \App\Http\Middleware\EnsureUserCanAccessCompany();
        $request    = new \Illuminate\Http\Request();
        $company    = \Modules\Core\Models\Company::factory()->create();

        // Ensure no user is authenticated
        auth()->logout();

        // Set up route with company parameter
        $request->setRouteResolver(function () use ($request, $company) {
            $route = new \Illuminate\Routing\Route('GET', '/{tenant}', ['middleware' => 'web']);
            $route->bind($request);
            $route->setParameter('tenant', $company->search_code);

            return $route;
        });

        // Act & Assert
        $middleware->handle($request, function () {
            $this->fail('Should not reach here - unauthenticated users should be redirected');
        });

        // Should be redirected to login
        $this->assertTrue(auth()->guest());
    }

    #[Test]
    #[Group('unit')]
    public function it_handles_nonexistent_company_gracefully()
    {
        $this->markTestIncomplete('Verify handling of non-existent company access');

        // Arrange
        $middleware = new \App\Http\Middleware\EnsureUserCanAccessCompany();
        $request    = new \Illuminate\Http\Request();
        $user       = \Modules\Core\Models\User::factory()->create();
        $this->actingAs($user);

        // Set up route with non-existent company
        $nonExistentSearchCode = 'nonexistent-company';
        $request->setRouteResolver(function () use ($request, $nonExistentSearchCode) {
            $route = new \Illuminate\Routing\Route('GET', '/{tenant}', ['middleware' => 'web']);
            $route->bind($request);
            $route->setParameter('tenant', $nonExistentSearchCode);

            return $route;
        });

        // Act & Assert
        $response = $middleware->handle($request, function () {
            $this->fail('Should not reach here - non-existent company should return 404');
        });

        $this->assertEquals(404, $response->getStatusCode());
    }
}
