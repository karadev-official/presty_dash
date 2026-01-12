<?php

namespace App\Listeners;

use App\Events\ProUserCreationProcessed;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Permission\Events\RoleAttached;

class CreateDefaultResourceForPro
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RoleAttached $event): void
    {
        $user = $event->model;

        if (!$user instanceof User) return;
        if ($user->role->name !== 'pro') return;
        if ($user->defaultResource()->exists()) return;


        $user->resources()->create([
            'name' => $user->name ?? 'Moi',
            'type' => Resource::TYPE_SELF,
            'specialty' => $user->specialty,
            'is_default' => true,
            'is_active' => true,
        ]);
    }
}
