<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Database;

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
        $database = New Database();
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

    }
}
