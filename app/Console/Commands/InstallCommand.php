<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'app:install';

    protected $description = 'Command description';


    public function handle()
    {
        $this->call(InstallLanguagesCommand::class);
        $this->call(SyncDepartmentsCommand::class);
        $this->call(CreateTicketPrioritiesCommand::class);
        $this->call(CreatePermissionsCommand::class);
        $this->call(AssignAllPermissionsCommand::class);
    }
}
