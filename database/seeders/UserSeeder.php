<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $bidangs = Bidang::all();
        
        // Admin
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@kominfo.go.id',
            'nip' => '198001012005011001',
            'phone' => '081234567890',
            'bidang_id' => $bidangs->first()->id,
            'role' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        
        // Staff per bidang
        $staffData = [
            ['Ahmad Pratama', 'ahmad.p@kominfo.go.id', '198502152010011002', 'INFO'],
            ['Siti Nurjannah', 'siti.n@kominfo.go.id', '198703202012012001', 'SEKT'],
            ['Rudi Hermawan', 'rudi.h@kominfo.go.id', '198904252015011001', 'KOMM'],
            ['Lisa Andriani', 'lisa.a@kominfo.go.id', '199001102018012001', 'STAT'],
        ];
        
        foreach ($staffData as $staff) {
            $bidang = $bidangs->where('kode', $staff[3])->first();
            
            User::create([
                'name' => $staff[0],
                'email' => $staff[1],
                'nip' => $staff[2],
                'phone' => '081234567' . rand(100, 999),
                'bidang_id' => $bidang->id,
                'role' => 'staf',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
        }
    }
}