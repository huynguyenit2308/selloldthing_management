<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'category_id',
        'price',
        'original_price',
        'description',
        'short_description',
        'condition',
        'location',
        'seller_name',
        'contact_phone',
        'contact_email',
        'contact_method',
        'sku',
        'origin',
        'warranty',
        'attachments',
        'additional_info',
        'status',
        'view_count',
        'quantity',
        'is_featured',
        'is_approved',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'featured_until',
        'expires_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'featured_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const OWNER_SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'name_asc' => ['name', 'asc'],
        'name_desc' => ['name', 'desc'],
        'price_asc' => ['price', 'asc'],
        'price_desc' => ['price', 'desc'],
        'views_asc' => ['view_count', 'asc'],
        'views_desc' => ['view_count', 'desc'],
    ];

    public const INVENTORY_SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'price_desc' => ['price', 'desc'],
        'price_asc' => ['price', 'asc'],
        'name_asc' => ['name', 'asc'],
        'name_desc' => ['name', 'desc'],
    ];

    public const FAVORITE_SORTS = [
        'newest' => ['favorites.created_at', 'desc'],
        'oldest' => ['favorites.created_at', 'asc'],
        'name_asc' => ['products.name', 'asc'],
        'name_desc' => ['products.name', 'desc'],
        'price_desc' => ['products.price', 'desc'],
        'price_asc' => ['products.price', 'asc'],
        'views_desc' => ['products.view_count', 'desc'],
        'views_asc' => ['products.view_count', 'asc'],
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('is_approved', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where(function($q) {
                $q->whereNull('featured_until')
                  ->orWhere('featured_until', '>', now());
            });
    }

    public function scopeWithOrderedImages(Builder $query): Builder
    {
        return $query->with(['images' => function ($imageQuery) {
            if (Schema::hasColumn('product_images', 'sort_order')) {
                $imageQuery->orderBy('sort_order');
            }

            $imageQuery->orderBy('created_at');
        }]);
    }

    public static function publicListingBase(): Builder
    {
        return self::publishedQuery()->withOrderedImages()->with('category');
    }

    public static function publicListing(array $filters = []): Builder
    {
        return self::applyListingFilters(self::publicListingBase(), $filters);
    }

    public static function publishedQuery(): Builder
    {
        return self::query()->published();
    }

    public static function availableConditions(): Collection
    {
        if (!Schema::hasColumn('products', 'condition')) {
            return collect();
        }

        return self::publishedQuery()
            ->whereNotNull('condition')
            ->distinct()
            ->orderBy('condition')
            ->pluck('condition');
    }

    public static function availableLocations(): Collection
    {
        if (!Schema::hasColumn('products', 'location')) {
            return collect();
        }

        return self::publishedQuery()
            ->whereNotNull('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');
    }

    public static function publishedPriceBounds(): ?Model
    {
        return self::publishedQuery()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
    }

    public static function ownerListing(int $userId, array $filters = [], array $sortMappings = self::OWNER_SORTS): Builder
    {
        $query = self::query()
            ->where('user_id', $userId)
            ->withOrderedImages()
            ->with('category');

        $status = $filters['status'] ?? null;
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $searchTerm = trim((string) ($filters['search'] ?? ''));
        if ($searchTerm !== '') {
            $query->where(function (Builder $builder) use ($searchTerm) {
                $like = '%' . $searchTerm . '%';
                $builder->where('name', 'like', $like);

                if (Schema::hasColumn('products', 'description')) {
                    $builder->orWhere('description', 'like', $like);
                }
            });
        }

        [$column, $direction] = self::resolveSortOption($filters['sort'] ?? null, $sortMappings);
        $query->orderBy($column, $direction);

        return $query;
    }

    public static function searchQuery(string $keyword): Builder
    {
        return self::publicListingBase()
            ->where(function (Builder $builder) use ($keyword) {
                $like = '%' . $keyword . '%';
                $builder->where('name', 'like', $like);

                if (Schema::hasColumn('products', 'description')) {
                    $builder->orWhere('description', 'like', $like);
                }
            });
    }

    public static function applySearchSort(Builder $query, string $sort, string $keyword): Builder
    {
        $sortMappings = [
            'relevance' => function (Builder $builder) use ($keyword) {
                $builder->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$keyword . '%'])
                    ->orderByDesc('view_count')
                    ->orderByDesc('created_at');
            },
            'price_asc' => fn (Builder $builder) => $builder->orderBy('price', 'asc'),
            'price_desc' => fn (Builder $builder) => $builder->orderBy('price', 'desc'),
            'newest' => fn (Builder $builder) => $builder->orderByDesc('created_at'),
        ];

        $handler = $sortMappings[$sort] ?? $sortMappings['relevance'];

        return $handler($query) ?? $query;
    }

    public static function suggestionQuery(string $keyword): Builder
    {
        return self::publishedQuery()
            ->select('name')
            ->where(function (Builder $builder) use ($keyword) {
                $builder->where('name', 'like', '%' . $keyword . '%');
            })
            ->orderByDesc('view_count')
            ->limit(6);
    }

    public static function adminListing(array $filters = []): Builder
    {
        $query = self::query()
            ->with(['category', 'user', 'images', 'approver'])
            ->select([
                'id',
                'name',
                'category_id',
                'user_id',
                'price',
                'original_price',
                'quantity',
                'status',
                'view_count',
                'is_featured',
                'is_approved',
                'approved_at',
                'approved_by',
                'featured_until',
                'expires_at',
                'created_at',
            ]);

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $status = $filters['status'] ?? null;
        $statusMap = [
            'pending' => 'pending',
            'published' => 'published',
            'hidden' => 'hidden',
            'sold' => 'sold',
        ];

        if ($status && array_key_exists($status, $statusMap)) {
            $query->where('status', $statusMap[$status]);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $approvalStatus = $filters['approval_status'] ?? null;
        if ($approvalStatus === 'pending') {
            $query->where('is_approved', false)->where('status', 'pending');
        } elseif ($approvalStatus === 'approved') {
            $query->where('is_approved', true);
        } elseif ($approvalStatus === 'featured') {
            $query->featured();
        }

        return $query->orderByDesc('created_at');
    }

    public static function applySort(Builder $query, ?string $option, array $mappings, ?array $fallback = null): Builder
    {
        [$column, $direction] = self::resolveSortOption($option, $mappings, $fallback);

        return $query->orderBy($column, $direction);
    }

    public static function resolveSortOption(?string $option, array $mappings, ?array $fallback = null): array
    {
        if ($option && isset($mappings[$option])) {
            return $mappings[$option];
        }

        if ($fallback) {
            return $fallback;
        }

        $first = reset($mappings);

        return is_array($first) ? $first : ['created_at', 'desc'];
    }

    protected static function applyListingFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['category'])) {
            $query->where('category_id', (int) $filters['category']);
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $query->where('price', '>=', (float) $filters['price_min']);
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $query->where('price', '<=', (float) $filters['price_max']);
        }

        if (isset($filters['condition']) && Schema::hasColumn('products', 'condition')) {
            $query->where('condition', $filters['condition']);
        }

        if (isset($filters['location']) && Schema::hasColumn('products', 'location')) {
            $query->where('location', $filters['location']);
        }

        return $query;
    }
}
