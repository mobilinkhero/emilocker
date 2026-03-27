<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    public function showLogin() { return view('customer.auth.login'); }

    public function login(Request $request)
    {
        $request->validate(['phone' => 'required', 'password' => 'required']);

        if (Auth::guard('customer')->attempt(['phone' => $request->phone, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors(['phone' => 'Invalid credentials.'])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
}
