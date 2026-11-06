<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\IntegrationTicketController;
use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Технический пользователь для внешних систем и его API-токен.
 * Не из AD: войти через веб не может, работает только по токену. Повторный запуск выпускает новый токен и отзывает старый.
 */
class IntegrationRobotCommand extends Command
{
    protected $signature = 'integration:robot
        {--name=Chuck Norris : Имя технического пользователя}
        {--email=chuck.norris@serviceradar.local : Почта (логин) технического пользователя}
        {--department=IT Department : Отдел пользователя}
        {--token=serviceradar : Название токена}
        {--tagline=Тикеты не закрываются. Они сдаются. : Подпись, которая выводится вместо почты}
        {--avatar=resources/images/integration/chuck-norris.jpg : Квадратное фото для аватара (ставится, если аватара ещё нет)}';

    protected $description = 'Создаёт технического пользователя для API и выпускает ему токен';

    public function handle(): int
    {
        $department = Department::query()->where('name', $this->option('department'))->first();

        if (! $department) {
            $this->error('Отдел «'.$this->option('department').'» не найден.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $this->option('email'))->first();

        if (! $user) {
            $login = Str::before($this->option('email'), '@');
            $user = new User;
            $user->forceFill([
                'name' => $this->option('name'),
                'email' => $this->option('email'),
                'username' => $login,
                'password' => Hash::make(Str::random(64)),
                'department_id' => $department->id,
                'visible' => false,
                'active' => true,
                'tg_notify' => false,
                'email_notify' => false,
                // В рабочей базе колонка обязательная, хотя её нет в миграциях проекта.
                ...(Schema::hasColumn('users', 'mailnickname') ? ['mailnickname' => $login] : []),
            ])->save();
        }

        if (Schema::hasColumn('users', 'tagline') && $user->tagline !== $this->option('tagline')) {
            $user->forceFill(['tagline' => $this->option('tagline')])->save();
        }

        $this->ensureAvatar($user);

        $user->tokens()->where('name', $this->option('token'))->delete();
        $token = $user->createToken($this->option('token'), [IntegrationTicketController::ABILITY])->plainTextToken;

        $this->info("Пользователь: {$user->name} (id {$user->id}, {$user->email})");
        $this->line('Токен (показывается один раз, сохраните в .env внешней системы):');
        $this->line($token);

        return self::SUCCESS;
    }

    /**
     * Аватар лежит там же, куда синхронизация с AD кладёт фото: images/users/{username}.jpg. Существующий не заменяем.
     */
    private function ensureAvatar(User $user): void
    {
        $source = base_path($this->option('avatar'));
        $target = 'images/users/'.$user->username.'.jpg';

        if ($user->username && is_file($source) && ! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }
    }
}
