<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\MainController;
use App\Models\User;
use Illuminate\Http\Request;

class UserUpdateController extends  MainController
{
    public function update(Request $request, int $id)
    {
        $data['user'] = User::find($id);

        return view('user.user', $data);
    }
}
