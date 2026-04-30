<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $data['datas'] = User::where('role', '!=', 'Super Admin')->orderBy('id', 'DESC')->get();
        // $data['datas'] = User::orderBy('id', 'DESC')->get();
        return view('backend.users.index', $data);
    }

    function create()
    {
        $data['roles'] = Role::where('name', '!=', 'Super Admin')->get();
        return view('backend.users.create', $data);
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
        ]);

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    function edit($id)
    {
        $data['data'] = User::find(decrypt($id));
        $data['roles'] = Role::where('name', '!=', 'Super Admin')->get();
        return view('backend.users.edit', $data);
    }

    public function status($id)
    {
        $user = User::find(decrypt($id));
        if ($user->status == 'Active') {
            $user->status = 'Inactive';
        } else {
            $user->status = 'Active';
        }
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User status updated successfully.');
    }

    function update(Request $request, $id)
    {
        $user = User::find(decrypt($id));

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->save();

        // Sync roles
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
        
    }

    function delete($id)
    {
        $user = User::find(decrypt($id));
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }
}
