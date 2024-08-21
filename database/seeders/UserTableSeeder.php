<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create(array(
            'name'          => 'Admin',
            'email'         => 'admin@byslglobal.com',
            'phone'         => '012345678925',
            'fingerprint_no'=> '123',
            'status'        => User::STATUS_ACTIVE,
            'is_supervisor' => User::STATUS_ACTIVE,
            'password'      => bcrypt(12345678),
        ));

        $user->assignRole(User::ROLE_ADMIN);
    }
}
