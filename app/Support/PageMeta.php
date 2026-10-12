<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class PageMeta
{
    /**
     * @param  'website'|'article'  $type
     * @return array{
     *     title: string,
     *     description: string,
     *     image: string|null,
     *     canonical: string,
     *     index: bool,
     *     type: 'website'|'article',
     *     siteName: string,
     *     card: 'summary'|'summary_large_image'
     * }
     */
    public static function make(
        ?string $title,
        ?string $description = null,
        ?string $image = null,
        bool $index = true,
        string $type = 'website',
        bool $suffix = true,
        ?string $canonical = null,
    ): array {
        $siteName = (string) SiteSetting::get('title_suffix');
        $pageTitle = trim((string) $title);
        $documentTitle = $pageTitle === ''
            ? $siteName
            : ($suffix ? $pageTitle.' - '.$siteName : $pageTitle);

        $resolvedType = $type === 'article' ? 'article' : 'website';

        if (! $index) {
            return [
                'title' => $documentTitle,
                'description' => '',
                'image' => null,
                'canonical' => '',
                'index' => false,
                'type' => $resolvedType,
                'siteName' => $siteName,
                'card' => 'summary',
            ];
        }

        $text = trim((string) $description);

        if ($text === '') {
            $text = (string) SiteSetting::get('meta_description');
        }

        $absoluteImage = self::absolute($image);

        return [
            'title' => $documentTitle,
            'description' => self::plain($text),
            'image' => $absoluteImage,
            'canonical' => $canonical ?: url()->current(),
            'index' => true,
            'type' => $resolvedType,
            'siteName' => $siteName,
            'card' => $absoluteImage === null ? 'summary' : 'summary_large_image',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $blocks
     */
    public static function descriptionFromBlocks(?array $blocks): string
    {
        foreach ($blocks ?? [] as $block) {
            $type = $block['type'] ?? '';
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if (in_array($type, ['text', 'text_image'], true) && filled($data['body'] ?? null)) {
                return (string) $data['body'];
            }
        }

        return '';
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $blocks
     */
    public static function imageFromBlocks(?array $blocks): ?string
    {
        foreach ($blocks ?? [] as $block) {
            $type = $block['type'] ?? '';

            if (! in_array($type, ['image', 'text_image'], true)) {
                continue;
            }

            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $path = $data['path'] ?? null;

            if (is_array($path)) {
                $path = $path[0] ?? null;
            }

            if (is_string($path) && $path !== '') {
                return $path;
            }
        }

        return null;
    }

    public static function plain(?string $value, int $limit = 160): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $value)));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);
        $space = mb_strrpos($cut, ' ');

        if ($space !== false && $space >= (int) ($limit * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " \t.,;:").'…';
    }

    private static function absolute(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            $image = trim((string) SiteSetting::get('share_image'));
        }

        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (! str_starts_with($image, '/')) {
            $image = Storage::disk('public')->url($image);

            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }
        }

        return url($image);
    }
}
