<?php

namespace App\Models;

use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title', 'description', 'source', 'video_file', 'video_url', 'thumbnail', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /** @return array{provider:string,src:string,id?:string}|null */
    public function embed(): ?array
    {
        if ($this->source === 'upload' && $this->video_file) {
            return ['provider' => 'file', 'src' => asset('storage/' . $this->video_file)];
        }

        return VideoEmbed::parse($this->video_url, true, false, false, true);
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }

        $embed = $this->source === 'link' ? VideoEmbed::parse($this->video_url) : null;

        return ($embed['provider'] ?? null) === 'youtube'
            ? "https://img.youtube.com/vi/{$embed['id']}/hqdefault.jpg"
            : null;
    }
}
