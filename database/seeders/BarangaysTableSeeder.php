<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            'Abulan',
            'Addalam',
            'Arubub',
            'Bannawag',
            'Bantay',
            'Barangay I (Poblacion - Centro)',
            'Barangay II (Poblacion - Centro)',
            'Barangcuag',
            'Dalibubon',
            'Daligan',
            'Diarao',
            'Dibuluan',
            'Dicamay I',
            'Dicamay II',
            'Dipangit',
            'Disimpit',
            'Divinan',
            'Dumawing',
            'Fugu',
            'Lacab',
            'Linamanan',
            'Linomot',
            'Malannit',
            'Minuri',
            'Namnama',
            'Napaliong',
            'Palagao',
            'Papan Este',
            'Papan Weste',
            'Payac',
            'Pongpongan (sometimes spelled Pungpongan)',
            'San Antonio',
            'San Isidro',
            'San Jose',
            'San Roque',
            'San Sebastian',
            'San Vicente',
            'Santa Isabel',
            'Santo Domingo',
            'Tupax',
            'Usol',
            'Villa Bello',
        ];

        foreach ($barangays as $barangay) {
            DB::table('barangays')->insert([
                'name' => $barangay,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

