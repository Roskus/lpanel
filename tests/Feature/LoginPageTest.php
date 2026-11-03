<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    #[Test]
    public function loginPageLoads(): void
    {
        $this->get('/login')->assertOk();
    }

    #[Test]
    public function loginPageDoesNotRevealTheVersion(): void
    {
        $this->get('/login')->assertDontSee('Version:');
    }
}
