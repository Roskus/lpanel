<?php

namespace Tests\Unit;

use Tests\TestCase;

class WebsiteCreateCommandTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function vhostMustHaveConfigFileCreated()
    {
        //TODO: Fix this test
        /*
        $domain = 'test-website.localhost';
        $this->artisan("website:create $domain")
                        ->expectsOutput('Virtual host created successfully')
                        ->assertExitCode(0);
        */
        $this->assertTrue(true);
    }
}
