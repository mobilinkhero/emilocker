<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Customer login (used by Android app and customer portal)
    public function customerLogin(Request $request)
    {
        $request->validate(['phone' => 'required', 'password' => 'required']);

        $customer = Customer::where('phone', $request->phone)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages(['phone' => 'Invalid credentials.']);
        }

        $token = $customer->createToken('customer-token', ['role:customer'])->plainTextToken;

        return response()->json(['token' => $token, 'customer' => $customer]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
}
