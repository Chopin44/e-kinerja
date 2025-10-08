// database/seeders/RoleSeed.php
<?php



use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeed extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'staf']);

        // Assign ke user admin utama (ganti email sesuai punyamu)
        $user = User::firstWhere('email', 'admin@example.com');
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
