<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Database;
use Illuminate\Http\Request;

class DatabaseController extends MainController
{
    //
    public function index(Request $request)
    {
        $data['databases'] = Database::all();

        return view('database.index', $data);
    }

    public function add(Request $request)
    {
        $database = new Database;
        $data['database'] = $database;

        return view('database.database', $data);
    }

    public function edit(Request $request, int $id)
    {
        $database = Database::find($id);
        $data['database'] = $database;

        return view('database.database', $data);
    }

    public function save(Request $request)
    {
        if (empty($request->id)) {
            $database = new Database;
            $database->created_at = now();
        } else {
            $database = Database::find($request->id);
        }
        $database->name = $request->name;
        $database->updated_at = now();
        $database->save();

        return redirect('/database');
    }
}
