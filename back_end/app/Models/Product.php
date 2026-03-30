<?php

namespace App\Models;

use App\Enums\PromotionType;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'price',
        'image',
        'quantity',
        'description',
        'category_id',
    ];

    protected $appends = ['final_price', 'active_promotion'];

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function getActivePromotionAttribute()
    {
        return $this->promotions()->active()->get()->sortByDesc(function ($promotion) {
            return $promotion->calculateDiscount($this->price);
        })->first();
    }

    public function getFinalPriceAttribute()
    {
        $promotion = $this->active_promotion;
        
        if (!$promotion) {
            return $this->price;
        }

        $discount = $promotion->calculateDiscount($this->price);
        
        return max(0, $this->price - $discount);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

}
