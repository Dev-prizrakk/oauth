<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request) {}
    public function login(Request $request) {}
    public function logout(Request $request) {}
    public function me(Request $request) {}
    public function passwordForgot(Request $request) {}
    public function passwordReset(Request $request) {}
    public function passwordTokenCheck(Request $request) {}
    public function emailVerifyLink($user) {}
    public function emailVerify(Request $request) {}
}
