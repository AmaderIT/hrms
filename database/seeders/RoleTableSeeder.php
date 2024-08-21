<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->seedRoleData();
    }

    /**
     * Seed role data to the database
     */
    protected function seedRoleData()
    {
        Role::create(array(
            'name'              => 'Admin',
            'guard_name'        => 'web',
        ));
    }
}
