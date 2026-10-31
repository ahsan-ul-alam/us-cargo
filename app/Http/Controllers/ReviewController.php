<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return view('reviews', [
            'reviews' => Review::where('is_approved', true)->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:100',
            'rating' => 'required|integer|between:1,5',
            'body' => 'required|string|max:1000',
        ]);

        Review::create($data + ['is_approved' => false]);

        return redirect(route('reviews') . '#write-review')->with('review_sent', 'ধন্যবাদ! আপনার রিভিউ অনুমোদনের পর প্রকাশিত হবে।');
    }
}
