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

    public function showStudentRegister() {
        return view('auth.student-register');
    }

    /**
     * Student Registration
     */
    public function studentRegister(Request $request) {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:students,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $student = Student::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('student')->login($student);

        return redirect()->route('documents.index');
    }

    /**
     * Student Login
     */
    public function studentLogin(Request $request) {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('documents.index'));
        }

        return back()->withErrors(['email' => 'Invalid student credentials']);
    }

    /**
     * Admin Login
     */
    public function adminLogin(Request $request) {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.applications.list'));
        }

        return back()->withErrors(['email' => 'Invalid admin credentials']);
    }

    /**
     * Logout (both admin or student depending on who's logged in)
     */
    public function logout(Request $request) {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('student')->check()) {
            Auth::guard('student')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.student');
    }
}
