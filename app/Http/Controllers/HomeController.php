<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\User;
use App\Models\Database;
use App\Helpers\Linux;

class HomeController extends MainController
{


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
        $webServerName = explode('/', $serverSoftware)[0];
        $data['webserver_name'] = $webServerName;
        $data['php_version'] = phpversion();
        $data['server_uptime'] = Linux::getUptime();
        $data['website_count'] = Website::where('status', Website::ACTIVE)->count();
        $data['user_count'] = User::where('status', User::ACTIVE)->count();
        $data['database_count'] = Database::where('status', Database::ACTIVE)->count();
        return view('dashboard', $data);
    }
}
