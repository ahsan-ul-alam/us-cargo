<?php

namespace App\Support;

class VideoEmbed
{
    /** @return array{provider:string,src:string}|null */
    public static function parse(?string $url, bool $autoplay = true, bool $muted = true, bool $loop = false, bool $controls = false): ?array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]{11})~i', $url, $m)) {
            $q = http_build_query([
                'enablejsapi' => 1, 'controls' => (int) $controls, 'modestbranding' => 1, 'rel' => 0, 'playsinline' => 1,
                'disablekb' => (int) ! $controls, 'iv_load_policy' => 3, 'fs' => (int) $controls,
                'autoplay' => (int) $autoplay, 'mute' => (int) $muted,
                'loop' => (int) $loop, 'playlist' => $loop ? $m[1] : null,
            ]);
            return ['provider' => 'youtube', 'id' => $m[1], 'src' => "https://www.youtube-nocookie.com/embed/{$m[1]}?{$q}"];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
            $q = http_build_query([
                'controls' => (int) $controls, 'title' => 0, 'byline' => 0, 'portrait' => 0, 'playsinline' => 1,
                'autoplay' => (int) $autoplay, 'muted' => (int) $muted, 'loop' => (int) $loop,
            ]);
            return ['provider' => 'vimeo', 'id' => $m[1], 'src' => "https://player.vimeo.com/video/{$m[1]}?{$q}"];
        }

        // Google Drive share links (the file must be shared as "Anyone with the link")
        if (preg_match('~(?:drive|docs)\.google\.com/(?:file/d/|open\?(?:.*&)?id=|uc\?(?:.*&)?id=)([\w-]{10,})~i', $url, $m)) {
            return ['provider' => 'drive', 'id' => $m[1], 'src' => "https://drive.google.com/file/d/{$m[1]}/preview"];
        }

        if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url)) {
            return ['provider' => 'file', 'src' => $url];
        }

        return null;
    }
}
