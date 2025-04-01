<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Admin;
use App\Models\Student;

class AuthController extends Controller
{
    // Show login forms
    public function showAdminLogin() {
        return view('auth.admin-login');
    }

    public function showStudentLogin() {
        return view('auth.student-login');
    }

    // Admin login
    public function adminLogin(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            Session::put('admin', $admin->id);
            return redirect('/approving-documents');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // Student login
    public function studentLogin(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $student = Student::where('email', $request->email)->first();
    
        if ($student && Hash::check($request->password, $student->password)) {
            Auth::login($student); // Ensure Laravel authentication is used
            

            return redirect(url('/dbconn'));
            
        }
        console.log('Login Successful');
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // Logout
    public function logout() {
        Session::flush();
        return redirect('/');
    }
}
