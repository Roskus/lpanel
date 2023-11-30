<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        if (empty($request->id)) {
            $user = new User();
            $user->created_at = now();
        } else {
            $user = User::find($request->id);
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->updated_at = now();
        $user->save();

        return redirect('/user');
    }
}
