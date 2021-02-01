<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\User;
use App\Models\Database;

use App\Helpers\Linux;
use App\Helpers\File as FileHelper;

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
        $data['website_count'] = Website::where('status', Website::ACTIVE)->count();
        $data['user_count'] = User::where('status', User::ACTIVE)->count();
        $data['database_count'] = Database::where('status', Database::ACTIVE)->count();
        $data['disk_free'] = FileHelper::formatSize($disk_free);
        $data['disk_total'] = FileHelper::formatSize($disk_total);
        $data['disk_percentage'] = $disk_percentage;
        return view('dashboard', $data);
    }
}
