<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfessionalProfileResource;
use App\Http\Resources\PublicProDetailResource;
use App\Http\Resources\PublicProResource;
use App\Models\ProfessionalProfile;
use App\Services\SlotGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    protected $slotGenerator;
    public function __construct(SlotGeneratorService $slotGenerator)
    {
        $this->slotGenerator = $slotGenerator;
    }

    public function pros(Request $request)
    {
        $pros = ProfessionalProfile::with([
            'pro',
            'workplaces.address',
        ])->get();

        return PublicProResource::collection($pros);
    }

    public function proById(Request $request, $id)
    {
        $pro = ProfessionalProfile::where('id', $id)
            ->firstOrFail();
        return new PublicProDetailResource($pro);
    }

    public function getProSlots(Request $request, int $professionalProfileId): JsonResponse
    {
        // LOG POUR DEBUG
//        \Log::info('📥 Requête reçue pour les slots', [
//            'professionalProfileId' => $professionalProfileId,
//            'all_params' => $request->all(),
//            'query_params' => $request->query(),
//            'input' => $request->input(),
//            'method' => $request->method(),
//        ]);

        $validated = $request->validate([
            'duration_min' => 'required|integer|min:5|max:480',
            'start_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
            'days_ahead' => 'nullable|integer|min:1|max:120',
        ]);

        $slots = $this->slotGenerator->getAvailableSlots(
            $professionalProfileId,
            $validated['duration_min'],
            $validated['start_date'] ?? null,
            $validated['days_ahead'] ?? 30
        );

        return response()->json([
            'data' => $slots,
            'meta' => [
                'professional_profile_id' => $professionalProfileId,
                'duration_min' => $validated['duration_min'],
                'start_date' => $validated['start_date'] ?? now()->format('Y-m-d'),
                'days_ahead' => $validated['days_ahead'] ?? 30,
                'total_days' => count($slots),
                'total_slots' => array_sum(array_map(fn($day) => count($day['slots']), $slots)),
            ]
        ]);
    }

}
