<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\MainController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserCreateController extends MainController
{
    public function create(Request $request): View
    {
        $data['user'] = new User;

        return view('user.user', $data);
    }
}
