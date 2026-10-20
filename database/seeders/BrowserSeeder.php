<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class BrowserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('e2e') || ! getenv('E2E_ADMIN_PASSWORD')) {
            throw new \RuntimeException('Seeder khusus browser test dengan password sementara.');
        }
        $user = new User;
        $user->name = 'Admin pengujian';
        $user->email = 'admin@example.test';
        $user->password = getenv('E2E_ADMIN_PASSWORD');
        $user->is_admin = true;
        $user->save();
    }
}
