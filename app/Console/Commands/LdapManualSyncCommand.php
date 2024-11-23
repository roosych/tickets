<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LdapManualSyncCommand extends Command
{
    protected $signature = 'ldap:manual-sync';

    protected $description = 'Manual users sync from LDAP';

    public function handle()
    {
        ini_set('memory_limit', '512M'); // Увеличение лимита памяти
        set_time_limit(300); // Увеличение времени выполнения

        $this->info('Sync users from LDAP...');
        $this->input->setInteractive(false);  // Отключаем интерактивный режим

        $this->call('ldap:import', [
            'provider' => 'users',
            '--filter' => '(department=*)',
            '--attributes' => 'cn,samaccountname,mail,title,department,distinguishedname,extensionattribute11,manager,mailnickname,mobile,pager,jpegphoto',
            '--no-interaction' => true,
            //'--force' => true,
        ]);
        $this->info('Sync users from LDAP completed!');
    }
}
