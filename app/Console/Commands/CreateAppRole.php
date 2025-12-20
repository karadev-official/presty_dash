<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateAppRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:create-app-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = ['super-admin', 'admin', 'pro', 'customer'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        $this->info('App roles created successfully.');
    }
}
