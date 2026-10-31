<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Admin-editable counters (value + label pairs) shown on the public site.
 * Stored as JSON in site_settings; defaults are used until an admin saves.
 */
class Counters
{
    public const GROUPS = [
        'hero' => 'Hero badges (home page, under the buttons)',
        'home' => 'Home page stats band',
        'about' => 'About page stats band',
    ];

    public const DEFAULTS = [
        'hero' => [
            ['value' => '£৬/কেজি', 'label' => 'এয়ার কার্গো রেট'],
            ['value' => '৫–৮ দিন', 'label' => 'দ্রুত ডেলিভারি'],
            ['value' => '৯০%', 'label' => 'সময়মতো পৌঁছানো'],
        ],
        'home' => [
            ['value' => '৩,০০০+', 'label' => 'সন্তুষ্ট গ্রাহক'],
            ['value' => '৫ লক্ষ+', 'label' => 'কেজি মালামাল পাঠানো হয়েছে'],
            ['value' => '৯০%', 'label' => 'অন টাইম সার্ভিস'],
            ['value' => '৫+', 'label' => 'বছরের অভিজ্ঞতা'],
        ],
        'about' => [
            ['value' => '১৫,০০০+', 'label' => 'সফল চালান'],
            ['value' => '৮,৫০০+', 'label' => 'সন্তুষ্ট গ্রাহক'],
            ['value' => '১২+', 'label' => 'বছরের অভিজ্ঞতা'],
            ['value' => '৬টি', 'label' => 'প্রধান শাখা'],
        ],
    ];

    public const PROMO_DEFAULTS = [
        'promo_enabled' => 1,
        'promo_tag' => 'বিশেষ অফার',
        'promo_value' => '£৬/কেজি',
        'promo_cta' => 'ফ্রি কোটেশন নিন →',
    ];

    public static function promo(): array
    {
        $out = [];
        foreach (self::PROMO_DEFAULTS as $k => $default) {
            $out[$k] = SiteSetting::get($k, $default);
        }
        $out['promo_enabled'] = (bool) $out['promo_enabled'];

        return $out;
    }

    public static function get(string $group): array
    {
        $raw = SiteSetting::get("counters_{$group}");
        $items = $raw ? json_decode($raw, true) : null;

        return is_array($items) ? $items : (self::DEFAULTS[$group] ?? []);
    }

    public static function save(string $group, array $items): void
    {
        $items = array_values(array_map(
            fn ($i) => ['value' => (string) ($i['value'] ?? ''), 'label' => (string) ($i['label'] ?? '')],
            $items
        ));

        SiteSetting::set("counters_{$group}", json_encode($items, JSON_UNESCAPED_UNICODE));
    }
}
