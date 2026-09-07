<?php

namespace Database\Seeders;

use App\Models\MaterialType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds the broad construction-material categories that don't already have
 * a built-in card on the Materials grid (Wood & Carpentry, Doors & Windows,
 * Hardware, Waterproofing & Insulation, Roofing, Finishing Materials,
 * External/Outdoor). Item-level options for these (and for the existing
 * cards) live in the `materialCategoryConfig` JS object in
 * resources/views/admin/menus/material/add_order.blade.php and
 * add_request.blade.php.
 *
 * Each new type needs an image (required by MaterialTypeController::store's
 * validation for admin-added types), so this generates a simple labelled
 * placeholder icon — replace it any time from the Materials grid by
 * deleting the card and re-adding it with a real image.
 */
class ConstructionMaterialTypesSeeder extends Seeder
{
    /**
     * name => [background colour hex, emoji-ish short label for the icon]
     */
    protected array $types = [
        'Wood & Carpentry'           => '8B5A2B',
        'Doors & Windows'            => '4A6FA5',
        'Hardware'                   => '6B7280',
        'Waterproofing & Insulation' => '1E7A5F',
        'Roofing'                    => 'B23A2F',
        'Finishing Materials'        => 'A0522D',
        'External/Outdoor'           => '2F7D32',
    ];

    public function run(): void
    {
        Storage::disk('public')->makeDirectory('material_types');

        foreach ($this->types as $name => $hexColor) {
            $slug = Str::slug($name, '');

            if (MaterialType::where('slug', $slug)->exists()) {
                continue;
            }

            $relativePath = 'material_types/' . $slug . '.png';
            $fullPath = Storage::disk('public')->path($relativePath);

            $this->makePlaceholderIcon($fullPath, $name, $hexColor);

            MaterialType::create([
                'name' => $name,
                'slug' => $slug,
                'image' => $relativePath,
            ]);
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
