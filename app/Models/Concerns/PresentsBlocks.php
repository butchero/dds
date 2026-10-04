<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait PresentsBlocks
{
    public function blocksForPublic(): array
    {
        return collect($this->blocks ?? [])->map(function (array $block) {
            $data = $block['data'] ?? [];

            foreach (['path'] as $key) {
                if (filled($data[$key] ?? null)) {
                    $data['url'] = Storage::disk('public')->url($data[$key]);
                }
            }

            if (isset($data['images']) && is_array($data['images'])) {
                $data['urls'] = collect($data['images'])->map(function ($image) {
                    $path = is_array($image) ? ($image['path'] ?? $image) : $image;

                    return filled($path) ? Storage::disk('public')->url($path) : null;
                })->filter()->values()->all();
            }

            $block['data'] = $data;

            return $block;
        })->all();
    }
}
