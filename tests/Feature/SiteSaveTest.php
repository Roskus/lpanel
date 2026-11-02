<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiteSaveTest extends TestCase
{
    use RefreshDatabase;

    private const DOMAIN = 'test-website.localhost';

    private string $webserverPath;

    protected function setUp(): void
    {
        parent::setUp();

        Process::fake();
        $this->webserverPath = sys_get_temp_dir().'/lpanel_'.uniqid();
        File::makeDirectory("{$this->webserverPath}/nginx", 0755, true);
        config([
            'app.webserver_path' => $this->webserverPath,
            'app.websites_path' => "{$this->webserverPath}/www",
        ]);
        $this->actingAs(User::factory()->create(['lang' => 'en']));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->webserverPath);

        parent::tearDown();
    }

    #[Test]
    public function websiteIsSavedWithItsVhost(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");

        $this->post('/site/save', [
            'url' => 'https://'.self::DOMAIN,
            'type' => 'nginx',
        ])->assertRedirect('/site')->assertSessionHasNoErrors();

        $this->assertFileExists("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
        $this->assertDatabaseHas('website', ['url' => self::DOMAIN, 'http' => true, 'https' => false]);
    }

    #[Test]
    public function aliasesAreAddedToTheVhost(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");

        $this->post('/site/save', [
            'url' => self::DOMAIN,
            'type' => 'nginx',
            'alias' => "www.test-website.localhost\r\n\r\nhttps://other.localhost\r\n",
        ])->assertRedirect('/site')->assertSessionHasNoErrors();

        $config = File::get("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
        $this->assertStringContainsString('server_name '.self::DOMAIN.' www.test-website.localhost other.localhost;', $config);
        $this->assertDatabaseHas('website', [
            'url' => self::DOMAIN,
            'alias' => '["www.test-website.localhost","other.localhost"]',
        ]);
    }

    #[Test]
    public function invalidAliasIsRejected(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");

        $this->from('/site/create')->post('/site/save', [
            'url' => self::DOMAIN,
            'type' => 'nginx',
            'alias' => 'bad; alias',
        ])->assertSessionHasErrors(['url' => 'Invalid alias: bad; alias']);

        $this->assertFileDoesNotExist("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
        $this->assertDatabaseCount('website', 0);
    }

    #[Test]
    public function errorIsShownAndWebsiteIsNotSavedWhenVhostFails(): void
    {
        $this->from('/site/create')->post('/site/save', [
            'url' => self::DOMAIN,
            'type' => 'nginx',
        ])->assertRedirect('/site/create')->assertSessionHasErrors('url');

        $this->assertDatabaseCount('website', 0);
    }

    #[Test]
    public function apacheShowsUnsupportedError(): void
    {
        $this->from('/site/create')->post('/site/save', [
            'url' => self::DOMAIN,
            'type' => 'apache',
        ])->assertSessionHasErrors(['url' => 'Unsupported webserver: apache']);

        $this->assertDatabaseCount('website', 0);
    }
}
