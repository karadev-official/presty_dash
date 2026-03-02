<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\AppointmentProduct;
use App\Models\AppointmentService;
use App\Models\AppointmentServiceOption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Appointment::class);
        $professional_profile_id = $request->user()->professionalProfile->id;
        return AppointmentResource::collection(Appointment::where('professional_profile_id', $professional_profile_id)->get());
    }

    public function store(AppointmentRequest $request)
    {
        $this->authorize('create', Appointment::class);
        $data = $request->validated();

        $appointment = new Appointment();
        DB::transaction(function () use (&$appointment, $data) {
            $appointment->fill($data);
            $appointment->save();

            if(!array_key_exists('products', $data)){
                return;
            }
            $products = $data['products'];
            foreach($products as $product){
                AppointmentProduct::create([
                    'appointment_id' => $appointment->id,
                    'product_id' => $product['product_id'],
                    'price' => $product['price'],
                    'quantity' => $product['quantity'],
                    'total' => $product['total'],
                    'notes' => $product['notes'],
                ]);
            }

            if(array_key_exists('services', $data)){
                $services = $data['services'];
                foreach($services as $service){
                    $newService = AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service['service_id'],
                        'price' => $service['price'],
                        'duration' => $service['duration'],
                        'quantity' => $service['quantity'],
                        'total' => $service['total'],
                        'notes' => $service['notes'],
                    ]);

                    $options = $service['options'];
                    foreach($options as $option){
                        AppointmentServiceOption::create([
                            'appointment_service_id' => $newService->id,
                            'service_option_id' => $option['service_option_id'],
                            'service_option_group_id' => $option['service_option_group_id'],
                            'option_name' => $option['option_name'],
                            'group_name' => $option['group_name'],
                            'price' => $option['price'],
                            'duration' => $option['duration'],
                        ]);
                    }
                }
            }
        });
        return new AppointmentResource($appointment);
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        return new AppointmentResource($appointment);
    }

    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $data = $request->validated();

        DB::transaction(function () use (&$appointment, $data) {
            // Mettre à jour les champs principaux
            $appointment->update($data);

            // ✅ Supprimer les anciens services et leurs options
            if (isset($data['services'])) {
                // Supprimer les options des anciens services
                foreach ($appointment->services as $oldService) {
                    $oldService->options()->delete();
                }
                // Supprimer les anciens services
                $appointment->services()->delete();

                // ✅ Créer les nouveaux services
                foreach ($data['services'] as $service) {
                    $newService = AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service['service_id'],
                        'price' => $service['price'],
                        'duration' => $service['duration'],
                        'quantity' => $service['quantity'],
                        'total' => $service['total'],
                        'notes' => $service['notes'] ?? null,
                    ]);

                    // ✅ Créer les options du service
                    if (isset($service['options']) && is_array($service['options'])) {
                        foreach ($service['options'] as $option) {
                            AppointmentServiceOption::create([
                                'appointment_service_id' => $newService->id,
                                'service_option_id' => $option['service_option_id'],
                                'service_option_group_id' => $option['service_option_group_id'],
                                'option_name' => $option['option_name'],
                                'group_name' => $option['group_name'],
                                'price' => $option['price'],
                                'duration' => $option['duration'],
                            ]);
                        }
                    }
                }
            }

            // ✅ Supprimer les anciens produits et créer les nouveaux
            if (isset($data['products'])) {
                // Supprimer les anciens produits
                $appointment->products()->delete();

                // ✅ Créer les nouveaux produits
                foreach ($data['products'] as $product) {
                    AppointmentProduct::create([
                        'appointment_id' => $appointment->id,
                        'product_id' => $product['product_id'],
                        'price' => $product['price'],
                        'quantity' => $product['quantity'],
                        'total' => $product['total'],
                        'notes' => $product['notes'] ?? null,
                    ]);
                }
            }
            if (isset($data['payments'])) {
                $incomingPaymentIds = collect($data['payments'])
                    ->pluck('id')
                    ->filter() // Enlever les null
                    ->toArray();

                // 1. Supprimer les paiements qui ne sont plus dans la liste
                $paymentsToDelete = $appointment->payments()
                    ->whereNotIn('id', $incomingPaymentIds)
                    ->get();

                foreach ($paymentsToDelete as $payment) {
                    $payment->forceDelete(); // ✅ Déclenche l'événement `deleted`
                }

                // 2. Créer ou mettre à jour les paiements
                foreach ($data['payments'] as $paymentData) {
                    if (isset($paymentData['id'])) {
                        // ✅ Mise à jour d'un paiement existant
                        $payment = $appointment->payments()->find($paymentData['id']);
                        $payment?->update([
                            'payment_method_id' => $paymentData['payment_method_id'],
                            'amount' => $paymentData['amount'],
                            'is_deposit' => $paymentData['is_deposit'] ?? false,
                            'notes' => $paymentData['notes'] ?? null,
                        ]);
                    } else {
                        // ✅ Création d'un nouveau paiement
                        $appointment->payments()->create([
                            'payment_method_id' => $paymentData['payment_method_id'],
                            'amount' => $paymentData['amount'],
                            'is_deposit' => $paymentData['is_deposit'] ?? false,
                            'notes' => $paymentData['notes'] ?? null,
                            'paid_at' => now(),
                        ]);
                    }
                }
            }
        });

        // Recharger les relations pour le retour
        $appointment->load([
            'customer',
            'services.service',
            'services.options',
            'products.product',
            'payments.paymentMethod',
            'workplace.address'
        ]);
        return new AppointmentResource($appointment);
    }

    public function cancel(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);
        $appointment->cancel();
        return new AppointmentResource($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return response()->json();
    }
}
