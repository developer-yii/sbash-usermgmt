<?php
namespace Sbash\Usermgmt\Controllers;

use Illuminate\Http\Request;
use Sbash\Usermgmt\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Carbon\Carbon;
use DB;

class SetPasswordController extends Controller
{
	public function create(Request $request)
    {
        // Check if the signed URL is valid and has not expired
        $user = DB::table('users')->find($request->user);
        if (! $user || ! $request->hasValidSignature()) {            
            return view('usermgmt::pages.link-expire');
        }

        // If the URL is valid, display the page for setting a new password
        return view('usermgmt::users.set_password', ['user' => $user]);
    }

    public function store(Request $request)
    {
        // Validate the form input
        $request->validate([
            'user_id' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        // Update the user's password in the database
        DB::table('users')
            ->where('id', $request->user_id)
            ->update([
                'password' => Hash::make($request->password),
                'email_verified_at' => Carbon::now(),
            ]);
        // Redirect the user to the login page
        return redirect()->route('login')->with(['flash_message_success' => trans('usermgmt')['notification']['password_changed']]);        
    }
}