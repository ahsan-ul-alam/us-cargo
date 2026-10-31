<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index()
    {
        return view('quote');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'phone'        => 'required|string|max:30',
            'email'        => 'nullable|email|max:200',
            'weight_kg'    => 'nullable|numeric|min:0',
            'direction'    => 'required|in:uk_to_bd,bd_to_uk',
            'service_type' => 'required|in:air_cargo,door_to_door,parcel',
            'description'  => 'nullable|string|max:1000',
        ]);

        QuoteRequest::create($data);

        return back()->with('success', 'আপনার কোটেশন অনুরোধ পাঠানো হয়েছে। আমরা শীঘ্রই যোগাযোগ করব।');
    }
}
