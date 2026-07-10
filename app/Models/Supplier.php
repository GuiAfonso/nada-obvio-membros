<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'categoria', 'categoria_color', 'cidade', 'whatsapp'])]
class Supplier extends Model
{
    use HasFactory;

    public function reviews(): HasMany
    {
        return $this->hasMany(SupplierReview::class);
    }

    /**
     * Destaques mais marcados pela comunidade, ordenados por frequência.
     *
     * @return array<int, array{key: string, label: string, color: string, count: int}>
     */
    public function topDestaques(int $limit = 5): array
    {
        $counts = [];

        foreach ($this->reviews as $review) {
            foreach ($review->destaques ?? [] as $key) {
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        arsort($counts);

        $catalog = config('suppliers.destaques');

        return collect($counts)
            ->take($limit)
            ->map(fn (int $count, string $key) => array_merge(
                $catalog[$key] ?? ['label' => $key, 'color' => 'default'],
                ['key' => $key, 'count' => $count],
            ))
            ->values()
            ->all();
    }
}
