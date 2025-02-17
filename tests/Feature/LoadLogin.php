<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LoadLogin extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
