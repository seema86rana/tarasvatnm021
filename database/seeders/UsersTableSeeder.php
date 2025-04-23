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
        // Truncate the table
        DB::table('users')->truncate();

        // Seed the table with data
        DB::table('users')->insert([
            [
                'name' => 'SuperAdmin User',
                'role_id' => 0, // SuperAdmin role
                'phone_number' => '9724572753',
                'email' => 'superadmin@yopmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('Gopal@123'), // Default Gopal@123
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'User User',
                'role_id' => 2, // Regular user role
                'phone_number' => '9016201780',
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
