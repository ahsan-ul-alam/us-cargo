<?php

namespace App\Http\Controllers;

use App\Models\AgentApplication;
use Illuminate\Http\Request;

class AgentApplicationController extends Controller
{
    public function index()
    {
        return view('agent-apply');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'phone'        => 'required|string|max:30',
            'company_name' => 'required|string|max:200',
            'address'      => 'required|string|max:1000',
            'email'        => 'required|email|max:200',
        ]);

        AgentApplication::create($data + ['status' => AgentApplication::STATUS_PENDING]);

        return redirect()->route('agent.apply')
            ->with('success', 'ধন্যবাদ! আপনার এজেন্ট আবেদন জমা হয়েছে। যাচাইয়ের পর আমরা আপনার সাথে যোগাযোগ করবো।');
    }
}
