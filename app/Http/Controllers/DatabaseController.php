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

    public function listUsers()
    {
        $mariaDBService = new MariaDB();
        $data['users'] = $mariaDBService->listUsers();
        $data['success_message'] = session('success_message', null);
        return view('database.users', $data);
    }

    public function createUser()
    {
        $data = [];
        return view('database.user', $data);
    }

    public function saveUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:32',
            'host' => 'required|string|max:64',
            'password' => 'required_without:id|nullable|string|min:4',
            'confirm_password' => 'same:password',
            'privileges' => 'required|string',
        ]);
        $mariaDBService = new MariaDB();
        if (empty($request->id)) {
            $mariaDBService->createUser($request->username, $request->host, $request->password, $request->privileges, $request->database);
            $msg = __('User created successfully.');
        } else {
            $mariaDBService->updateUser($request->username, $request->host, $request->password, $request->privileges, $request->database);
            $msg = __('User updated successfully.');
        }
        return redirect('/database/users')->with('success_message', $msg);
    }

    public function editUser($username, $host)
    {
        $mariaDBService = new MariaDB();
        $data['user'] = $mariaDBService->getUser($username, $host);
        return view('database.user', $data);
    }

    public function deleteUser($username, $host)
    {
        $mariaDBService = new MariaDB();
        $mariaDBService->deleteUser($username, $host);
        return redirect('/database/users')->with('success_message', __('User deleted successfully.'));
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
