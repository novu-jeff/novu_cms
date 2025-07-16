<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlbumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
        $now = Carbon::now();

        // Sample image paths
        $imagePaths = [
            'assignments/PtiP81ZyJnK6opMO3GLmpWgT6CeSxxtQpHK8t9Pw.png',
            'members/MujM15UsNYEkZd0RWVzKYaLBmUvJ4Bdnyr763aWw.png',
        ];

        for ($i = 1; $i <= 3; $i++) {
            // Create album
            $albumId = DB::table('albums')->insertGetId([
                'name' => 'Album ' . $i,
                'isActive' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert 3 photos using the 2 given paths and a fallback
            for ($j = 0; $j < 3; $j++) {
                DB::table('album_photo')->insert([
                    'album_id' => $albumId,
                    'image_path' => $imagePaths[$j % count($imagePaths)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
