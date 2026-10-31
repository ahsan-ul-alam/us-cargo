<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ShippingRate;
use App\Models\SiteSetting;
use App\Support\VideoEmbed;

class HomeController extends Controller
{
    public function index()
    {
        $video = SiteSetting::get('hero_video_enabled', 1)
            ? VideoEmbed::parse(SiteSetting::get('hero_video_url'), true, true, true)
            : null;

        return view('home', [
            'heroVideo' => $video,
            'reviews'   => Review::where('is_approved', true)->orderBy('sort_order')->latest()->get(),
        ]);
    }
    public function services(){ return view('services'); }

    public function pricing()
    {
        $rates = ShippingRate::where('is_active', true)->get()->groupBy('direction');

        return view('pricing', ['rates' => $rates]);
    }

    public function about()   { return view('about'); }
    public function faq()     { return view('faq'); }
}
