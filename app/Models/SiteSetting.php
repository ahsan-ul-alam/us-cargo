<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /** All settings, loaded once per request (scoped binding, see AppServiceProvider). */
    protected static function values(): array
    {
        return app('site_settings');
    }

    public static function get(string $key, $default = null)
    {
        return static::values()[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        app()->forgetInstance('site_settings');
    }

    public const BRAND_PRIMARY = '#022450';
    public const BRAND_SECONDARY = '#fd9230';

    /** :root overrides for the admin-chosen brand colours (primary = navy, secondary = orange). */
    public static function brandCss(): string
    {
        $hex = fn (string $key, string $default) => preg_match('/^#[0-9a-f]{6}$/i', (string) static::get($key)) ? static::get($key) : $default;
        $p = $hex('brand_primary', self::BRAND_PRIMARY);
        $s = $hex('brand_secondary', self::BRAND_SECONDARY);

        $o = max(0, min(100, (int) static::get('hero_overlay', 85)));
        $css = '';

        if (strcasecmp($p, self::BRAND_PRIMARY) !== 0 || strcasecmp($s, self::BRAND_SECONDARY) !== 0) {
            $css .= "--navy:{$p};--navy-2:color-mix(in srgb,{$p} 85%,#fff);"
                . "--orange:{$s};--orange-dark:color-mix(in srgb,{$s} 90%,#000);--orange-soft:color-mix(in srgb,{$s} 10%,#fff);";
        }
        $hc = $hex('hero_overlay_color', '');
        if ($hc !== '') {
            $css .= "--hero-c:{$hc};";
        }
        if ($o !== 85) {
            $css .= '--hero-o:' . ($o / 100) . ';';
        }

        return $css === '' ? '' : ":root{{$css}}";
    }

    public static function siteName(): string
    {
        return static::get('site_name') ?: 'প্রাইম কার্গো';
    }

    /** Text after the page title in the browser tab. */
    public static function siteTitle(): string
    {
        return static::get('site_title') ?? 'যুক্তরাজ্য ⇄ বাংলাদেশ';
    }

    public static function siteDescription(): string
    {
        return static::get('site_description') ?: 'প্রাইম কার্গো — যুক্তরাজ্য ও বাংলাদেশের মধ্যে দ্রুত, নিরাপদ ও সাশ্রয়ী এয়ার কার্গো সার্ভিস।';
    }

    public static function legalName(): string
    {
        return static::get('site_legal_name') ?: 'প্রাইম কার্গো লিমিটেড';
    }

    public static function logoUrl(): string
    {
        $path = static::get('brand_logo');

        return $path ? asset('storage/' . $path) : asset('images/pirmecargo.png');
    }

    public static function faviconUrl(): string
    {
        $path = static::get('brand_favicon');

        return $path ? asset('storage/' . $path) : asset('images/favicon.png');
    }
}
