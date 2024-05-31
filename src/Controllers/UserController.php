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
        if(class_exists('App\Http\Middleware\PreventBackHistory')){
            $this->middleware(['preventBackHistory']);
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
              $button = '<button class="btn btn-primary waves-effect waves-light edit" id="' . $data->id . '" data-toggle="tooltip" data-placement="right" title="'.__('usermgmt')['table']['edit_data'].'"><i class="fa fa-edit"></i></button>';
              /* $button .= '<button class="btn btn-info ml-1 detail" id="' . $data->id . '" name="hapus"><i class="fa fa-eye"></i></button>'; */
              $button .= '<button class="btn btn-danger ml-1 delete" id="' . $data->id . '" name="delete"><i class="fa fa-trash"></i></button>';
              return $button;
            })->rawColumns(['actions', 'created'])
            ->make(true);
        }
    }

    public function add(Request $request)
    {
        if (!$this->hasAddPermission()) {
          return response()->json(['message' => trans('usermgmt')['permission']['no_perm_add_user']], 422);
        }

        $validator = $this->validateRequest($request, 'add');

        if ($validator->fails()) {
          return $this->responseWithValidationErrors($validator);
        }

        $orgId = session()->get('organization_id', '');

        if(!$orgId && !auth()->user()->can('permission_list'))
        {
          return response()->json(['message' => trans('usermgmt')['notification']['update_org_settings']], 422);
        }

        $user = $this->createUser($request);

        $setPasswordLink = $this->generateSetPasswordLink($user);

        $emailDetails = $this->getEmailDetails($orgId, $request->name);

        try {
            $this->sendSetPasswordEmail($user, $setPasswordLink, $emailDetails);
        } catch (\Exception $e) {
            return $this->responseWithException($e);
        }

        $user->assignRole($request->role);
        $this->assignUserToOrganization($user, $orgId);

        $result = ['status' => true, 'message' => trans('usermgmt')['notification']['user_added'], 'data' => []];
        return response()->json($result);
    }

    public function getDetails(Request $request)
    {
        if (!(
          (auth()->user()->can('user_edit') && $this->isOrganizationAdmins(session('organization_id'))) ||
          (auth()->user()->hasAnyRole(['level_2', 'level_3']) && auth()->user()->can('user_edit'))
        )) {
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
        $id = $request->id_edit;

        if (!(
          ($user->can('user_edit') && $this->isOrganizationAdmins(session('organization_id')) && $user->id != $id) ||
          ($user->hasAnyRole(['level_2', 'level_3']) && $user->can('user_edit'))
        )) {
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
        // Can not delete if user is not admin or organization owner
        if (!(
          ($user->can('user_delete') && $this->isOrganizationAdmins(session('organization_id'))) ||
          ($user->hasAnyRole(['level_2', 'level_3']) && $user->can('user_delete'))
        )) {
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

    public function isOrganizationAdmins($organizationId = null)
    {
      $organizationId = $organizationId ?? session('organization_id');

      $orgAdmin = UserOrganization::where('organization_id', $organizationId)->where('user_id', auth()->user()->id)->whereIn('access_type', [1, 3])->first();
      if ($orgAdmin) {
        return true;
      }
      return false;
    }

    private function hasAddPermission()
    {
        return (auth()->user()->can('user_add') && $this->isOrganizationAdmins(session('organization_id'))) ||
              (auth()->user()->hasAnyRole(['level_2', 'level_3']) && auth()->user()->can('user_add'));
    }

    private function validateRequest($request, $action)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];

        $messages = [
            'name.required' => trans('validation.required'),
            'name.max' => trans('validation.max.string'),
            'email.required' => trans('validation.required'),
            'email.email' => trans('validation.email'),
            'email.max' => trans('validation.max.string'),
            'email.unique' => trans('validation.unique'),
            'password.required' => trans('validation.required'),
            'password.min' => trans('validation.min.string'),
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function responseWithValidationErrors($validator)
    {
        return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => []]);
    }

    private function createUser($request)
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    }

    private function generateSetPasswordLink($user)
    {
        return config('app.project_alias') === 'sFlow'
            ? URL::signedRoute('set-password.create', ['user' => $user->id])
            : URL::temporarySignedRoute('set-password.create', now()->addHours(24), ['user' => $user->id]);
    }

    private function getEmailDetails($orgId, $userName)
    {
        $from = config('mail.from.address');
        $name = config('mail.from.name');

        if ($orgId) {
            $org = Organization::find($orgId);
            if ($org) {
                $from = $org->email;
                $name = $org->name;
            }
        }

        return ['from' => $from, 'name' => $name, 'userName' => $userName];
    }

    private function sendSetPasswordEmail($user, $setPasswordLink, $emailDetails)
    {
        Mail::to($user->email)->send(new SetPasswordEmail($setPasswordLink, $emailDetails['userName'], $emailDetails['name'], session('organization_id')), function ($message) use ($user, $emailDetails) {
            $message->from($emailDetails['from'], $emailDetails['name']);
        });
    }

    private function assignUserToOrganization($user, $orgId)
    {
        if ($user && !auth()->user()->can('permission_list')) {
            UserOrganization::create([
                'user_id' => $user->id,
                'organization_id' => $orgId,
                'user_type' => 'users',
                'access_type' => 2,
            ]);
        }
    }

    private function responseWithException($e)
    {
        return response()->json(['status' => false, 'other' => true, 'message' => $e->getMessage(), 'data' => []]);
    }
}