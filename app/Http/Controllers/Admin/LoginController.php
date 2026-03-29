<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    public function index(){
       
        return view('admin.login');
    }
    public function login(Request $request){
        $request->validate([
            'userId' => 'required',
            'password' => 'required',
        ]);
        
        // Attempt to find the user based on the provided user ID
        $user = User::where('sup_id', $request->userId)
                    ->orWhere('level_2', $request->userId)
                    ->orWhere('level_3', $request->userId)
                    ->orWhere('level_4', $request->userId)
                    ->orWhere('level_5', $request->userId)
                    ->first();

        if($user){
            // Authentication succeeded
            if($user->acc_status == '1'){
                // Set the authenticated user manually
                auth()->login($user);

                $userId = $request->userId;
                \session(['userId' => $userId]);
                return redirect()->route('admin.dashboard');
            } else {
                // Account is inactive
                return redirect()->route('admin.login_form')->with('error', 'Your account has been inactive. Please contact the admin.');
            }
        } else {
            // Authentication failed
            return redirect()->route('admin.login_form')->with('error', 'User ID Invalid');
        }
    }

}
