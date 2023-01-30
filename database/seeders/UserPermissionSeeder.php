<?php
namespace Sbash\Usermgmt\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $perms = [
            ['name' => 'user_list', 'guard_name' => 'web'],
            ['name' => 'user_add', 'guard_name' => 'web'],
            ['name' => 'user_edit', 'guard_name' => 'web'],
            ['name' => 'user_delete', 'guard_name' => 'web'],
            ['name' => 'user_detail', 'guard_name' => 'web'],
            ['name' => 'user_list', 'guard_name' => 'web'],
        ];

        foreach($perms as $key => $per)
        {
            DB::table('permissions')->insert($per);
        }

        $rows = [
          'level_1' => [ //1
            'user_list', 
            'user_detail'
          ],
          'level_2' => [ 
            'user_list',
            'user_add',
            'user_edit',
            'user_delete',
            'user_detail'            
          ],
          'level_3' => [            
            'user_list',
            'user_add',
            'user_edit',
            'user_delete',
            'user_detail'            
          ],
          'User' => [            
            'user_list',
            'user_add',
            'user_edit',
            'user_delete',
            'user_detail'
          ],          
        ];

        foreach ($rows as $role_name => $permissions) {
          $role = Role::findByName($role_name);
          foreach ($permissions as $id => $permission) {
            $role->givePermissionTo($permission);
          }
        }        

    }
}
