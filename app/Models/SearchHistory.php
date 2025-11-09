<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'keyword',
        'search_count',
        'last_searched_at',
    ];

    protected $casts = [
        'last_searched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function recordFromRequest(Request $request, string $keyword): self
    {
        $user = $request->user();
        $sessionId = $request->session()->getId();

        $attributes = [
            'keyword' => $keyword,
        ];

        if ($user) {
            $attributes['user_id'] = $user->id;
        } else {
            $attributes['session_id'] = $sessionId;
        }

        $history = static::firstOrNew($attributes);

        if ($history->exists) {
            $history->increment('search_count');
            $history->last_searched_at = Carbon::now();
            $history->save();
        } else {
            $history->fill([
                'search_count' => 1,
                'last_searched_at' => Carbon::now(),
            ]);
            $history->save();
        }

        return $history;
    }

    public static function recentForRequest(Request $request, int $limit = 10)
    {
        $query = static::query()->orderByDesc('last_searched_at');

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('session_id', $request->session()->getId());
        }

        return $query->limit($limit)->get();
    }

    public function belongsToRequest(Request $request): bool
    {
        if ($request->user()) {
            return $this->user_id === $request->user()->id;
        }

        return $this->session_id === $request->session()->getId();
    }

    public static function pruneForRequest(Request $request, int $limit = 20): void
    {
        $query = static::query()->orderByDesc('last_searched_at');

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('session_id', $request->session()->getId());
        }

        /** @var Collection<int, int> $ids */
        $ids = $query->pluck('id');

        if ($ids->count() <= $limit) {
            return;
        }

        $idsToDelete = $ids->slice($limit);

        if ($idsToDelete->isNotEmpty()) {
            static::whereIn('id', $idsToDelete)->delete();
        }
    }
}
