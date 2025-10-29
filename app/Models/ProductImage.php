<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['product_id', 'url', 'description', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        $path = trim((string) ($this->attributes['url'] ?? ''));

        if ($path === '') {
            return asset('images/product_1.png');
        }

        $normalized = str_replace('\\', '/', $path);

        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $localHosts = array_filter([$appHost, 'localhost', '127.0.0.1']);

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            $parsedUrl = parse_url($normalized);
            $host = $parsedUrl['host'] ?? null;

            $isLocalHost = $host === null || in_array($host, $localHosts, true);

            if ($isLocalHost) {
                $normalized = ltrim($parsedUrl['path'] ?? '', '/');
            } else {
                return $normalized;
            }
        }

        $normalized = ltrim($normalized, '/');

        $diskCandidates = [$normalized];

        if (Str::startsWith($normalized, 'storage/')) {
            $diskCandidates[] = Str::after($normalized, 'storage/');
        }

        if (Str::startsWith($normalized, 'public/')) {
            $diskCandidates[] = Str::after($normalized, 'public/');
        }

        $diskCandidates = array_unique(array_filter(array_map(static fn ($candidate) => ltrim((string) $candidate, '/'), $diskCandidates)));

        foreach ($diskCandidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (Storage::disk('public')->exists($candidate)) {
                return '/' . ltrim('storage/' . $candidate, '/');
            }
        }

        $publicCandidates = [
            $normalized,
            'storage/' . ltrim($normalized, '/'),
        ];

        if (Str::startsWith($normalized, 'storage/')) {
            $publicCandidates[] = Str::after($normalized, 'storage/');
        }

        if (!Str::startsWith($normalized, 'images/')) {
            $publicCandidates[] = 'images/' . ltrim($normalized, '/');
        }

        $publicCandidates = array_unique(array_filter(array_map(static fn ($candidate) => ltrim((string) $candidate, '/'), $publicCandidates)));

        foreach ($publicCandidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (file_exists(public_path($candidate))) {
                return '/' . ltrim($candidate, '/');
            }
        }

        return asset('images/product_1.png');
    }
}
