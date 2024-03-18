<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;

class UserUpdateController
{
    public function update(Request $request, int $id)
    {
        $data['user'] = User::find($id);

        return view('user.user', $data);
    }
}
