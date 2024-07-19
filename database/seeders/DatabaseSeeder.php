<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $password = Str::random();
        $email = 'superadmin@email.com';

        $admin = User::create([
            'name' => 'Super admin',
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->command->alert('nmrxiv-platform: Users table seed successfully');
        $this->command->line('You may log in to admin console using <info>'.$email.'</info> and password: <info>'.$password.'</info>');
        $this->command->line('');

        $this->call(ShieldSeeder::class);
        $this->call(SolventSeeder::class);
        $this->call(DeviceSeeder::class);

        $admin->assignRole('super_admin');
    }
}
