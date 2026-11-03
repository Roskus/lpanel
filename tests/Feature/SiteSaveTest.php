<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Website;
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
    public function deletedWebsiteIsRestoredWithTheNewAliases(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx', 'alias' => 'old.localhost']);
        Website::where('url', self::DOMAIN)->first()->delete();

        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx', 'alias' => 'new.localhost'])
            ->assertRedirect('/site');

        $this->assertDatabaseCount('website', 1);
        $this->assertNotSoftDeleted('website', ['url' => self::DOMAIN, 'alias' => '["new.localhost"]']);
        $this->assertStringContainsString(
            'server_name '.self::DOMAIN.' new.localhost;',
            File::get("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf')
        );
    }

    #[Test]
    public function onlyStoredWebsitesHaveAnEditAction(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx']);
        File::put("{$this->webserverPath}/nginx/conf.d/unknown.localhost.conf", '');
        $id = Website::where('url', self::DOMAIN)->value('id');

        $response = $this->get('/site')->assertOk();

        $response->assertSee("/site/edit/$id", false);
        $this->assertSame(1, substr_count($response->getContent(), '/site/edit/'));
    }

    #[Test]
    public function editFormShowsTheStoredAliases(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx', 'alias' => "a.localhost\nb.localhost"]);
        $id = Website::where('url', self::DOMAIN)->value('id');

        $this->get("/site/edit/$id")
            ->assertOk()
            ->assertSee("a.localhost\nb.localhost", false);
    }

    #[Test]
    public function editingChangesTheAliasesOfTheVhost(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/conf.d");
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx', 'alias' => 'old.localhost']);
        $id = Website::where('url', self::DOMAIN)->value('id');

        $this->post('/site/save', [
            'id' => $id,
            'url' => self::DOMAIN,
            'type' => 'nginx',
            'alias' => 'new.localhost',
        ])->assertRedirect('/site')->assertSessionHasNoErrors();

        $config = File::get("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
        $this->assertStringContainsString('server_name '.self::DOMAIN.' new.localhost;', $config);
        $this->assertStringNotContainsString('old.localhost', $config);
        $this->assertDatabaseHas('website', ['id' => $id, 'alias' => '["new.localhost"]']);
    }

    #[Test]
    public function editingKeepsADisabledWebsiteDisabled(): void
    {
        File::makeDirectory("{$this->webserverPath}/nginx/sites-available");
        File::makeDirectory("{$this->webserverPath}/nginx/sites-enabled");
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx']);
        $this->post('/site/disable', ['domain' => self::DOMAIN]);
        $id = Website::where('url', self::DOMAIN)->value('id');

        $this->post('/site/save', ['id' => $id, 'url' => self::DOMAIN, 'type' => 'nginx', 'alias' => 'www.test-website.localhost'])
            ->assertRedirect('/site')->assertSessionHasNoErrors();

        $this->assertFalse(is_link("{$this->webserverPath}/nginx/sites-enabled/".self::DOMAIN.'.conf'));
        $this->assertStringContainsString(
            'www.test-website.localhost',
            File::get("{$this->webserverPath}/nginx/sites-available/".self::DOMAIN.'.conf')
        );
        $this->assertDatabaseHas('website', ['id' => $id, 'status' => Website::INACTIVE]);
    }

    #[Test]
    public function editingTheDomainRenamesTheWebsite(): void
    {
        $nginx = $this->useSitesEnabled();
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx', 'alias' => 'www.test-website.localhost']);
        File::put("{$this->webserverPath}/www/".self::DOMAIN.'/public/app.php', 'app');
        $id = Website::where('url', self::DOMAIN)->value('id');

        $this->post('/site/save', [
            'id' => $id,
            'url' => 'https://renamed.localhost',
            'type' => 'nginx',
            'alias' => "www.renamed.localhost\nother.localhost",
        ])->assertRedirect('/site')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('website', ['id' => $id, 'url' => 'renamed.localhost', 'status' => Website::ACTIVE]);
        $this->assertFileDoesNotExist("$nginx/sites-available/".self::DOMAIN.'.conf');
        $this->assertFalse(is_link("$nginx/sites-enabled/".self::DOMAIN.'.conf'));
        $this->assertTrue(is_link("$nginx/sites-enabled/renamed.localhost.conf"));
        $config = File::get("$nginx/sites-available/renamed.localhost.conf");
        $this->assertStringContainsString('server_name renamed.localhost www.renamed.localhost other.localhost;', $config);
        $this->assertStringContainsString("root {$this->webserverPath}/www/renamed.localhost/public;", $config);
        $this->assertFileExists("{$this->webserverPath}/www/renamed.localhost/public/app.php");
        $this->assertDirectoryDoesNotExist("{$this->webserverPath}/www/".self::DOMAIN);
    }

    #[Test]
    public function renamingKeepsADisabledWebsiteDisabled(): void
    {
        $nginx = $this->useSitesEnabled();
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx']);
        $this->post('/site/disable', ['domain' => self::DOMAIN]);
        $id = Website::where('url', self::DOMAIN)->value('id');

        $this->post('/site/save', ['id' => $id, 'url' => 'renamed.localhost', 'type' => 'nginx'])
            ->assertSessionHasNoErrors();

        $this->assertFileExists("$nginx/sites-available/renamed.localhost.conf");
        $this->assertFalse(is_link("$nginx/sites-enabled/renamed.localhost.conf"));
        $this->assertDatabaseHas('website', ['id' => $id, 'url' => 'renamed.localhost', 'status' => Website::INACTIVE]);
    }

    #[Test]
    public function renamingToADomainInUseFails(): void
    {
        $nginx = $this->useSitesEnabled();
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx']);
        $this->post('/site/save', ['url' => 'taken.localhost', 'type' => 'nginx']);
        $id = Website::where('url', self::DOMAIN)->value('id');

        $this->from("/site/edit/$id")
            ->post('/site/save', ['id' => $id, 'url' => 'taken.localhost', 'type' => 'nginx', 'alias' => 'new.localhost'])
            ->assertRedirect("/site/edit/$id")
            ->assertSessionHasErrors(['url' => 'Domain already in use: taken.localhost']);

        $this->assertDatabaseHas('website', ['id' => $id, 'url' => self::DOMAIN, 'alias' => '[]']);
        $this->assertTrue(is_link("$nginx/sites-enabled/".self::DOMAIN.'.conf'));
    }

    #[Test]
    public function renameIsUndoneWhenTheWebserverRejectsIt(): void
    {
        $nginx = $this->useSitesEnabled();
        $this->post('/site/save', ['url' => self::DOMAIN, 'type' => 'nginx']);
        $id = Website::where('url', self::DOMAIN)->value('id');
        $config = File::get("$nginx/sites-available/".self::DOMAIN.'.conf');
        Process::fake(fn () => Process::result(errorOutput: 'bad config', exitCode: 1));

        $this->post('/site/save', ['id' => $id, 'url' => 'renamed.localhost', 'type' => 'nginx'])
            ->assertSessionHasErrors(['url' => 'Error reloading webserver: bad config']);

        $this->assertDatabaseHas('website', ['id' => $id, 'url' => self::DOMAIN]);
        $this->assertSame($config, File::get("$nginx/sites-available/".self::DOMAIN.'.conf'));
        $this->assertTrue(is_link("$nginx/sites-enabled/".self::DOMAIN.'.conf'));
        $this->assertFileDoesNotExist("$nginx/sites-available/renamed.localhost.conf");
        $this->assertFalse(is_link("$nginx/sites-enabled/renamed.localhost.conf"));
        $this->assertDirectoryExists("{$this->webserverPath}/www/".self::DOMAIN.'/public');
        $this->assertDirectoryDoesNotExist("{$this->webserverPath}/www/renamed.localhost");
    }

    private function useSitesEnabled(): string
    {
        $nginx = "{$this->webserverPath}/nginx";
        File::makeDirectory("$nginx/sites-available");
        File::makeDirectory("$nginx/sites-enabled");

        return $nginx;
    }

    #[Test]
    public function editingAnUnknownWebsiteReturnsNotFound(): void
    {
        $this->get('/site/edit/999')->assertNotFound();
        $this->post('/site/save', ['id' => 999, 'url' => self::DOMAIN, 'type' => 'nginx'])->assertNotFound();
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
