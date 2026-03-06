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

    public function showSetup()
    {
        return view('auth.setup');
    }

    public function processSetup(Request $request)
    {
        $request->validate([
            'secret_key'            => 'required|string',
            'new_email'             => 'required|email|max:255',
            'new_password'          => 'required|string|min:8|confirmed',
        ]);

        if (!hash_equals(config('app.setup_secret_key', ''), $request->secret_key)) {
            return back()->withErrors(['secret_key' => 'Invalid secret key.']);
        }

        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            return back()->withErrors(['secret_key' => 'No admin account found.']);
        }

        // Check if the new email belongs to a different (non-admin) user
        $existing = User::where('email', $request->new_email)->where('id', '!=', $admin->id)->first();
        if ($existing) {
            return back()->withErrors(['new_email' => 'That email is already in use by another account.']);
        }

        $admin->email    = $request->new_email;
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return redirect()->route('login')->with('success', 'Admin credentials updated. You can now log in.');
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
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/')->with('after_splash', 'login');
    }
}