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
        public function showStudentRegister()
    {
        return view('auth.student-register');
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
        // Student login


    public function studentRegister(Request $request)
    {
        $data = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:students,email',
        'password' => 'required|confirmed|min:6',
        // …
        ]);

        $student = Student::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
        // …
        ]);

        Auth::login($student);
        return redirect()->route('documents.index');
    }

        public function studentLogin(Request $request)
{
    $credentials = $request->only('email', 'password');

    // Try to authenticate using the "student" guard:
    if (Auth::guard('student')->attempt($credentials)) {
        // Regenerate session to protect against fixation:
        $request->session()->regenerate();

        return redirect()->intended(route('documents.index'));
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
}
        public function logout()
        {
            // Properly log out the user
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            return redirect()->route('login.student');
        }

        
    }
