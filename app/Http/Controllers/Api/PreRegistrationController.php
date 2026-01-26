<?php

namespace App\Http\Controllers\Api;

use App\Enums\PilgrimLogType;
use App\Enums\PreRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Web\StorePreRegRequest;
use App\Http\Resources\Api\PreRegistrationResource;
use App\Http\Resources\Api\TransactionResource;
use App\Models\GroupLeader;
use App\Models\Passport;
use App\Models\Pilgrim;
use App\Models\PilgrimLog;
use App\Models\PreRegistration;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PreRegistrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PreRegistration::with(
            [
                'groupLeader',
                'pilgrim.user.presentAddress',
                'pilgrim.user.permanentAddress',
                'passports',
                'registration',
            ]
        )->whereIn('status', [PreRegistrationStatus::Active, PreRegistrationStatus::Pending]);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_no', 'like', '%' . $search . '%')
                    ->orWhere('tracking_no', 'like', '%' . $search . '%')
                    ->orWhereHas('pilgrim.user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', '%' . $search . '%')
                            ->orWhere('full_name_bangla', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%')
                            ->orWhere('nid', 'like', '%' . $search . '%');
                    });
            });
        }

        return PreRegistrationResource::collection($query->latest()->paginate(perPage()));
    }

    public function groupLeaders(): JsonResponse
    {
        $groupLeaders = GroupLeader::all()->map(function ($groupLeader) {
            return [
                'type' => 'group-leader',
                'id' => $groupLeader->id,
                'attributes' => [
                    'groupName' => $groupLeader->group_name,
                ],
            ];
        });

        return response()->json(['data' => $groupLeaders]);
    }

    public function pilgrims(): JsonResponse
    {
        $pilgrims = User::whereHas('pilgrim')->get()->map(function ($user) {
            return [
                'type' => 'pilgrim',
                'id' => $user->pilgrim->id,
                'attributes' => [
                    'avatar' => $user->avatar,
                    'fullName' => $user->full_name,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                ],
            ];
        });

        return response()->json(['data' => $pilgrims]);
    }

    public function passports(Request $request): JsonResponse
    {
        $request->validate([
            'pilgrim_id' => ['required', 'exists:pilgrims,id'],
        ]);

        $passports = Passport::where('pilgrim_id', $request->pilgrim_id)->get();

        $passports = $passports ? $passports->map(function ($passport) {
            return [
                'type' => 'passport',
                'id' => $passport->id,
                'attributes' => [
                    'passportNumber' => $passport->passport_number,
                    'issueDate' => $passport->issue_date,
                    'expiryDate' => $passport->expiry_date,
                    'passportType' => $passport->passport_type,
                ],
            ];
        }) : [];

        return response()->json(['data' => $passports]);
    }

    public function store(StorePreRegRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Handle Pilgrim
            if ($request->pilgrim_type === 'existing') {
                $pilgrimId = $validated['pilgrim_id'];
                $pilgrim = Pilgrim::find($pilgrimId);
            } else {

                // handle avatar upload if exists
                if ($request->hasFile('new_pilgrim.avatar')) {
                    $file = $request->file('new_pilgrim.avatar');

                    $firstName = $validated['new_pilgrim']['first_name'];
                    $timestamp = time();
                    $random = uniqid();
                    $extension = $file->getClientOriginalExtension();

                    $fileName = "{$firstName}_{$timestamp}_{$random}.$extension";
                    $filePath = $file->storeAs('avatars', $fileName);
                    $validated['new_pilgrim']['avatar_path'] = $filePath;
                }

                $user = User::create([
                    'avatar' => $validated['new_pilgrim']['avatar_path'] ?? null,
                    'first_name' => $validated['new_pilgrim']['first_name'],
                    'last_name' => $validated['new_pilgrim']['last_name'] ?? null,
                    'full_name' => trim($validated['new_pilgrim']['first_name'] . ' ' . ($validated['new_pilgrim']['last_name'] ?? '')),
                    'first_name_bangla' => $validated['new_pilgrim']['first_name_bangla'],
                    'last_name_bangla' => $validated['new_pilgrim']['last_name_bangla'] ?? null,
                    'full_name_bangla' => trim($validated['new_pilgrim']['first_name_bangla'] . ' ' . ($validated['new_pilgrim']['last_name_bangla'] ?? '')),
                    'mother_name' => $validated['new_pilgrim']['mother_name'] ?? null,
                    'mother_name_bangla' => $validated['new_pilgrim']['mother_name_bangla'] ?? null,
                    'father_name' => $validated['new_pilgrim']['father_name'] ?? null,
                    'father_name_bangla' => $validated['new_pilgrim']['father_name_bangla'] ?? null,
                    'occupation' => $validated['new_pilgrim']['occupation'] ?? null,
                    'spouse_name' => $validated['new_pilgrim']['spouse_name'] ?? null,
                    'email' => $validated['new_pilgrim']['email'] ?? null,
                    'phone' => $validated['new_pilgrim']['phone'] ?? null,
                    'gender' => $validated['new_pilgrim']['gender'],
                    'is_married' => $validated['new_pilgrim']['is_married'] ?? false,
                    'nid' => $validated['new_pilgrim']['nid'] ?? null,
                    'birth_certificate_number' => $validated['new_pilgrim']['birth_certificate_number'] ?? null,
                    'date_of_birth' => $validated['new_pilgrim']['date_of_birth'] ?? null,
                ]);
                $pilgrim = $user->pilgrim()->create();
                $pilgrimId = $pilgrim->id;

                // Save Present Address
                $user->presentAddress()->create(array_merge(
                    $validated['new_pilgrim']['present_address'],
                    ['type' => 'present']
                ));

                // Save Permanent Address
                if ($request->boolean('same_as_present_address')) {
                    $permanentAddressData = $validated['new_pilgrim']['present_address'];
                } else {
                    $permanentAddressData = $validated['new_pilgrim']['permanent_address'];
                }

                $user->permanentAddress()->create(array_merge(
                    $permanentAddressData,
                    ['type' => 'permanent']
                ));
            }

            // Handle Passport
            $passport = null;
            if ($request->passport_type === 'existing') {
                // Use existing passport
                $passport = Passport::find($validated['passport_id']);
            } elseif ($request->passport_type === 'new') {
                // Create new passport

                // handle file upload if exists
                if ($request->hasFile('new_passport.file')) {
                    $file = $request->file('new_passport.file');
                    $passportNumber = $validated['new_passport']['passport_number'];
                    $extension = $file->getClientOriginalExtension();
                    $fileName = "$passportNumber.$extension";
                    $filePath = $file->storeAs('passports', $fileName);
                    $validated['new_passport']['file_path'] = $filePath;
                }

                $passport = Passport::create([
                    'pilgrim_id' => $pilgrimId,
                    'passport_number' => $validated['new_passport']['passport_number'],
                    'issue_date' => $validated['new_passport']['issue_date'],
                    'expiry_date' => $validated['new_passport']['expiry_date'],
                    'passport_type' => $validated['new_passport']['passport_type'],
                    'file_path' => $validated['new_passport']['file_path'] ?? null,
                    'notes' => $validated['new_passport']['notes'] ?? null,
                ]);
            }

            $preReg = $pilgrim->preRegistrations()->create([
                'group_leader_id' => $validated['group_leader_id'],
                'serial_no' => $validated['serial_no'] ?? null,
                'bank_voucher_no' => $validated['bank_voucher_no'] ?? null,
                'tracking_no' => $validated['tracking_no'] ?? null,
                'voucher_name' => $validated['voucher_name'] ?? null,
                'date' => $validated['date'] ?? null,
                'status' => $validated['status'],
            ]);

            if ($passport) $preReg->assignPassport($passport);

            if ($preReg->isActive()) {
                PilgrimLog::add(
                    $pilgrim,
                    $preReg->id,
                    PreRegistration::class,
                    PilgrimLogType::HajjPreRegistered,
                    "হজ প্রি-রেজিস্ট্রেশন করা হয়েছে।"
                );
            }
            DB::commit();

            return $this->success("Pre-registration created successfully.", 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed to create Pre-registration: " . $e->getMessage());
        }
    }

    public function markAsRegistered(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $validated = $request->validate([
            'serial_no' => ['required', 'string', 'max:100'],
            'tracking_no' => ['required', 'string', 'max:100'],
            'bank_voucher_no' => ['required', 'string', 'max:100'],
            'voucher_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $validated['status'] = PreRegistrationStatus::Registered;

        $preRegistration->update($validated);

        PilgrimLog::add(
            $preRegistration->pilgrim,
            $preRegistration->id,
            PreRegistration::class,
            PilgrimLogType::HajjPreRegistered,
            "Hajj Pre-Registration completed."
        );

        return $this->success("Marked as registered successfully.");
    }

    public function show(PreRegistration $preRegistration): PreRegistrationResource
    {
        $preRegistration->load([
            'groupLeader',
            'pilgrim.user.presentAddress',
            'pilgrim.user.permanentAddress',
            'passports',
        ]);

        return new PreRegistrationResource($preRegistration);
    }

    public function destroy(PreRegistration $preRegistration): JsonResponse
    {
        return $this->error("Currently, pre-registration deletion is disabled.");
        $preRegistration->delete();
        return $this->success("Pre-registration deleted successfully.");
    }

    public function addPassport(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $validated = $request->validate([
            'passport_number' => ['required', 'string', 'unique:passports,passport_number'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            'passport_type' => ['required', 'in:ordinary,official,diplomatic'],
            'file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        // Get pilgrim from pre-registration
        $pilgrimId = $preRegistration->pilgrim_id;

        // handle file upload if exists
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $passportNumber = $validated['passport_number'];
            $extension = $file->getClientOriginalExtension();
            $fileName = "$passportNumber.$extension";
            $filePath = $file->storeAs('passports', $fileName);
            $validated['file_path'] = $filePath;
        }

        $passport = Passport::create([
            'pilgrim_id' => $pilgrimId,
            'passport_number' => $validated['passport_number'],
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'passport_type' => $validated['passport_type'],
            'file_path' => $validated['file_path'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Attach passport to pre-registration
        $preRegistration->assignPassport($passport);

        return $this->success("Passport added successfully.");
    }

    public function updatePassport(Request $request, Passport $passport): JsonResponse
    {
        $validated = $request->validate([
            'passport_number' => ['required', 'string', 'unique:passports,passport_number,' . $passport->id],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            'passport_type' => ['required', 'in:ordinary,official,diplomatic'],
            'file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->has('file')) {
            $passport->deleteFile();

            $passport->file_path = $request->hasFile('file')
                ? $request->file('file')->storeAs('passports', $validated['passport_number'] . '.' . $request->file('file')->getClientOriginalExtension())
                : null;
        }

        $passport->passport_number = $validated['passport_number'];
        $passport->issue_date = $validated['issue_date'];
        $passport->expiry_date = $validated['expiry_date'];
        $passport->passport_type = $validated['passport_type'];
        $passport->notes = $validated['notes'] ?? null;
        $passport->save();

        return $this->success("Passport updated successfully.");
    }

    public function updateAddresses(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $validated = $request->validate([
            'present_address' => ['required', 'array'],
            'present_address.house_no' => ['nullable', 'string', 'max:255'],
            'present_address.road_no' => ['nullable', 'string', 'max:255'],
            'present_address.village' => ['required', 'string', 'max:255'],
            'present_address.post_office' => ['required', 'string', 'max:255'],
            'present_address.police_station' => ['required', 'string', 'max:255'],
            'present_address.district' => ['required', 'string', 'max:255'],
            'present_address.division' => ['required', 'string', 'max:255'],
            'present_address.postal_code' => ['required', 'string', 'max:20'],
            'present_address.country' => ['nullable', 'string', 'max:255'],

            'same_as_present_address' => ['required', 'boolean'],

            'permanent_address' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'array'
            ],
            'permanent_address.house_no' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'nullable',
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.road_no' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'nullable',
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.village' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.post_office' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.police_station' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.district' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.division' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
            'permanent_address.postal_code' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:20'])
            ],
            'permanent_address.country' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'nullable',
                Rule::when(!$request->boolean('same_as_present_address'), ['string', 'max:255'])
            ],
        ]);

        $user = $preRegistration->pilgrim->user;

        DB::beginTransaction();
        try {
            // Update Present Address
            $user->presentAddress()->updateOrCreate(
                ['addressable_id' => $user->id, 'addressable_type' => User::class, 'type' => 'present'],
                array_merge($validated['present_address'], ['type' => 'present'])
            );

            // Update Permanent Address
            if ($request->boolean('same_as_present_address')) {
                // Copy present to permanent
                $user->permanentAddress()->updateOrCreate(
                    ['addressable_id' => $user->id, 'addressable_type' => User::class, 'type' => 'permanent'],
                    array_merge($validated['present_address'], ['type' => 'permanent'])
                );
            } else {
                // Use provided permanent address
                $user->permanentAddress()->updateOrCreate(
                    ['addressable_id' => $user->id, 'addressable_type' => User::class, 'type' => 'permanent'],
                    array_merge($validated['permanent_address'], ['type' => 'permanent'])
                );
            }

            DB::commit();

            return $this->success("Addresses updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed to update addresses: " . $e->getMessage());
        }
    }

    public function updatePilgrimPersonalInfo(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string'],
            'first_name_bangla' => ['required', 'string'],
            'last_name' => ['nullable', 'string'],
            'last_name_bangla' => ['nullable', 'string'],
            'father_name' => ['nullable', 'string'],
            'father_name_bangla' => ['nullable', 'string'],
            'mother_name' => ['nullable', 'string'],
            'mother_name_bangla' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string'],
            'spouse_name' => ['nullable', 'string'],
        ]);

        $user = $preRegistration->pilgrim->user;
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'full_name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'first_name_bangla' => $validated['first_name_bangla'],
            'last_name_bangla' => $validated['last_name_bangla'] ?? null,
            'full_name_bangla' => trim($validated['first_name_bangla'] . ' ' . ($validated['last_name_bangla'] ?? '')),
            'father_name' => $validated['father_name'] ?? null,
            'father_name_bangla' => $validated['father_name_bangla'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'mother_name_bangla' => $validated['mother_name_bangla'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'spouse_name' => $validated['spouse_name'] ?? null,
        ]);

        return $this->success("Personal information updated successfully.");
    }

    public function updatePilgrimContactInfo(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $user = $preRegistration->pilgrim->user;

        $validated = $request->validate([
            'email' => ['nullable', 'email', "unique:users,email,{$user->id}"],
            'phone' => ['nullable', 'string'],
            'gender' => ['required', 'in:male,female,other'],
            'is_married' => ['required', 'boolean'],
            'nid' => ['nullable', 'string', "unique:users,nid,{$user->id}"],
            'birth_certificate_number' => ['nullable', 'string', "unique:users,birth_certificate_number,{$user->id}"],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $user->update($validated);

        return $this->success("Contact & identification updated successfully.");
    }

    public function updatePilgrimAvatar(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $request->validate([
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $user = $preRegistration->pilgrim->user;

        if ($request->has('avatar')) {
            $user->deleteAvatar();

            $user->avatar = $request->hasFile('avatar')
                ? $request->file('avatar')->storeAs(
                    'avatars',
                    $user->first_name . '_' . time() . '_' . uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension()
                )
                : null;
        }

        $user->save();

        return $this->success("Avatar updated successfully.");
    }

    public function updatePreRegDetails(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $validated = $request->validate([
            'serial_no' => ['required', 'string', 'max:100'],
            'tracking_no' => ['required', 'string', 'max:100'],
            'bank_voucher_no' => ['required', 'string', 'max:100'],
            'voucher_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $preRegistration->update($validated);

        return $this->success("Pre-registration details updated successfully.");
    }

    public function transactions(PreRegistration $preRegistration): AnonymousResourceCollection
    {
        $transactions = Transaction::whereHas('references', fn($query) => $query->where('referenceable_id', $preRegistration->id)->where('referenceable_type', PreRegistration::class))
            ->latest()
            ->paginate(request()->get('per_page', 10));

        return TransactionResource::collection($transactions);
    }
}
