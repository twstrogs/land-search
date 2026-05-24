<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'raw_content',
        'price_text',
        'price_value',
        'area_text',
        'area_value',
        'frontage_texts',
        'frontage_count',
        'depth_text',
        'address_text',
        'ward',
        'district',
        'city',
        'property_type',
        'facebook_url',
        'image_url',
        'images',
        'author_id',
        'author_name',
        'confidence',
        'published_at',
        'source_group',
        'hash',
        'raw_content_hash',
    ];

    protected $casts = [
        'frontage_texts' => 'array',
        'images' => 'array',
        'price_value' => 'decimal:2',
        'area_value' => 'decimal:2',
        'confidence' => 'decimal:4',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    public function aiExtractions(): HasMany
    {
        return $this->hasMany(AiExtraction::class);
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(SourceRecord::class);
    }

    public function latestExtraction(): ?AiExtraction
    {
        return $this->aiExtractions()->latest()->first();
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (!$keyword) {
            return $query;
        }
        
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'LIKE', "%{$keyword}%")
              ->orWhere('description', 'LIKE', "%{$keyword}%")
              ->orWhere('address_text', 'LIKE', "%{$keyword}%")
              ->orWhere('district', 'LIKE', "%{$keyword}%")
              ->orWhere('city', 'LIKE', "%{$keyword}%");
        });
    }

    public function scopeFilterByPrice($query, ?float $min, ?float $max)
    {
        if ($min !== null && $min > 0) {
            $query->where('price_value', '>=', $min);
        }
        if ($max !== null && $max > 0) {
            $query->where('price_value', '<=', $max);
        }
        return $query;
    }

    public function scopeFilterByArea($query, ?float $min, ?float $max)
    {
        if ($min !== null && $min > 0) {
            $query->where('area_value', '>=', $min);
        }
        if ($max !== null && $max > 0) {
            $query->where('area_value', '<=', $max);
        }
        return $query;
    }

    public function scopeFilterByPropertyType($query, ?string $type)
    {
        if ($type) {
            $query->where('property_type', $type);
        }
        return $query;
    }

    public function scopeFilterByLocation($query, ?string $city, ?string $district)
    {
        if ($city) {
            $query->where('city', 'LIKE', "%{$city}%");
        }
        if ($district) {
            $query->where('district', 'LIKE', "%{$district}%");
        }
        return $query;
    }

    public function getPhonesAttribute()
    {
        return $this->contacts->pluck('phone')->toArray();
    }

    public function getFeaturesListAttribute()
    {
        return $this->features->pluck('name')->toArray();
    }

    /**
     * Get features grouped by their category
     */
    public function getFeaturesByGroupAttribute(): array
    {
        $groups = [];
        foreach ($this->features as $feature) {
            // Features are stored with slug like "legal:co_so_do"
            $parts = explode(':', $feature->slug, 2);
            $group = $parts[0] ?? 'other';

            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][] = $feature->name;
        }
        return $groups;
    }

    /**
     * Get human-readable feature groups
     */
    public function getFeatureGroupsDisplayAttribute(): array
    {
        $groupNames = [
            'basic_info' => 'Thông tin cơ bản',
            'location' => 'Vị trí',
            'infrastructure' => 'Giao thông & Hạ tầng',
            'legal' => 'Pháp lý',
            'land' => 'Đặc điểm đất',
            'house' => 'Đặc điểm nhà',
            'investment' => 'Tiềm năng đầu tư',
            'environment' => 'Môi trường sống',
            'suitable_for' => 'Phù hợp cho',
            'status' => 'Trạng thái',
            'other' => 'Khác',
        ];

        $result = [];
        foreach ($this->featuresByGroup as $group => $features) {
            $result[] = [
                'name' => $groupNames[$group] ?? ucfirst($group),
                'features' => $features,
            ];
        }
        return $result;
    }

    public function getPriceFormattedAttribute(): ?string
    {
        if (!$this->price_value) {
            return $this->price_text;
        }
        
        if ($this->price_value >= 1_000_000_000) {
            return round($this->price_value / 1_000_000_000, 1) . ' tỷ';
        }
        
        return round($this->price_value / 1_000_000, 0) . ' triệu';
    }

    public function getAreaFormattedAttribute(): ?string
    {
        if (!$this->area_value) {
            return $this->area_text;
        }
        return $this->area_value . ' m²';
    }

    /**
     * Get image URL from local storage path
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // If already a full URL (legacy/migrated data), return as-is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Convert local storage path to URL
        return url('storage/' . ltrim($value, '/'));
    }

    /**
     * Get image URLs from local storage paths
     */
    public function getImagesAttribute($value): array
    {
        if (!$value) {
            return [];
        }

        $paths = is_array($value) ? $value : json_decode($value, true) ?? [];

        return array_map(function ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return url('storage/' . ltrim($path, '/'));
        }, $paths);
    }
}
