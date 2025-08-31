<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'province_code',
        'province_name',
        'regency_code',
        'regency_name',
        'district_code',
        'district_name',
        'village_code',
        'village_name',
        'detailed_address',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the full address as a formatted string.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->village_name,
            $this->district_name,
            $this->regency_name,
            $this->province_name,
        ]);

        $address = implode(', ', $parts);
        
        if ($this->detailed_address) {
            $address = $this->detailed_address . ', ' . $address;
        }

        return $address;
    }

    /**
     * Check if location has coordinates.
     *
     * @return bool
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get coordinates as array.
     *
     * @return array|null
     */
    public function getCoordinatesAttribute(): ?array
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    /**
     * Get the respondents for this location.
     */
    public function respondents(): HasMany
    {
        return $this->hasMany(Respondent::class);
    }
}
