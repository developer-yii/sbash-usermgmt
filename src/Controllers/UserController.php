<?php
namespace Sbash\Usermgmt\Controllers;

use Illuminate\Http\Request;
use Sbash\Usermgmt\Controllers\Controller;
use Sbash\Usermgmt\Mail\SetPasswordEmail;
use Spatie\Permission\Models\Role;
use Sbash\Orgmgmt\Models\Organization;
use Sbash\Orgmgmt\Models\UserOrganization;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {   
        if(class_exists('App\Http\Middleware\CheckSubscription') && class_exists('App\Http\Middleware\PreventBackHistory')){        
            $this->middleware(['check.subscription', 'preventBackHistory']);        
        }     
    }
    
    public function index() {     
        $user = \Auth::user();

        if (!$user->can('user_list')) {
          return redirect()->back()->with(['flash_message_error' => trans('usermgmt')['permission']['no_access_users_page']]);
        }  

        if(!count($user->userOrganizations()->get()) && !$user->can('permission_list')){
          return redirect()->back()->with(['flash_message_error' => trans('usermgmt')['notification']['update_org_settings']]);
        }

        $role = ($user->hasRole('User')) ? Role::where('name','User')->get() : Role::all();

        return view('usermgmt::users.index', compact('role'));
    }

    public function getData(Request $request)
    {
        $loginuser = \Auth::user();

        $orgId = session()->get('organization_id', '');

        if($loginuser->can('permission_list'))
        {
          $user = User::with('roles')->get();
        }
        else if($orgId)
        {
          $user = User::with('roles')
                ->join('user_organizations','users.id','user_organizations.user_id')
                ->where('user_organizations.organization_id',$orgId)
                ->select('users.*')
                ->get();
        }     
        else
        { 
          $user = User::with('roles')
                ->where('id',$loginuser->id)
                ->get();
        }        

        if ($request->ajax()) {
          return datatables()->of($user)
            ->addColumn('role', function(User $user){
                return $user->roles->pluck('name')->implode(', ');
            })
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
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_add_user']], 422);
        }
        $rules = [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          'password' => ['required', 'string', 'min:8'],
        ];

        $messages = [
          'name.required' => trans('validation')['required'],
          'name.max' => trans('validation')['max']['string'],
          'email.required' => trans('validation')['required'],
          'email.email' => trans('validation')['email'],
          'email.max' => trans('validation')['max']['string'],
          'email.unique' => trans('validation')['unique'],
          'password.required' => trans('validation')['required'],
          'password.min' => trans('validation')['min']['string'],
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];
            return response()->json($result);            
          // return response()->json(['message' => trans('notification.user_add_failed'), 'message' => $validator->errors()->first()], 422);
        }

        $orgId = session()->get('organization_id', '');

        if(!$orgId && !auth()->user()->can('permission_list'))
        {
          return response()->json(['message' => trans('usermgmt')['notification']['update_org_settings']], 422);
        }

        $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make($request->password),
        ]);

        $setPasswordLink = URL::temporarySignedRoute(
            'set-password.create',
            now()->addHours(24),
            ['user' => $user->id]
        );

        // Mail::to($user->email)->from('setaro@mail.com', 'Setaro')->send(new SetPasswordEmail($setPasswordLink));
        // Mail::send(new SetPasswordEmail($setPasswordLink), [], function ($message) use ($user) {            
        //     $message->to($user->email);
        // });

        $subject = trans('usermgmt')['mails']['account_created_subject'];

        $from = config('mail.from.address');
        $name = config('mail.from.name');

        if(session('organization_id')){
          $org = Organization::find(session('organization_id'));
          if($org){
            $from = $org->email;
            $name = $org->name;
          }
        }

        try {

            // Mail::to($user->email)->send(new SetPasswordEmail($setPasswordLink), [], function ($message) use ($user) {            
            //     $message;
            // });

            Mail::to($user->email)->send(new SetPasswordEmail($setPasswordLink, $request->name, $name, $orgId), function ($message) use ($user, $from, $name) {
                $message->from($from,$name);
            });

            // Mail::send('usermgmt::mails.set_password',['setPasswordLink' => $setPasswordLink,'name' => $request->name,'org' => $name], function ($message) use ($user,$subject,$from,$name) {
            //     $message->from($from,$name)
            //     ->to($user->email)
            //     ->subject($subject);
            // });
            
        } catch (\Exception $e) {            
            $result = ['status' => false, 'other' => true, 'message' => $e->getMessage(), 'data' => []];
            return response()->json($result);
        }

        $user->assignRole($request->role);

        if($user && !auth()->user()->can('permission_list'))
        {
          $userOrg = UserOrganization::create([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'user_type' => 'users',
            'access_type' => 2,
          ]); 
        }

        $result = ['status' => true, 'message' => trans('usermgmt')['notification']['user_added'], 'data' => []];
        return response()->json($result);
        // return response()->json(['message' => trans('notification.user_added')], 200);
    }

    public function getDetails(Request $request)
    {
        if (!auth()->user()->can('user_edit')) {
            return response()->json(['message' => trans('usermgmt')['permission']['no_perm_edit_user']], 422);
        }
        $data = User::find($request->id);

        if (!$data) {
          return response()->json(['message' => trans('usermgmt')['notification']['user_not_found']], 404);
        }

        $data->roles->pluck('name');

        return response()->json($data);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('user_edit')) {
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_update_user']], 422);
        }

        $id = $request->id_edit;

        if (!$user->can('permission_edit') && !$user->isOrganizationOwner(session('organization_id')) && $user->id != $id) {
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_update_user']], 422);
        }

        $rules = [
          'name' => ['required', 'string', 'max:255'],
          'role' => 'required',
          'email' => 'required|string|email|max:255,' . $id,
        ];

        $messages = [
          'name.required' => trans('validation')['required'],
          'name.max' => trans('validation')['max']['string'],
          'role.required' => trans('validation')['required'],
          'email.required' => trans('validation')['required'],
          'email.email' => trans('validation')['email'],
          'email.max' => trans('validation')['max']['string'],
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];
            return response()->json($result);
        }

        $user = User::find($id);
        if (!$user) {
          return response()->json(['message' => trans('usermgmt')['notification']['user_not_found']], 404);
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

        $result = ['status' => true, 'message' => trans('usermgmt')['notification']['user_updated'], 'data' => []];
        return response()->json($result);        
    }

    public function delete(Request $request)
    {
        $user = auth()->user();
        
        // Can not delete if user is not having delete permission
        if (!$user->can('user_delete')) {
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_delete_user']], 422);
        }

        // Can not delete if user is not admin or organization owner
        if (!$user->can('permission_edit') && !$user->isOrganizationOwner(session('organization_id'))) {
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_delete_user']], 422);
        }

        // Can not delete self
        if($user->id == $request->id)
        {
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_delete_self']], 422); 
        }

        $data = User::find($request->id);

        if (!$data) {
          return response()->json(['message' => trans('usermgmt')['notification']['user_not_found']], 404);
        }
        $data->delete();
        return response()->json(['message' => trans('usermgmt')['notification']['delete_user_success']], 200);
    }
}