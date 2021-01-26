<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
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
        return view('dashboard', $data);
    }
}
