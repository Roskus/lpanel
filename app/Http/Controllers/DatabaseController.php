<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Database;
use App\Service\Database\MariaDB;
use Illuminate\Http\Request;

class DatabaseController extends MainController
{
    public function index(Request $request)
    {
        $mariaDBService = new MariaDB();
        $data['databases'] = $mariaDBService->listDatabases();

        // Mensaje de éxito si existe en la sesión
        $data['success_message'] = session('success_message', null);

        return view('database.index', $data);
    }

    public function create(Request $request)
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
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $mariaDBService = new MariaDB();

        if (empty($request->id)) {
            $database = new Database;
            $database->created_at = now();

            // Crear la base de datos si no existe
            $mariaDBService->createDatabase($request->name);
        } else {
            $database = Database::find($request->id);
        }

        $database->name = $request->name;
        $database->updated_at = now();
        $database->save();

        return redirect('/database');
    }

    public function createUser()
    {
        $data = [];
        return view('database.user', $data);
    }

    public function delete(Request $request, string $name)
    {
        $mariaDBService = new MariaDB();

        // Eliminar la base de datos
        $mariaDBService->deleteDatabase($name);

        // Mensaje de éxito
        return redirect('/database')->with('success_message', __('Database ":name" deleted successfully.', ['name' => $name]));
    }
}
