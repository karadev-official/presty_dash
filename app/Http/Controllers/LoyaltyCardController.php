<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoyaltyCardResource;
use App\Models\Customer;
use App\Models\LoyaltyCard;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use function Pest\Laravel\json;

class LoyaltyCardController extends Controller
{

    public function __construct(
        protected LoyaltyService $loyaltyService,
    ){}
    public function index(Request $request)
    {
        $loyaltyProgram = $request->user()->professionalProfile->loyaltyProgram;
        $cards = $loyaltyProgram->cards;
        return LoyaltyCardResource::collection($cards);
    }

    public function show(Request $request, Customer $customer)
    {
        $professionalProfile = $request->user()->professionalProfile;
        $program = $professionalProfile->loyaltyProgram;

        if (!$program) {
            return response()->json(['message' => 'Aucun programme de fidélité'], 404);
        }

        $card = LoyaltyCard::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'loyalty_program_id' => $program->id,
            ],
            [
                'total_visits' => 0,
                'is_active' => true,
            ]
        );

        return response()->json([
            'card' => new LoyaltyCardResource($card),
            'stats' => $this->loyaltyService->getCardStats($card),
        ]);
    }

    public function update(Request $request, LoyaltyCard $loyaltyCard)
    {
        $validated = $request->validate([
            'total_visits' => 'sometimes|required|integer|min:0',
            'is_active' => 'sometimes|required|boolean',
        ]);
        $loyaltyCard->total_visits = $validated['total_visits'];
        $loyaltyCard->is_active = $validated['is_active'];
        $loyaltyCard->save();
        return response()->json([
            'card' => new LoyaltyCardResource($loyaltyCard),
            'stats' => $this->loyaltyService->getCardStats($loyaltyCard),
        ]);
    }
}
