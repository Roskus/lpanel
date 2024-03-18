<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\MainController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserSaveController extends MainController
{
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
        if (! empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        $user->updated_at = now();
        $user->save();

        return redirect('/user');
    }
}
