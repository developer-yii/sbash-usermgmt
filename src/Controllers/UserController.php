<?php
namespace Sbash\Usermgmt\Controllers;

use Illuminate\Http\Request;
use Sbash\Usermgmt\Controllers\Controller;
use Sbash\Usermgmt\Inspire;

class UserController extends Controller
{
    public function index() {
        $user = 'Navnit';
        return view('usermgmt::users.index', compact('user'));
    }
}