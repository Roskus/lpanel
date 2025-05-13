<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\File as FileHelper;
use App\Helpers\Linux;
use App\Models\User;
use App\Service\Database\MariaDB;
use App\Service\Webserver\Apache;
use App\Service\Webserver\Nginx;

class HomeController extends MainController
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $disk_percentage = 0;
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
        $webServerName = explode('/', $serverSoftware)[0];

        $disk_free = disk_free_space(env('SERVER_DISK_UNIT'));
        $disk_total = disk_total_space(env('SERVER_DISK_UNIT'));
        if ($disk_total > 0) {
            $disk_percentage = ($disk_free * 100) / $disk_total;
            $disk_percentage = round(100 - $disk_percentage, 0);
        }
        $data['webserver_name'] = $webServerName;
        $data['php_version'] = phpversion();
        $data['server_uptime'] = Linux::getUptime();
        $apacheService = new Apache();
        $nginxService = new Nginx();
        $mariaDBService = new MariaDB();

        $apacheSites = count($apacheService->getEnabledSites());
        $nginxSites = count($nginxService->getEnabledSites());

        $data['website_count'] = $apacheSites + $nginxSites;
        $data['user_count'] = User::where('status', User::ACTIVE)->count();

        // Obtener bases de datos ignorando las del sistema
        $systemDatabases = ['mysql', 'information_schema', 'performance_schema', 'sys'];
        $databases = $mariaDBService->listDatabases();
        $data['database_count'] = count(array_diff($databases, $systemDatabases));

        $data['disk_free'] = FileHelper::formatSize($disk_free);
        $data['disk_total'] = FileHelper::formatSize($disk_total);
        $data['disk_percentage'] = $disk_percentage;

        return view('dashboard', $data);
    }
}

