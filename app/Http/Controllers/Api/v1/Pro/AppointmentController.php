<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\AppointmentProduct;
use App\Models\AppointmentService;
use App\Models\AppointmentServiceOption;
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
        DB::transaction(function () use (&$appointment, $request, $data) {
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

        $appointment->update($request->validated());

        return new AppointmentResource($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return response()->json();
    }
}
