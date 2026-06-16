<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shade;
use App\Models\ShadeImage;

class MigrateShadeImages extends Command
{
    protected $signature = 'migrate:shade-images';
    protected $description = 'Migrate shade_img1 to shade_img4 to shade_images table';

    public function handle()
    {
        $shades = Shade::all();

        foreach ($shades as $shade) {
            $images = [
                $shade->shade_img1,
                $shade->shade_img2,
                $shade->shade_img3,
                $shade->shade_img4,
            ];

            foreach ($images as $image) {
                if ($image) {
                    ShadeImage::create([
                        'shade_id' => $shade->id,
                        'shade_img' => $image,
                    ]);
                }
            }
        }

        $this->info('Shade images migrated successfully.');
    }
}
