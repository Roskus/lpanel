<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserIndexController
{
    public function index(Request $request): View
    {
        $data['users'] = User::all();

        return view('user.index', $data);
    }
}
