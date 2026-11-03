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

class WebsiteStateTest extends TestCase
{
    use RefreshDatabase;

    private const DOMAIN = 'test-website.localhost';

    private string $available;

    private string $link;

    protected function setUp(): void
    {
        parent::setUp();

        Process::fake();
        $path = sys_get_temp_dir().'/lpanel_'.uniqid();
        File::makeDirectory("$path/nginx/sites-available", 0755, true);
        File::makeDirectory("$path/nginx/sites-enabled");
        config(['app.webserver_path' => $path, 'app.websites_path' => "$path/www"]);
        $this->available = "$path/nginx/sites-available/".self::DOMAIN.'.conf';
        $this->link = "$path/nginx/sites-enabled/".self::DOMAIN.'.conf';

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(config('app.webserver_path'));

        parent::tearDown();
    }

    #[Test]
    public function websiteCanBeDisabledAndEnabled(): void
    {
        $this->artisan('panel:website:disable', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertFalse(is_link($this->link));
        $this->assertFileExists($this->available);
        $this->assertDatabaseHas('website', ['url' => self::DOMAIN, 'status' => Website::INACTIVE]);

        $this->artisan('panel:website:enable', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertTrue(is_link($this->link));
        $this->assertDatabaseHas('website', ['url' => self::DOMAIN, 'status' => Website::ACTIVE]);
    }

    #[Test]
    public function linkIsRestoredWhenReloadFails(): void
    {
        Process::fake(fn () => Process::result(errorOutput: 'bad config', exitCode: 1));

        $this->artisan('panel:website:disable', ['domain' => self::DOMAIN])
            ->expectsOutput('Error reloading webserver: bad config')
            ->assertExitCode(1);

        $this->assertTrue(is_link($this->link));
        $this->assertDatabaseHas('website', ['url' => self::DOMAIN, 'status' => Website::ACTIVE]);
    }

    #[Test]
    public function disabledWebsiteStaysDisabledWhenRecreateFails(): void
    {
        $this->artisan('panel:website:disable', ['domain' => self::DOMAIN])->assertExitCode(0);
        Process::fake(fn () => Process::result(errorOutput: 'bad config', exitCode: 1));

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(1);

        $this->assertFalse(is_link($this->link));
    }

    #[Test]
    public function unknownOrInvalidDomainIsRejected(): void
    {
        foreach (['unknown.localhost', '../../evil'] as $domain) {
            $this->artisan('panel:website:disable', ['domain' => $domain])
                ->expectsOutput("Website not found in sites-available: $domain")
                ->assertExitCode(1);
        }
    }

    #[Test]
    public function websiteCanBeDisabledFromTheWeb(): void
    {
        $this->actingAs(User::factory()->create(['lang' => 'en']));

        $this->post('/site/disable', ['domain' => self::DOMAIN])->assertRedirect('/site');
        $this->assertFalse(is_link($this->link));

        $this->get('/site')->assertOk()->assertSee('Enable')->assertSee(self::DOMAIN);

        $this->post('/site/enable', ['domain' => self::DOMAIN])->assertRedirect('/site');
        $this->assertTrue(is_link($this->link));
    }

    #[Test]
    public function buttonsAreHiddenForSitesThatCannotChangeState(): void
    {
        $path = config('app.webserver_path').'/nginx';
        File::put("$path/sites-available/default", '');
        $this->actingAs(User::factory()->create(['lang' => 'en']));

        $this->get('/site')->assertOk()->assertDontSee('value="default"', false);

        File::deleteDirectory("$path/sites-available");
        File::deleteDirectory("$path/sites-enabled");
        File::makeDirectory("$path/conf.d");
        File::put("$path/conf.d/".self::DOMAIN.'.conf', '');

        $this->get('/site')->assertOk()->assertSee(self::DOMAIN)->assertDontSee('/site/disable');
    }

    #[Test]
    public function errorIsShownInTheWebsitesList(): void
    {
        $this->actingAs(User::factory()->create(['lang' => 'en']));

        $this->post('/site/disable', ['domain' => 'unknown.localhost'])
            ->assertRedirect('/site')
            ->assertSessionHasErrors('domain');
    }
}
