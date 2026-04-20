<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('demo.users') as $user) {
            User::factory()
                ->demo($user['name'], $user['email'])
                ->create();
        }
    }
}
