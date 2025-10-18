<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'url', 'description'];

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->attributes['url'] ?? '';

        if (!$path) {
            return asset('images/product_1.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relativePath = ltrim($path, '/');

        if (!str_starts_with($relativePath, 'images/')) {
            $relativePath = 'images/' . $relativePath;
        }

        $fullPath = public_path($relativePath);

        if (!file_exists($fullPath)) {
            $pngRelativePath = preg_replace('/\.[^.]+$/', '.png', $relativePath);

            if ($pngRelativePath && file_exists(public_path($pngRelativePath))) {
                $relativePath = $pngRelativePath;
            }
        }

        if (!file_exists(public_path($relativePath))) {
            return asset('images/product_1.png');
        }

        return asset($relativePath);
    }
}
