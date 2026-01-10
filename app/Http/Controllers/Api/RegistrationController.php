<?php

namespace App\Http\Controllers\Api;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Http\Resources\Api\PilgrimResource;
use App\Http\Resources\Api\RegistrationResource;
use App\Models\Bank;
use App\Models\GroupLeader;
use App\Models\Package;
use App\Models\PreRegistration;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class  RegistrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return RegistrationResource::collection(Registration::with('preRegistration.groupLeader', 'pilgrim.user', 'package', 'bank')
            ->latest()
            ->paginate($request->get('per_page', 10)));
    }

    public function banks(): JsonResponse
    {
        $banks = Bank::all()->map(function ($bank) {
            return [
                "type" => "bank",
                "id" => $bank->id,
                "attributes" => [
                    "name" => $bank->name,
                    "accountNumber" => $bank->account_number,
                ],
            ];
        });

        return response()->json(['data' => $banks]);
    }

    public function preRegistrations(): JsonResponse
    {
        $preRegistrations = PreRegistration::active()->with('pilgrim.user')->get()->map(function ($preRegistration) {
            return [
                "type" => "pre-registration",
                "id" => $preRegistration->id,
                "attributes" => [
                    "serialNo" => $preRegistration->serial_no,
                ],
                "relationships" => [
                    "pilgrim" => new PilgrimResource($preRegistration->relationLoaded('pilgrim') ? $preRegistration->pilgrim : null),
                ],
            ];
        });

        return response()->json(['data' => $preRegistrations]);
    }

    public function packages(): JsonResponse
    {
        $packages = Package::hajj()->active()->get()->map(function ($package) {
            return [
                "type" => "package",
                "id" => $package->id,
                "attributes" => [
                    "name" => $package->name,
                    "price" => $package->price,
                ],
            ];
        });

        return response()->json(['data' => $packages]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->error("This feature is under maintenance.", 503);
        $request->validate([
            "pre_registration_id" => ["required", "integer", "exists:pre_registrations,id"],
            "package_id" => ["required", "integer", "exists:packages,id"],
            "bank_id" => ["required", "integer", "exists:banks,id"],
            "passport_number" => ['required', 'string', 'max:100'],
            "passport_expiry_date" => ['required', 'date'],
            'date' => ['required', 'date'],
        ]);

        $preRegistration = PreRegistration::findOrFail($request->pre_registration_id);

        $preRegistration->registration()->create([
            'pilgrim_id' => $preRegistration->pilgrim_id,
            'package_id' => $request->package_id,
            'bank_id' => $request->bank_id,
            'date' => $request->date,
            'passport_number' => $request->passport_number,
            'passport_expiry_date' => $request->passport_expiry_date,
        ]);

        return $this->success("Registration created successfully.", 201);
    }

    public function update(Request $request, Registration $registration): JsonResponse
    {
        $request->validate([
            "pre_registration_id" => ["required", "integer", "exists:pre_registrations,id"],
            "package_id" => ["required", "integer", "exists:packages,id"],
            "bank_id" => ["required", "integer", "exists:banks,id"],
            "passport_number" => ['required', 'string', 'max:100'],
            "passport_expiry_date" => ['required', 'date'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(RegistrationStatus::values())]
        ]);

        $registration->update([
            'package_id' => $request->package_id,
            'bank_id' => $request->bank_id,
            'date' => $request->date,
            'passport_number' => $request->passport_number,
            'passport_expiry_date' => $request->passport_expiry_date,
            'status' => $request->status,
        ]);

        return $this->success("Registration updated successfully.");
    }

    public function destroy(Registration $registration): JsonResponse
    {
        $registration->delete();
        return $this->success("Registration deleted successfully.");
    }
}
