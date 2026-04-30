<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    function index()
    {
        $data['datas'] = Role::where('name', '!=', 'Super Admin')->orderBy('id', 'DESC')->get();
        return view('backend.roles.index', $data);
    }

    function create()
    {

        $data['permissions']    =   Permission::orderBy('name')->get();
        $permissionIds = [];
        
         // return $permissionIds;
        $data['permissionIds'] = $permissionIds;

        $role_permission = Permission::orderBy('name')->get();

        $custom_permission = array();

        foreach($role_permission as $per){

            $key = substr($per->name, 0, strpos($per->name, ".")); 

            if(str_starts_with($per->name, $key)){
                $custom_permission[$key][] = $per;
            }
            
        }
   
        $data['custom_permission'] = $custom_permission;

        return view('backend.roles.create', $data);
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles')->with('success', 'Role created successfully.');
    }

    function edit($id)
    {
        $data['data'] = Role::find(decrypt($id));

        $data['permissions']    =   Permission::orderBy('name')->get();
        $permissionIds = [];
        $dats = Role::where('roles.id', decrypt($id))
                ->with('permissions')
                ->first();
               
        if($dats['permissions']){
            foreach ($dats['permissions'] as $key => $value) {
                array_push($permissionIds, $value->id);
            }
        }
         // return $permissionIds;
        $data['permissionIds'] = $permissionIds;

        $role_permission = Permission::orderBy('name')->get();

        $custom_permission = array();

        foreach($role_permission as $per){

            $key = substr($per->name, 0, strpos($per->name, ".")); 

            if(str_starts_with($per->name, $key)){
                $custom_permission[$key][] = $per;
            }
            
        }
   
        $data['custom_permission'] = $custom_permission;
     
        return view('backend.roles.edit', $data);
    }

    function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . decrypt($id),
        ]);

        $params['role'] = $request->name;
        User::where('role', $request->old_name)->update($params);

        $role = Role::find(decrypt($id));
        $role->name = $request->name;
        $role->save();

        // Update permissions
        $permissions = $request->permissions;
        if ($permissions) {
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles')->with('success', 'Role updated successfully.');
    }
}
