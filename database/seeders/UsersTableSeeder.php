<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'SuperAdmin User',
                'role_id' => 1, // SuperAdmin role
                'phone_number' => '7474121245',
                'email' => 'superadmin@yopmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('Gopal@123'), // Default Gopal@123
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Admin User',
                'role_id' => 2, // Admin role
                'phone_number' => '1234567890',
                'email' => 'admin@yopmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('Gopal@123'), // Default Gopal@123
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'User User',
                'role_id' => 3, // Regular user role
                'phone_number' => '0987654321',
                'email' => 'user@yopmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('Gopal@123'), // Default Gopal@123
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
