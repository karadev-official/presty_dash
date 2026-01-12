<?php

namespace App\Console\Commands;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillProSelfResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources:backfill-pro-self {--dry-run : Affiche ce qui serait créé sans écrire en base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Crée la ressource 'self' manquante pour les users ayant le rôle 'pro'.";


    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $prosQuery = User::role('pro');

        $totalPros = $prosQuery->count();
        $this->info("Pros trouvés : {$totalPros}");
        $this->newLine();

        $stats = [
            'created_self' => 0,
            'converted_default_to_self' => 0,
            'fixed_default_flags' => 0,
            'fixed_multiple_self' => 0,
            'activated_self' => 0,
            'renamed_self' => 0,
            'ok' => 0,
        ];

        $prosQuery->chunkById(200, function ($pros) use ($dryRun, &$stats) {
            foreach ($pros as $pro) {
                DB::beginTransaction();

                try {
                    $resources = Resource::where('pro_user_id', $pro->id)
                        ->orderBy('id')
                        ->get();

                    $selfResources = $resources->where('type', Resource::TYPE_SELF);
                    $defaultResources = $resources->where('is_default', true);

                    // 1) Choisir la ressource "self" canonique (celle qu'on gardera)
                    $canonical = null;

                    if ($selfResources->count() > 0) {
                        // Priorité à une self déjà default, sinon la plus ancienne (id min)
                        $canonical = $selfResources->firstWhere('is_default', true) ?? $selfResources->first();
                    } else {
                        // Pas de self : si une ressource est marquée default, on la convertit en self
                        if ($defaultResources->count() > 0) {
                            $canonical = $defaultResources->first();

                            if (!$dryRun) {
                                $canonical->update([
                                    'type' => Resource::TYPE_SELF,
                                    'is_default' => true,
                                ]);
                            }
                            $stats['converted_default_to_self']++;
                        } else {
                            // Pas de self et pas de default : on crée une self
                            if ($dryRun) {
                                // Simule une ressource canonique
                                $canonical = new Resource(['id' => null]);
                            } else {
                                $canonical = Resource::create([
                                    'pro_user_id' => $pro->id,
                                    'name' => $pro->name ?? 'Moi',
                                    'specialty' => $pro->specialty,
                                    'type' => Resource::TYPE_SELF,
                                    'is_default' => true,
                                    'is_active' => true,
                                ]);
                            }
                            $stats['created_self']++;
                        }
                    }

                    // Si dry-run et on n'a pas d'objet persistant pour canonical (création simulée)
                    if ($dryRun && $canonical->id === null) {
                        DB::rollBack();
                        continue;
                    }

                    // Recharger canonical si besoin
                    if (!$dryRun) {
                        $canonical->refresh();
                    }

                    $didSomething = false;

                    // 2) Forcer canonical: type=self, is_default=true, is_active=true
                    $canonicalUpdates = [];

                    if ($canonical->type !== Resource::TYPE_SELF) {
                        $canonicalUpdates['type'] = Resource::TYPE_SELF;
                    }
                    if (!$canonical->is_default) {
                        $canonicalUpdates['is_default'] = true;
                    }
                    if (!$canonical->is_active) {
                        $canonicalUpdates['is_active'] = true;
                        $stats['activated_self']++;
                    }
                    // Optionnel : si name vide, on le remplit
                    if (!$canonical->name) {
                        $canonicalUpdates['name'] = $pro->name ?? 'Moi';
                        $stats['renamed_self']++;
                    }

                    if (!empty($canonicalUpdates)) {
                        $didSomething = true;
                        if (!$dryRun) {
                            $canonical->update($canonicalUpdates);
                        }
                    }

                    // 3) Corriger toutes les autres ressources :
                    // - aucune autre ressource ne doit être is_default=true
                    // - aucune autre ressource ne doit être type=self (on les déclasse en employee)
                    $others = Resource::where('pro_user_id', $pro->id)
                        ->where('id', '!=', $canonical->id)
                        ->get();

                    $otherDefaultCount = 0;
                    $otherSelfCount = 0;

                    foreach ($others as $r) {
                        $updates = [];

                        if ($r->is_default) {
                            $updates['is_default'] = false;
                            $otherDefaultCount++;
                        }

                        if ($r->type === Resource::TYPE_SELF) {
                            $updates['type'] = Resource::TYPE_EMPLOYEE; // déclassement
                            $otherSelfCount++;
                        }

                        if (!empty($updates)) {
                            $didSomething = true;
                            if (!$dryRun) {
                                $r->update($updates);
                            }
                        }
                    }

                    if ($otherDefaultCount > 0) {
                        $stats['fixed_default_flags']++;
                    }
                    if ($otherSelfCount > 0) {
                        $stats['fixed_multiple_self']++;
                    }

                    if (!$didSomething) {
                        $stats['ok']++;
                    }

                    if ($dryRun) {
                        DB::rollBack();
                    } else {
                        DB::commit();
                    }
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $this->error("Erreur pour pro_id={$pro->id} : " . $e->getMessage());
                }
            }
        });

        $this->newLine();
        $this->info("Résultat" . ($this->option('dry-run') ? " (DRY-RUN)" : "") . " :");
        foreach ($stats as $k => $v) {
            $this->line("- {$k}: {$v}");
        }

        return self::SUCCESS;
    }
}
