<?php

namespace Modules\Core\Tests\Feature\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Traits\BelongsToCompany;
use PHPUnit\Framework\Attributes\Test;

class BelongsToCompanyTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test table if it doesn't exist
        if ( ! Schema::hasTable('test_models')) {
            Schema::create('test_models', function ($table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
                $table->string('name');
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        parent::tearDown();
    }

    #[Test]
    public function it_filters_models_by_company_using_global_scope()
    {
        $this->markTestIncomplete();

        // Create two companies
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        // Create test models for both companies
        $model1             = new BelongsToCompanyTestModel();
        $model1->name       = 'Model 1';
        $model1->company_id = $company1->id;
        $model1->save();

        $model2             = new BelongsToCompanyTestModel();
        $model2->name       = 'Model 2';
        $model2->company_id = $company2->id;
        $model2->save();

        // Act as a user from company1
        $user = User::factory()->create();
        $user->companies()->attach($company1);
        Auth::login($user);

        // Set the current company in session
        session(['current_company_id' => $company1->id]);

        // Verify only company1's models are returned
        $models = BelongsToCompanyTestModel::all();
        $this->assertCount(1, $models);
        $this->assertEquals($company1->id, $models->first()->company_id);
        $this->assertEquals('Model 1', $models->first()->name);
    }

    #[Test]
    public function it_can_scope_queries_to_specific_company()
    {
        $this->markTestIncomplete();

        // Create companies and test models
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $model1             = new BelongsToCompanyTestModel();
        $model1->name       = 'Model 1';
        $model1->company_id = $company1->id;
        $model1->save();

        $model2             = new BelongsToCompanyTestModel();
        $model2->name       = 'Model 2';
        $model2->company_id = $company2->id;
        $model2->save();

        // Test scoping to company1
        $models = BelongsToCompanyTestModel::forCompany($company1->id)->get();
        $this->assertCount(1, $models);
        $this->assertEquals($company1->id, $models->first()->company_id);
        $this->assertEquals('Model 1', $models->first()->name);

        // Test scoping to company2
        $models = BelongsToCompanyTestModel::forCompany($company2->id)->get();
        $this->assertCount(1, $models);
        $this->assertEquals($company2->id, $models->first()->company_id);
        $this->assertEquals('Model 2', $models->first()->name);
    }

    #[Test]
    public function it_defines_company_relationship()
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();

        $model             = new BelongsToCompanyTestModel();
        $model->name       = 'Test Model';
        $model->company_id = $company->id;
        $model->save();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->company());
        $this->assertTrue($model->company->is($company));
        $this->assertEquals($company->id, $model->company->id);
    }

    #[Test]
    public function it_sets_company_id_on_model_creation()
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();

        // Set up authenticated user with a company
        $user = User::factory()->create();
        $user->companies()->attach($company);
        Auth::login($user);
        session(['current_company_id' => $company->id]);

        // Create model without explicitly setting company_id
        $model       = new BelongsToCompanyTestModel();
        $model->name = 'Auto Company Model';
        $model->save();

        // Verify company_id was set automatically
        $this->assertNotNull($model->company_id);
        $this->assertEquals($company->id, $model->company_id);

        // Test that explicitly set company_id is not overridden
        $otherCompany       = Company::factory()->create();
        $model2             = new BelongsToCompanyTestModel();
        $model2->name       = 'Manual Company Model';
        $model2->company_id = $otherCompany->id;
        $model2->save();

        $this->assertEquals($otherCompany->id, $model2->company_id);
    }

    #[Test]
    public function it_handles_unauthenticated_users()
    {
        $this->markTestIncomplete();

        // Create a company and model first
        $company           = Company::factory()->create();
        $model             = new BelongsToCompanyTestModel();
        $model->name       = 'Test Model';
        $model->company_id = $company->id;
        $model->save();

        // Logout and clear any existing auth
        Auth::logout();
        session()->flush();

        // Should not throw an exception but should return no results
        $models = BelongsToCompanyTestModel::all();
        $this->assertCount(0, $models);
    }
}

// Test model that uses the BelongsToCompany trait
class BelongsToCompanyTestModel extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $table = 'test_models';

    protected $guarded = [];
}
