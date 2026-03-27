<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function home()    { return view('landing.home'); }
    public function features(){ return view('landing.features'); }
    public function pricing() { return view('landing.pricing'); }
    public function contact() { return view('landing.contact'); }

    public function submitContact(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email',
            'message' => 'required|string|max:2000',
        ]);

        // TODO: send mail / store inquiry
        return back()->with('success', 'Message sent. We will get back to you shortly.');
    }
}
