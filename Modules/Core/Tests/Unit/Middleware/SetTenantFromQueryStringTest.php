<?php

namespace Modules\Core\Tests\Unit\Middleware;

use App\Http\Middleware\SetTenantFromQueryString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;

class SetTenantFromQueryStringTest extends AbstractTestCase
{
    private SetTenantFromQueryString $middleware;

    private $user;

    private $company1;

    private $company2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new SetTenantFromQueryString();

        // Create test companies
        $this->company1 = Company::factory()->create(['search_code' => 'comp1']);
        $this->company2 = Company::factory()->create(['search_code' => 'comp2']);

        // Create a test user with elevated role
        $this->user = $this->createUserWithRole(UserRole::SUPER_ADMIN);
        $this->user->companies()->attach($this->company1->id);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    #[Test]
    #[Group('unit')]
    public function it_sets_tenant_from_query_string_for_authenticated_user()
    {
        $this->markTestIncomplete();

        $request = Request::create('/', 'GET', ['company' => 'comp1']);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals($this->company1->id, Session::get('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_elevated_users_to_switch_to_any_company()
    {
        $this->markTestIncomplete();

        $request = Request::create('/', 'GET', ['company' => 'comp2']);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals($this->company2->id, Session::get('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_denies_switching_to_unauthorized_company_for_regular_users()
    {
        $this->markTestIncomplete();

        // Create a regular user with access only to company1
        $regularUser = $this->createUserWithRole(UserRole::CUSTOMER_USER);
        $regularUser->companies()->attach($this->company1->id);
        Auth::login($regularUser);

        $request = Request::create('/', 'GET', ['company' => 'comp2']);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertNotEquals($this->company2->id, Session::get('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_ignores_invalid_company_codes()
    {
        $this->markTestIncomplete();

        $request = Request::create('/', 'GET', ['company' => 'nonexistent']);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertNull(Session::get('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_does_nothing_when_no_company_parameter()
    {
        $this->markTestIncomplete();

        $request = Request::create('/');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertNull(Session::get('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_does_nothing_for_unauthenticated_users()
    {
        $this->markTestIncomplete();

        Auth::logout();

        $request = Request::create('/', 'GET', ['company' => 'comp1']);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertNull(Session::get('current_company_id'));
    }

    #[Test]
    #[Group('unit')]
    public function it_sets_tenant_parameter_in_route()
    {
        $this->markTestIncomplete();

        $request = Request::create('/some-route', 'GET', ['company' => 'comp1']);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('comp1', $req->route('tenant'));

            return response('OK');
        });
    }
}
