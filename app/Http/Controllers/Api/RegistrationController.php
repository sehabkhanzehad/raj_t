<?php

namespace App\Http\Controllers\Api;

use App\Enums\PilgrimLogType;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Http\Resources\Api\PassportResource;
use App\Http\Resources\Api\PilgrimResource;
use App\Http\Resources\Api\PreRegistrationResource;
use App\Models\Bank;
use App\Models\GroupLeader;
use App\Models\Package;
use App\Models\Passport;
use App\Models\PilgrimLog;
use App\Models\PreRegistration;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class  RegistrationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PreRegistrationResource::collection(
            PreRegistration::whereHas('registration', fn($query) => $query->currentYear())
                ->with([
                    'groupLeader',
                    'pilgrim.user.presentAddress',
                    'pilgrim.user.permanentAddress',
                    'groupLeader',
                    'passports',
                    'registration.package',
                    'registration.bank',
                    'registration.preRegistration',
                ])->latest()->paginate(perPage())
        );
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
        $preRegistrations = PreRegistration::active()->whereDoesntHave('registration')->with('pilgrim.user', 'passports')->get()->map(function ($preRegistration) {
            return [
                "type" => "pre-registration",
                "id" => $preRegistration->id,
                "attributes" => [
                    "serialNo" => $preRegistration->serial_no,
                ],
                "relationships" => [
                    "pilgrim" => new PilgrimResource($preRegistration->relationLoaded('pilgrim') ? $preRegistration->pilgrim : null),
                    "passport" => $preRegistration->relationLoaded('passports') && $preRegistration->hasPassport() ? new PassportResource($preRegistration->passport()) : null,
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
        $validated = $request->validate([
            "pre_registration_id" => ["required", "integer", "exists:pre_registrations,id"],
            "package_id" => ["required", "integer", "exists:packages,id"],
            "bank_id" => ["required", "integer", "exists:banks,id"],
            "date" => ['required', 'date'],

            "passport_number" => ['required', 'string', 'max:100'],
            "passport_type" => ['required', 'in:ordinary,official,diplomatic'],
            "issue_date" => ['required', 'date'],
            "expiry_date" => ['required', 'date', 'after:issue_date'],
            "passport_file" => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'passport_notes' => ['nullable', 'string', 'max:1000'],
        ]);


        $preRegistration = PreRegistration::findOrFail($request->pre_registration_id);

        if (Registration::where('pre_registration_id', $preRegistration->id)->exists()) {
            return $this->error("This pre-registration has already been registered for the current year.", 409);
        }

        if ($preRegistration->hasPassport()) {
            $passport = $preRegistration->passport();

            if ($request->has('passport_file')) {
                $passport->deleteFile();

                $passport->file_path = $request->hasFile('passport_file')
                    ? $request->file('passport_file')->storeAs('passports', $validated['passport_number'] . '.' . $request->file('passport_file')->getClientOriginalExtension())
                    : null;
            }

            $passport->passport_number = $validated['passport_number'];
            $passport->passport_type = $validated['passport_type'];
            $passport->issue_date = $validated['issue_date'];
            $passport->expiry_date = $validated['expiry_date'];
            $passport->notes = $validated['passport_notes'] ?? null;
            $passport->save();
        } else {
            if ($request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $passportNumber = $validated['passport_number'];
                $extension = $file->getClientOriginalExtension();
                $fileName = "$passportNumber.$extension";
                $filePath = $file->storeAs('passports', $fileName);
                $validated['file_path'] = $filePath;
            }

            $passport = Passport::create([
                'passport_number' => $validated['passport_number'],
                'passport_type' => $validated['passport_type'],
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'],
                'file_path' => $validated['file_path'] ?? null,
                'notes' => $validated['passport_notes'] ?? null,
                'pilgrim_id' => $preRegistration->pilgrim_id,
            ]);

            $preRegistration->assignPassport($passport);
        }

        $registration = $preRegistration->registration()->create([
            'pilgrim_id' => $preRegistration->pilgrim_id,
            'package_id' => $request->package_id,
            'bank_id' => $request->bank_id,
            'date' => $request->date,
        ]);

        PilgrimLog::add(
            $registration->pilgrim,
            $registration->id,
            Registration::class,
            PilgrimLogType::HajjRegistered,
            "হজ রেজিস্ট্রেশন করা হয়েছে।"
        );

        return $this->success("Registration created successfully.", 201);
    }

    public function update(Request $request, Registration $registration): JsonResponse
    {
        $request->validate([
            "pre_registration_id" => ["required", "integer", "exists:pre_registrations,id"],
            "package_id" => ["required", "integer", "exists:packages,id"],
            "bank_id" => ["required", "integer", "exists:banks,id"],
            "date" => ['required', 'date'],
        ]);

        $registration->update([
            'pre_registration_id' => $request->pre_registration_id,
            'package_id' => $request->package_id,
            'bank_id' => $request->bank_id,
            'date' => $request->date,
        ]);

        return $this->success("Registration updated successfully.");
    }

    public function destroy(Registration $registration): JsonResponse
    {
        return $this->error("Registration deletion is disabled temporarily.", 403);
        $registration->delete();
        return $this->success("Registration deleted successfully.");
    }
}
