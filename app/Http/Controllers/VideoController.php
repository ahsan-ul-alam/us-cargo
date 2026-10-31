<?php

namespace App\Http\Controllers;

use App\Models\Video;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::where('is_active', true)->orderBy('sort_order')->latest()->get()
            ->map(fn (Video $v) => ['model' => $v, 'embed' => $v->embed()])
            ->filter(fn ($row) => $row['embed'] !== null)
            ->values();

        return view('videos', ['videos' => $videos]);
    }
}
