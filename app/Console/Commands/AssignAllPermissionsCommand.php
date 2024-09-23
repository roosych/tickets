<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;

class AssignAllPermissionsCommand extends Command
{
    // Если передается user_id, то присваиваем все пермишены данному юзеру,
    // если нет, то только всем кто менеджер
    protected $signature = 'permissions:assign {user_id?}';
    protected $description = 'Assign all permissions to user';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->assignPermissions();
        $this->info('All permissions assigned to all managers!');
    }

    public function assignPermissions()
    {
        $permissions = Permission::all();
        $userId = $this->argument('user_id');

        if ($userId) {
            // Если передан user_id, проверяем, есть ли пользователь
            $user = User::where('id', $userId)->first();
            if (!$user) {
                $this->error("User $userId not found!");
                return;
            }
            // Присваиваем все разрешения пользователю
            $user->permissions()->sync($permissions);
            $this->info("All permissions have been assigned to the user with ID $userId successfully.");
        } else {
            $users = User::where('is_manager', '=', 'manager')->get();
            if ($users->isEmpty()) {
                $this->info("No managers users found");
                return;
            }
            // Присваиваем разрешения каждому пользователю в цикле
            foreach ($users as $user) {
                $user->permissions()->sync($permissions);
                //$this->info("Permissions assigned to user with ID {$user->id}");
            }
        }
    }
}
