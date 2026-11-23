<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class LdapManualSyncCommand extends Command
{
    protected $signature = 'ldap:manual-sync {--dry-run : Только показать, кого отключит, ничего не меняя}';

    protected $description = 'Manual users sync from LDAP';

    // userAccountControl: флаг ACCOUNTDISABLE
    private const UAC_ACCOUNT_DISABLE = 0x2;

    public function handle(): int
    {
        ini_set('memory_limit', '512M'); // Увеличение лимита памяти
        set_time_limit(600); // Увеличение времени выполнения

        $this->input->setInteractive(false);  // Отключаем интерактивный режим

        if (! $this->option('dry-run')) {
            $this->info('Sync users from LDAP...');
            $this->call('ldap:import', [
                'provider' => 'users',
                '--filter' => '(department=*)',
                // useraccountcontrol нужен для поля status (см. sync_attributes в config/auth.php)
                '--attributes' => 'cn,samaccountname,mail,title,department,distinguishedname,extensionattribute11,manager,mailnickname,mobile,pager,jpegphoto,useraccountcontrol',
                '--no-interaction' => true,
            ]);
        }

        $this->deactivateMissingOrDisabled();

        $this->info('Sync users from LDAP completed!');

        return self::SUCCESS;
    }

    /**
     * Отключает в кабинете тех, кто отключён или удалён в AD.
     * Обратно не включает: «активный» может быть снят вручную в настройках.
     */
    private function deactivateMissingOrDisabled(): void
    {
        // Все учётки AD (без фильтра по department — уволенным его часто очищают)
        $enabled = [];
        $disabled = [];
        foreach (LdapUser::query()->select(['samaccountname', 'useraccountcontrol'])->paginate(1000) as $ldapUser) {
            $login = strtolower((string) $ldapUser->getFirstAttribute('samaccountname'));
            if ($login === '') {
                continue;
            }
            $uac = (int) $ldapUser->getFirstAttribute('useraccountcontrol');
            ($uac & self::UAC_ACCOUNT_DISABLE) ? $disabled[$login] = true : $enabled[$login] = true;
        }

        // Только пользователи, пришедшие из AD (у технических учёток вроде робота интеграции guid нет)
        $active = User::query()->where('active', true)->whereNotNull('guid')->get(['id', 'username', 'name']);

        // Защита: если AD ответил подозрительно мало, не отключаем людей массово
        if (count($enabled) < $active->count() * 0.5) {
            $message = 'ldap:manual-sync: AD returned only ' . count($enabled) . ' enabled accounts for '
                . $active->count() . ' active users — deactivation skipped';
            Log::warning($message);
            $this->warn($message);

            return;
        }

        $toDeactivate = $active->filter(function (User $user) use ($enabled) {
            return ! isset($enabled[strtolower((string) $user->username)]);
        });

        if ($toDeactivate->isEmpty()) {
            $this->info('Nobody to deactivate.');

            return;
        }

        $rows = $toDeactivate->map(fn (User $u) => [
            $u->username,
            $u->name,
            isset($disabled[strtolower((string) $u->username)]) ? 'отключён в AD' : 'нет в AD',
        ])->values()->all();
        $this->table(['Логин', 'Имя', 'Причина'], $rows);

        // Отделы, у которых менеджер уходит в неактивные — им нужно назначить нового
        $orphaned = Department::whereIn('manager_id', $toDeactivate->pluck('id'))->with('manager:id,username')->get();
        if ($orphaned->isNotEmpty()) {
            $list = $orphaned->map(fn (Department $d) => "{$d->name} ({$d->manager->username})")->implode('; ');
            Log::warning("ldap:manual-sync: departments left with an inactive manager: {$list}");
            $this->warn("Departments left with an inactive manager: {$list}");
        }

        if ($this->option('dry-run')) {
            $this->warn("Dry run: would deactivate {$toDeactivate->count()} user(s).");

            return;
        }

        User::whereIn('id', $toDeactivate->pluck('id'))->update(['active' => false]);

        $logins = $toDeactivate->pluck('username')->implode(', ');
        Log::info("ldap:manual-sync deactivated {$toDeactivate->count()} user(s): {$logins}");
        $this->info("Deactivated {$toDeactivate->count()} user(s).");
    }
}
