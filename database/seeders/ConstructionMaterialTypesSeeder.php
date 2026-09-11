<?php

namespace Database\Seeders;

use App\Models\MaterialType;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds the broad construction-material categories that don't already have
 * a built-in card on the Materials grid (Wood & Carpentry, Doors & Windows,
 * Hardware, Waterproofing & Insulation, Roofing, Finishing Materials,
 * External/Outdoor, Scaffolding & Formwork, Safety & PPE, Sanitaryware, Glass & Aluminium).
 */
class ConstructionMaterialTypesSeeder extends Seeder
{
    /**
     * name => [background colour hex, source image in public/images/sri]
     */
    protected array $types = [
        'Wood & Carpentry'           => ['color' => '8B5A2B', 'src' => 'woodcarpenter.avif'],
        'Doors & Windows'            => ['color' => '4A6FA5', 'src' => 'doors.jpg'],
        'Hardware'                   => ['color' => '6B7280', 'src' => 'Hardware.avif'],
        'Waterproofing & Insulation' => ['color' => '1E7A5F', 'src' => 'waterproofing.webp'],
        'Roofing'                    => ['color' => 'B23A2F', 'src' => 'roofing.avif'],
        'Finishing Materials'        => ['color' => 'A0522D', 'src' => 'finishing.jfif'],
        'External/Outdoor'           => ['color' => '2F7D32', 'src' => 'outdoor.jfif'],
        'Scaffolding & Formwork'     => ['color' => 'D97706', 'src' => 'scaffolding.jfif'],
        'Safety & PPE'               => ['color' => 'DC2626', 'src' => 'safety.jfif'],
        'Sanitary & Bath Fittings'   => ['color' => '0284C7', 'src' => 'bath_fitting.jfif'],
        'Glass & Aluminium'          => ['color' => '0D9488', 'src' => 'glass.jpg'],
    ];

    public function run(): void
    {
        Storage::disk('public')->makeDirectory('material_types');

        foreach ($this->types as $name => $meta) {
            $slug = Str::slug($name, '');
            $sriPath = public_path('images/sri/' . $meta['src']);

            if (file_exists($sriPath)) {
                $ext = pathinfo($meta['src'], PATHINFO_EXTENSION);
                $relativePath = 'material_types/' . $slug . '.' . $ext;
                $destinationPath = Storage::disk('public')->path($relativePath);

                if (!file_exists($destinationPath)) {
                    copy($sriPath, $destinationPath);
                }
            } else {
                $relativePath = 'material_types/' . $slug . '.png';
                $fullPath = Storage::disk('public')->path($relativePath);
                $this->makePlaceholderIcon($fullPath, $name, $meta['color']);
            }

            MaterialType::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'image' => $relativePath]
            );
        }

        // Standard construction measurement units
        $standardUnits = [
            'Bags', 'Ton', 'Tons', 'Kg', 'CFT', 'Brass', 'Sqft', 'Rft', 'Meter',
            'M Cube', 'Nos', 'Pieces', 'Bundles', 'Boxes', 'Slabs', 'Sheets',
            'Roll', 'Coil', 'Pack', 'Litres', 'Ltr', 'Buckets', 'Tins', 'Load',
            'Unit', 'Units', 'Hours', 'Days', 'Trips', 'Cups', 'Sets', 'Pairs'
        ];

        foreach ($standardUnits as $unitName) {
            Unit::firstOrCreate(['name' => $unitName]);
        }
    }

    protected function makePlaceholderIcon(string $path, string $label, string $hexColor): void
    {
        $size = 300;
        $image = imagecreatetruecolor($size, $size);

        [$r, $g, $b] = sscanf($hexColor, "%02x%02x%02x");
        $bg = imagecolorallocate($image, $r, $g, $b);
        $white = imagecolorallocate($image, 255, 255, 255);

        imagefilledrectangle($image, 0, 0, $size, $size, $bg);

        // Wrap the label across a few lines of built-in GD font so it fits the icon.
        $words = explode(' ', $label);
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $candidate = trim($current . ' ' . $word);
            if (strlen($candidate) > 12 && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        $font = 5; // largest built-in GD font
        $lineHeight = imagefontheight($font) + 6;
        $totalHeight = count($lines) * $lineHeight;
        $y = (int) (($size - $totalHeight) / 2);

        foreach ($lines as $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            $x = (int) (($size - $textWidth) / 2);
            imagestring($image, $font, max($x, 4), $y, $line, $white);
            $y += $lineHeight;
        }

        imagepng($image, $path);
        imagedestroy($image);
    }
}
