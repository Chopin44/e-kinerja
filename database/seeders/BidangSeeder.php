<?php
// database/seeders/BidangSeeder.php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    public function run()
    {
        $bidangs = [
            [
                'nama' => 'Sekretariat',
                'kode' => 'SEKT',
                'deskripsi' => 'Bidang Administrasi dan Kesekretariatan',
                'kepala_bidang' => 'Dr. Ahmad Santoso',
                'icon' => 'fas fa-cogs',
                'is_active' => true,
            ],
            [
                'nama' => 'Bidang Informasi',
                'kode' => 'INFO',
                'deskripsi' => 'Bidang Teknologi Informasi dan Komunikasi',
                'kepala_bidang' => 'Ir. Siti Nurjannah',
                'icon' => 'fas fa-network-wired',
                'is_active' => true,
            ],
            [
                'nama' => 'Bidang Komunikasi',
                'kode' => 'KOMM',
                'deskripsi' => 'Bidang Komunikasi dan Media',
                'kepala_bidang' => 'Drs. Budi Hartono',
                'icon' => 'fas fa-broadcast-tower',
                'is_active' => true,
            ],
            [
                'nama' => 'Bidang Statistik',
                'kode' => 'STAT',
                'deskripsi' => 'Bidang Statistik dan Analisis Data',
                'kepala_bidang' => 'M.Si. Lisa Andriani',
                'icon' => 'fas fa-chart-line',
                'is_active' => true,
            ],
        ];

        foreach ($bidangs as $bidang) {
            Bidang::create($bidang);
        }
    }
}