<?php

namespace App\Models;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'value',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'type' => PromotionType::class,
        'status' => PromotionStatus::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'value' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', PromotionStatus::ACTIVE)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function calculateDiscount($originalPrice)
    {
        if ($this->type === PromotionType::PERCENTAGE) {
            return $originalPrice * ($this->value / 100);
        }

        return $this->value;
    }
}
