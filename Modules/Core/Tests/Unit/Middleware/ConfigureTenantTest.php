<?php

namespace Modules\Core\Tests\Unit\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Mockery;
use Modules\Core\Http\Middleware\ConfigureTenant;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ConfigureTenant::class)]
class ConfigureTenantTest extends AbstractTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_redirects_unauthenticated_users_to_login(): void
    {
        $this->markTestIncomplete();

        // Create a test request
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        // Create the middleware instance
        $middleware = new \Modules\Core\Http\Middleware\ConfigureTenant();

        // Mock the Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(false);

        // Mock the redirect response
        $redirect = Mockery::mock('Illuminate\Routing\Redirector');
        $redirect->shouldReceive('guest')
            ->once()
            ->with('login')
            ->andReturn(new \Illuminate\Http\RedirectResponse('login'));

        $this->app->instance('redirect', $redirect);

        // Handle the request
        $response = $middleware->handle($request, function ($req) {
            $this->fail('Next middleware should not be called for unauthenticated users');
        });

        // Assert the response is a redirect to login
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(url('login'), $response->getTargetUrl());
    }

    #[Test]
    #[Group('unit')]
    public function it_handles_customer_admin_without_company_assignment(): void
    {
        $this->markTestIncomplete();

        // Create a test request
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        // Create the middleware instance
        $middleware = new \Modules\Core\Http\Middleware\ConfigureTenant();

        // Create a user with no company assignments
        $user = User::factory()->create();

        // Mock the Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Mock the redirect response
        $redirect = Mockery::mock('Illuminate\Routing\Redirector');
        $redirect->shouldReceive('route')
            ->once()
            ->with('error')
            ->andReturn(new \Illuminate\Http\RedirectResponse('/error'));

        $this->app->instance('redirect', $redirect);

        // Handle the request
        $response = $middleware->handle($request, function ($req) {
            $this->fail('Next middleware should not be called for users without company assignment');
        });

        // Assert the response is a redirect to error page
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(url('error'), $response->getTargetUrl());
    }

    #[Test]
    #[Group('unit')]
    public function it_configures_tenant_for_super_admin(): void
    {
        $this->markTestIncomplete();

        // Create a test request
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        // Create the middleware instance
        $middleware = new \Modules\Core\Http\Middleware\ConfigureTenant();

        // Create a super admin user
        $user    = User::factory()->create(['is_super_admin' => true]);
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Mock the Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->times(2)
            ->andReturn($user);

        // Mock the session
        Session::shouldReceive('put')
            ->once()
            ->with('current_company_id', $company->id);

        // Handle the request
        $response = $middleware->handle($request, function ($req) {
            return new \Illuminate\Http\Response('OK');
        });

        // Assert the response is successful
        $this->assertEquals('OK', $response->getContent());

        // Verify the tenant was set in the session
        $this->assertTrue(Session::has('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_configures_tenant_for_regular_user_with_company(): void
    {
        $this->markTestIncomplete();

        // Create a test request
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        // Create the middleware instance
        $middleware = new \Modules\Core\Http\Middleware\ConfigureTenant();

        // Create a regular user with a company
        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Mock the Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->times(2)
            ->andReturn($user);

        // Mock the session
        Session::shouldReceive('put')
            ->once()
            ->with('current_company_id', $company->id);

        // Handle the request
        $response = $middleware->handle($request, function ($req) {
            return new \Illuminate\Http\Response('OK');
        });

        // Assert the response is successful
        $this->assertEquals('OK', $response->getContent());

        // Verify the tenant was set in the session
        $this->assertTrue(Session::has('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_handles_user_without_any_company_assignments(): void
    {
        $this->markTestIncomplete();

        // Create a test request
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        // Create the middleware instance
        $middleware = new \Modules\Core\Http\Middleware\ConfigureTenant();

        // Create a user with no company assignments
        $user = User::factory()->create();

        // Mock the Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Mock the redirect response
        $redirect = Mockery::mock('Illuminate\Routing\Redirector');
        $redirect->shouldReceive('route')
            ->once()
            ->with('error')
            ->andReturn(new \Illuminate\Http\RedirectResponse('/error'));

        $this->app->instance('redirect', $redirect);

        // Handle the request
        $response = $middleware->handle($request, function ($req) {
            $this->fail('Next middleware should not be called for users without company assignments');
        });

        // Assert the response is a redirect to error page
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(url('error'), $response->getTargetUrl());
    }

    #[Test]
    #[Group('unit')]
    public function it_preserves_tenant_across_requests(): void
    {
        $this->markTestIncomplete();

        // Create a test request
        $request = \Illuminate\Http\Request::create('/test', 'GET');

        // Create the middleware instance
        $middleware = new \Modules\Core\Http\Middleware\ConfigureTenant();

        // Create a user with a company
        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Set a current company in the session
        session(['current_company_id' => $company->id]);

        // Mock the Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->times(2)
            ->andReturn($user);

        // The session should not be updated since we already have a current company
        Session::shouldReceive('put')
            ->never();

        // Handle the request
        $response = $middleware->handle($request, function ($req) {
            return new \Illuminate\Http\Response('OK');
        });

        // Assert the response is successful
        $this->assertEquals('OK', $response->getContent());

        // Verify the tenant is still set in the session
        $this->assertEquals($company->id, session('current_company_id'));
    }
}
