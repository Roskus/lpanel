<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;

class HomeController extends MainController
{


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data['website_count'] = Website::where('status', Website::ACTIVE)->count();
        return view('dashboard', $data);
    }
}
