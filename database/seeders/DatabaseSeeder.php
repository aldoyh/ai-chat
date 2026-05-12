<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Aldoyh',
            'email' => 'aldoyh@dev.doy.tech',
            'password' => Hash::make('973@33334122'),
        ]);

        $this->call([
            ChatSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
