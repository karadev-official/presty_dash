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

            if(!array_key_exists('appointment_products', $data)){
                return;
            }
            $appointmentProducts = $data['appointment_products'];
            foreach($appointmentProducts as $appointmentProduct){
                AppointmentProduct::create([
                    'appointment_id' => $appointment->id,
                    'product_id' => $appointmentProduct['product_id'],
                    'price' => $appointmentProduct['price'],
                    'name' => $appointmentProduct['name'],
                    'quantity' => $appointmentProduct['quantity'],
                    'total' => $appointmentProduct['total'],
                    'notes' => $appointmentProduct['notes'],
                ]);
            }

            if(array_key_exists('appointment_services', $data)){
                $appointmentServices = $data['appointment_services'];
                foreach($appointmentServices as $appointmentService){
                    $newService = AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $appointmentService['service_id'],
                        'price' => $appointmentService['price'],
                        'name' => $appointmentService['name'],
                        'duration' => $appointmentService['duration'],
                        'quantity' => $appointmentService['quantity'],
                        'total' => $appointmentService['total'],
                        'notes' => $appointmentService['notes'],
                    ]);

                    $appointmentServiceOptions = $appointmentService['options'];
                    foreach($appointmentServiceOptions as $appointmentServiceOption){
                        AppointmentServiceOption::create([
                            'appointment_service_id' => $newService->id,
                            'service_option_id' => $appointmentServiceOption['service_option_id'],
                            'service_option_group_id' => $appointmentServiceOption['service_option_group_id'],
                            'option_name' => $appointmentServiceOption['option_name'],
                            'group_name' => $appointmentServiceOption['group_name'],
                            'price' => $appointmentServiceOption['price'],
                            'duration' => $appointmentServiceOption['duration'],
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
            if (isset($data['appointment_services'])) {
                // Supprimer les options des anciens services
                foreach ($appointment->services as $oldService) { // ✅ Changé de services à appointment_services
                    $oldService->options()->delete();
                }
                // Supprimer les anciens services
                $appointment->services()->delete(); // ✅ Changé de services() à appointment_services()

                // ✅ Créer les nouveaux services
                foreach ($data['appointment_services'] as $appointmentService) {
                    $newService = AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $appointmentService['service_id'],
                        'price' => $appointmentService['price'],
                        'name' => $appointmentService['name'],
                        'duration' => $appointmentService['duration'],
                        'quantity' => $appointmentService['quantity'],
                        'total' => $appointmentService['total'],
                        'notes' => $appointmentService['notes'] ?? null,
                    ]);

                    // ✅ Créer les options du service
                    if (isset($appointmentService['options']) && is_array($appointmentService['options'])) {
                        foreach ($appointmentService['options'] as $option) {
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
            if (isset($data['appointment_products'])) {
                // Supprimer les anciens produits
                $appointment->products()->delete(); // ✅ Changé de products() à appointment_products()

                // ✅ Créer les nouveaux produits
                foreach ($data['appointment_products'] as $appointmentProduct) {
                    AppointmentProduct::create([
                        'appointment_id' => $appointment->id,
                        'product_id' => $appointmentProduct['product_id'],
                        'price' => $appointmentProduct['price'],
                        'name' => $appointmentProduct['name'],
                        'quantity' => $appointmentProduct['quantity'],
                        'total' => $appointmentProduct['total'],
                        'notes' => $appointmentProduct['notes'] ?? null,
                    ]);
                }
            }

            // ✅ Gérer les paiements
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
            'services.options',
            'products',
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
