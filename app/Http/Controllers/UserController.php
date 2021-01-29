<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends MainController
{
    //
    public function index(Request $request)
    {
        $data['users'] = User::all();
        return view('user.index', $data);
    }

    public function add(Request $request)
    {
        $data['user'] = new User();
        return view('user.user', $data);
    }

    public function edit(Request $request, int $id)
    {
        $data['user'] = User::find($id);
        return view('user.user', $data);
    }

    public function save(Request $request)
    {

    }
}
