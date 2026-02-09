<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, false, false)) {
            $user = Auth::user();
            
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->is_approved) {
                return redirect()->route('quizzer.dashboard');
            } else {
                Auth::logout();
                return back()->withErrors(['email' => 'Account pending approval.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'quizzer',
            'is_approved' => false,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful. Awaiting admin approval.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}