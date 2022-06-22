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
        $domain = 'test-website.localhost';
        $this->artisan("website:create $domain")
                        ->expectsOutput('Virtual host created successfuly')
                        ->assertExitCode(0);        
    }
}
