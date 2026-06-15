<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertyImageSeeder extends Seeder
{
    public function run(): void
    {
        $storagePath = storage_path('app/public/properties');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // Color schemes per property type
        $palettes = [
            'villa'     => [['bg' => [46,125,50], 'sky' => [86,165,110], 'accent' => [165,214,167]],
                            ['bg' => [56,142,60], 'sky' => [96,182,100], 'accent' => [185,234,187]],
                            ['bg' => [67,160,71], 'sky' => [107,200,111], 'accent' => [205,254,207]]],
            'apartment' => [['bg' => [21,101,192], 'sky' => [61,141,232], 'accent' => [144,202,249]],
                            ['bg' => [25,118,210], 'sky' => [65,158,250], 'accent' => [164,222,255]],
                            ['bg' => [30,136,229], 'sky' => [70,176,255], 'accent' => [184,242,255]]],
            'office'    => [['bg' => [106,27,154], 'sky' => [146,67,194], 'accent' => [206,147,216]],
                            ['bg' => [123,31,162], 'sky' => [163,71,202], 'accent' => [226,167,236]]],
            'shop'      => [['bg' => [230,81,0], 'sky' => [255,121,40], 'accent' => [255,204,128]],
                            ['bg' => [239,108,0], 'sky' => [255,148,40], 'accent' => [255,224,148]]],
            'warehouse' => [['bg' => [78,52,46], 'sky' => [118,92,86], 'accent' => [188,170,164]],
                            ['bg' => [93,64,55], 'sky' => [133,104,95], 'accent' => [208,190,184]]],
            'land'      => [['bg' => [85,139,47], 'sky' => [125,179,87], 'accent' => [197,225,165]],
                            ['bg' => [104,159,56], 'sky' => [144,199,96], 'accent' => [217,245,185]]],
        ];

        $defaultPalette = $palettes['apartment'];

        $properties = Property::all();
        $this->command->info('Found ' . count($properties) . ' properties');

        // Clear old image records
        Image::query()->delete();
        $this->command->info('Cleared old image records');

        foreach ($properties as $i => $prop) {
            $type = $prop->property_type ?? 'apartment';
            $typePalettes = $palettes[$type] ?? $defaultPalette;

            for ($j = 0; $j < 2; $j++) {
                $filename = 'prop_' . $prop->id . '_' . $j . '.jpg';
                $filepath = $storagePath . '/' . $filename;

                $pal = $typePalettes[$j % count($typePalettes)];
                list($r, $g, $b) = $pal['bg'];
                list($sr, $sg, $sb) = $pal['sky'];
                list($ar, $ag, $ab) = $pal['accent'];

                $width = 800;
                $height = 600;
                $im = imagecreatetruecolor($width, $height);
                imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));

                // Sky
                imagefilledrectangle($im, 0, 0, $width, (int)($height * 0.35),
                    imagecolorallocate($im, $sr, $sg, $sb));

                // Sun/cloud accent circle
                $accentColor = imagecolorallocatealpha($im, $ar, $ag, $ab, 40);
                imagefilledellipse($im, (int)($width * 0.8), (int)($height * 0.15),
                    (int)($width * 0.15), (int)($width * 0.15), $accentColor);

                // Building body
                $bldgR = max(0, $r - 40); $bldgG = max(0, $g - 40); $bldgB = max(0, $b - 30);
                $bldgColor = imagecolorallocate($im, $bldgR, $bldgG, $bldgB);

                $bldgX1 = (int)($width * 0.08);
                $bldgX2 = (int)($width * 0.92);
                $bldgY1 = (int)($height * 0.28);
                $bldgY2 = (int)($height * 0.82);
                imagefilledrectangle($im, $bldgX1, $bldgY1, $bldgX2, $bldgY2, $bldgColor);

                // Roof accent line
                $roofColor = imagecolorallocate($im, min(255, $bldgR + 60), min(255, $bldgG + 60), min(255, $bldgB + 60));
                imagefilledrectangle($im, $bldgX1, $bldgY1, $bldgX2, $bldgY1 + 8, $roofColor);

                // Windows
                $winColor = imagecolorallocate($im, $ar, $ag, $ab);
                $winW = (int)(($bldgX2 - $bldgX1) * 0.12);
                $winH = (int)($winW * 1.3);

                for ($row = 0; $row < 3; $row++) {
                    for ($col = 0; $col < 4; $col++) {
                        $wx = (int)($bldgX1 + ($bldgX2 - $bldgX1) * 0.1 + ($bldgX2 - $bldgX1) * 0.22 * $col);
                        $wy = (int)($bldgY1 + 30 + ($winH + 15) * $row);

                        if ($wx + $winW > $bldgX2 - 10) continue;
                        if ($wy + $winH > $bldgY2 - 60) continue;

                        $winLight = imagecolorallocate($im, 255, 255, 210);
                        if (rand(0, 3) > 0) {
                            imagefilledrectangle($im, $wx + 2, $wy + 2, $wx + $winW - 2, $wy + $winH - 2, $winLight);
                        }
                        imagefilledrectangle($im, $wx, $wy, $wx + $winW, $wy + $winH, $winColor);
                        imagerectangle($im, $wx, $wy, $wx + $winW, $wy + $winH,
                            imagecolorallocate($im, 255, 255, 255));
                        // Window cross
                        $crossC = imagecolorallocate($im, 255, 255, 255);
                        imageline($im, (int)($wx + $winW/2), $wy, (int)($wx + $winW/2), $wy + $winH, $crossC);
                        imageline($im, $wx, (int)($wy + $winH/2), $wx + $winW, (int)($wy + $winH/2), $crossC);
                    }
                }

                // Door
                $doorColor = imagecolorallocate($im, max(0, $ar - 60), max(0, $ag - 60), max(0, $ab - 60));
                $doorW = 36; $doorH = 70;
                $doorX = (int)(($bldgX1 + $bldgX2) / 2 - $doorW/2);
                $doorY = $bldgY2 - $doorH;
                imagefilledrectangle($im, $doorX, $doorY, $doorX + $doorW, $doorY + $doorH, $doorColor);
                // Door knob
                imagefilledellipse($im, $doorX + $doorW - 8, (int)($doorY + $doorH/2), 5, 5,
                    imagecolorallocate($im, 255, 215, 0));

                // Ground area
                $gndR = max(0, $r - 15); $gndG = min(255, $g + 15); $gndB = max(0, $b - 15);
                if ($type === 'land') {
                    $gndR = 139; $gndG = 90; $gndB = 43; // earth tone
                }
                imagefilledrectangle($im, 0, $bldgY2, $width, $height,
                    imagecolorallocate($im, $gndR, $gndG, $gndB));

                // Trees on sides for villa/land
                if (in_array($type, ['villa', 'land'])) {
                    $treeColor = imagecolorallocate($im, 34, 139, 34);
                    $trunkColor = imagecolorallocate($im, 101, 67, 33);
                    for ($t = 0; $t < 2; $t++) {
                        $tx = $t === 0 ? (int)($width * 0.04) : (int)($width * 0.96);
                        $ty = $bldgY2 - 20;
                        imagefilledrectangle($im, $tx - 3, $ty, $tx + 3, $ty + 40, $trunkColor);
                        imagefilledellipse($im, $tx, $ty - 15, 40, 40, $treeColor);
                        imagefilledellipse($im, $tx - 8, $ty - 10, 25, 25, $treeColor);
                        imagefilledellipse($im, $tx + 8, $ty - 10, 25, 25, $treeColor);
                    }
                }

                // Save image
                imagejpeg($im, $filepath, 88);
                imagedestroy($im);

                Image::create([
                    'property_id' => $prop->id,
                    'image_path' => 'properties/' . $filename,
                    'added_at' => now(),
                ]);
                $this->command->info("  Created: properties/$filename → property #{$prop->id}");
            }

            // Set status to 'available' so it shows in the API
            if ($prop->status !== 'available') {
                $prop->update(['status' => 'available']);
                $this->command->info("  Updated property #{$prop->id} status → available");
            }
        }

        $this->command->info("\n✅ Done! Generated images for " . count($properties) . " properties.");
    }
}
