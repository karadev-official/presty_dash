<?php

namespace App\Listeners;

use App\Events\ProUserCreationProcessed;
use App\Models\LoyaltyProgram;
use App\Models\Resource;
use App\Models\User;
use Spatie\Permission\Events\RoleAttached;

class CreateDefaultResourceAndProProfileForPro
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
        if (!$user->hasRole('pro')) return;
        if ($user->defaultResource()->exists()) return;

        $user->resources()->create([
            'name' => $user->name ?? 'Moi',
            'type' => Resource::TYPE_SELF,
            'specialty' => '',
            'is_default' => true,
            'is_active' => true,
        ]);

        // création automatique du professionalProfile.
        $professionalProfile = $user->professionalProfile;
        if(!$professionalProfile){
            $professionalProfile = $user->professionalProfile()->create([
                "specialty" => null
            ]);
        }

        // et aussi du programme de fidélité.
        LoyaltyProgram::firstOrCreate(
            [
                'professional_profile_id' => $professionalProfile->id,
            ],
            [
                'name' => 'Carte Fidélité',
                'description' => 'Collectez des visites et bénéficiez de réductions',
                'min_appointment_amount' => 1000,
                'is_active' => false,
            ]
        );
    }
}
