<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhpInfoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guestIsRedirectedToLogin(): void
    {
        $this->get('/phpinfo')->assertRedirect('/login');
    }

    #[Test]
    public function userCanSeePhpInfoWithoutEnvironmentVariables(): void
    {
        $_ENV['LPANEL_TEST_SECRET'] = 'do-not-show-this';
        $_SERVER['LPANEL_TEST_SECRET'] = 'do-not-show-this';

        $this->actingAs(User::factory()->create(['lang' => 'en']));

        $this->get('/phpinfo')
            ->assertOk()
            ->assertSee('PHP Version')
            ->assertDontSee('do-not-show-this');

        unset($_ENV['LPANEL_TEST_SECRET'], $_SERVER['LPANEL_TEST_SECRET']);
    }
}
