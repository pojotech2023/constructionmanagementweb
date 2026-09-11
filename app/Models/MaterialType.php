<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'created_by',
        'updated_by',
    ];

    /**
     * Map material slugs directly to user images in public/images/sri
     */
    protected static array $sriImageMap = [
        'woodcarpentry'           => 'woodcarpenter.avif',
        'doorswindows'            => 'doors.jpg',
        'hardware'                => 'Hardware.avif',
        'waterproofinginsulation' => 'waterproofing.webp',
        'roofing'                 => 'roofing.avif',
        'finishingmaterials'      => 'finishing.jfif',
        'externaloutdoor'         => 'outdoor.jfif',
        'scaffoldingformwork'     => 'scaffolding.jfif',
        'safetyppe'               => 'safety.jfif',
        'sanitarybathfittings'    => 'bath_fitting.jfif',
        'glassaluminium'          => 'glass.jpg',
    ];

    /**
     * Get image URL with automatic resolution to public/images/sri or storage
     */
    public function getImageUrlAttribute(): string
    {
        // 1. Direct match from mapped files in public/images/sri
        if (isset(self::$sriImageMap[$this->slug])) {
            $sriFile = self::$sriImageMap[$this->slug];
            if (file_exists(public_path('images/sri/' . $sriFile))) {
                return asset('images/sri/' . $sriFile);
            }
        }

        // 2. If image field is stored
        if (!empty($this->image)) {
            $filename = basename($this->image);
            if (file_exists(public_path('images/sri/' . $filename))) {
                return asset('images/sri/' . $filename);
            }

            if (file_exists(public_path('storage/' . $this->image))) {
                return asset('storage/' . $this->image);
            }

            return asset('storage/' . $this->image);
        }

        return asset('images/sri/material.jpg');
    }
}
