<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\MainController;
use App\Models\User;
use Illuminate\Http\Request;

class UserDeleteController extends MainController
{
    public function delete(Request $request, int $id)
    {
        $user = User::find($id);
        $user->delete();

        return redirect('/user');
    }
}
