<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function updateCategory($validated, $imagePath)
    {
        return $this->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'status' => (int) $validated['status'],
        ]);
    }
}
