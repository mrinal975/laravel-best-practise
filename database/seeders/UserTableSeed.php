<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\IdGenerate\IdGenerate;

class UserTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
                ['name'=> 'admin', 'email'=>'mrinalmallik1@gmail.com','password'=>Hash::make('123456'), 'status' => 1, 'role_id'=>1, 'created_at' => now() ]
            ];

        User::truncate()->insert($users);
    }
}
