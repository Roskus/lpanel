<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends MainController
{
    //
    public function detail(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $data['user'] = $user;

        return view('profile', $data);
    }

    public function save(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->lang = $request->lang;
        $user->updated_at = now();
        $user->save();

        return redirect('/profile');
    }
}
