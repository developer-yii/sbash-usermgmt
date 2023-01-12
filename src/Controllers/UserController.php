<?php
namespace Sbash\Usermgmt\Controllers;

use Illuminate\Http\Request;
use Sbash\Usermgmt\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function index() {        
        $role = Role::all();        
        return view('usermgmt::users.index', compact('role'));
    }

    public function getData(Request $request)
    {
        $user = User::query();
        if ($request->ajax()) {
          return datatables()->of($user)
            ->addColumn('created', function ($data) {
              return date('d M, Y', strtotime($data->created_at));
            })
            ->addColumn('actions', function ($data) {
              $button = '<button class="btn btn-primary waves-effect waves-light edit" id="' . $data->id . '" data-toggle="tooltip" data-placement="right" title="Edit Data"><i class="fa fa-edit"></i></button>';
              /* $button .= '<button class="btn btn-info ml-1 detail" id="' . $data->id . '" name="hapus"><i class="fa fa-eye"></i></button>'; */
              $button .= '<button class="btn btn-danger ml-1 delete" id="' . $data->id . '" name="delete"><i class="fa fa-trash"></i></button>';
              return $button;
            })->rawColumns(['actions', 'created'])
            ->make(true);
        }
    }

    public function add(Request $request)
    {
        if (!auth()->user()->can('user_add')) {
          return response()->json(['message' => 'You dont have Permission to Add User'], 422);
        }
        $rules = [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          'password' => ['required', 'string', 'min:8'],
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];
            return response()->json($result);            
          // return response()->json(['message' => trans('notification.user_add_failed'), 'message' => $validator->errors()->first()], 422);
        }

        $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        $result = ['status' => true, 'message' => 'User Added', 'data' => []];
        return response()->json($result);
        // return response()->json(['message' => trans('notification.user_added')], 200);
    }

    public function getDetails(Request $request)
    {
        if (!auth()->user()->can('user_edit')) {
            return response()->json(['message' => 'You dont have Permission to Edit user'], 422);
        }
        $data = User::find($request->id);

        if (!$data) {
          return response()->json(['message' => 'User not found'], 404);
        }

        $data->roles->pluck('name');

        return response()->json($data);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('user_edit')) {
          return response()->json(['message' => 'You dont have Permission to update user'], 422);
        }
        $id = $request->id_edit;

        $rules = [
          'name' => ['required', 'string', 'max:255'],
          'role' => 'required',
          'email' => 'required|string|email|max:255,' . $id,
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];
            return response()->json($result);
        }

        $user = User::find($id);
        if (!$user) {
          return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->password) {
          $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
          ]);
        } else {
          $user->update([
            'name' => $request->name,
            'email' => $request->email,
          ]);
        }
        $user->syncRoles($request->role);

        $result = ['status' => true, 'message' => 'User Updated', 'data' => []];
        return response()->json($result);        
    }

    public function delete(Request $request)
    {
        if (!auth()->user()->can('user_delete')) {
          return response()->json(['message' => 'You dont have Permission to delete User'], 422);
        }
        $data = User::find($request->id);

        if (!$data) {
          return response()->json(['message' => 'User not found'], 404);
        }
        $data->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }
}