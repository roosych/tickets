<?php
// todo Не запускать команду отдельно. Отделы и сотрудники отделов регулируются вручную. 20.02.2025
namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDepartmentsCommand extends Command
{
    protected $signature = 'departments:sync';

    protected $description = 'Fill and sync departments with users';

    public function handle()
    {
        $this->info('Sync departments...');
        //$this->fillDepartments();
        //$this->attachDeptToUser();
        $this->info('Sync completed!');
    }

    // заполнение департаментов с перезаписыванием
    public function fillDepartments()
    {
        DB::transaction(function () {
            // Получаем всех пользователей, у которых is_manager совпадает с manager
            $users = User::where('is_manager', 'manager')->get();

            foreach ($users as $user) {
                if ($user->department_id) {
                    // Если у пользователя уже есть department_id, обновляем название департамента
                    Department::where('id', $user->department_id)
                        ->update(['name' => $user->department]);
                } else {
                    // Если у пользователя нет department_id, создаем новый департамент
                    $department = Department::firstOrCreate(['name' => $user->department]);
                    $user->update(['department_id' => $department->id]);
                }
            }
        });
    }




    // Присваивание департамента юзеру (по айди)
//    public function attachDeptToUser()
//    {
//        DB::transaction(function () {
//            // Выбираем всех пользователей, у которых manager === is_manager
//            $users = User::whereColumn('manager', 'is_manager')->get();
//
//            foreach ($users as $user) {
//                $department = Department::where('name', $user->department)->first();
//
//                if ($department) {
//                    $user->department_id = $department->id;
//                    $user->save();
//                }
//            }
//        });
//    }
}
