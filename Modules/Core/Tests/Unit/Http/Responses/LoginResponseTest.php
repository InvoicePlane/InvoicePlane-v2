<?php

namespace Modules\Core\Tests\Feature\Http\Responses;

use Modules\Core\Tests\AbstractTestCase;

class LoginResponseTest extends AbstractTestCase
{
    public function test_elevated_user_redirected_to_first_company()
    {
        $this->markTestIncomplete('Test elevated user is redirected to first company');
    }

    public function test_regular_user_redirected_to_their_company()
    {
        $this->markTestIncomplete('Test regular user is redirected to their company');
    }

    public function test_aborts_if_no_company_found_for_elevated_user()
    {
        $this->markTestIncomplete('Test handling when no companies exist for elevated user');
    }

    public function test_aborts_if_user_has_no_company()
    {
        $this->markTestIncomplete('Test handling when user has no company assigned');
    }
}
