<?php

namespace App\Http\Controllers\Api;

use App\Enums\PilgrimLogType;
use App\Enums\PreRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PreRegistrationResource;
use App\Models\Bank;
use App\Models\GroupLeader;
use App\Models\Passport;
use App\Models\Pilgrim;
use App\Models\PilgrimLog;
use App\Models\PreRegistration;
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
        return PreRegistrationResource::collection(PreRegistration::with(
            [
                'groupLeader',
                'pilgrim.user.presentAddress',
                'pilgrim.user.permanentAddress',
                'groupLeader',
                'bank'
            ]
        )->latest()->paginate($request->get('per_page', 10)));
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

    public function pilgrims(): JsonResponse
    {
        $pilgrims = User::whereHas('pilgrim')->get()->map(function ($user) {
            return [
                'type' => 'pilgrim',
                'id' => $user->pilgrim->id,
                'attributes' => [
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "group_leader_id" => ["required", "integer", "exists:group_leaders,id"],
            'status' => ['required', Rule::in(['active', 'pending'])],
            "bank_id" => ['required_if:status,active', "integer", "exists:banks,id"],
            'serial_no' => ['required_if:status,active', 'string', 'max:100'],
            'tracking_no' => ['required_if:status,active', 'string', 'max:100'],
            'bank_voucher_no' => ['required_if:status,active', 'string', 'max:100'],
            'voucher_name' => ['required_if:status,active', 'string', 'max:255'],
            'date' => ['required_if:status,active', 'date'],

            'pilgrim_type' => ['required', 'in:existing,new'],
            'pilgrim_id' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->pilgrim_type === 'existing';
                }),
                'exists:pilgrims,id'
            ],

            'new_pilgrim' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->pilgrim_type === 'new';
                }),
                'array'
            ],
            'new_pilgrim.avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'new_pilgrim.first_name' => ['required_with:new_pilgrim', 'string'],
            'new_pilgrim.first_name_bangla' => ['required_with:new_pilgrim', 'string'],
            'new_pilgrim.last_name' => ['nullable', 'string'],
            'new_pilgrim.last_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.mother_name' => ['nullable', 'string'],
            'new_pilgrim.mother_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.father_name' => ['nullable', 'string'],
            'new_pilgrim.father_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.occupation' => ['nullable', 'string'],
            'new_pilgrim.spouse_name' => ['nullable', 'string'],
            'new_pilgrim.email' => ['nullable', 'email', 'unique:users,email'],
            'new_pilgrim.phone' => ['nullable', 'string'],
            'new_pilgrim.gender' => ['required_with:new_pilgrim', 'in:male,female,other'],
            'new_pilgrim.is_married' => ['required_with:new_pilgrim', 'boolean'],
            'new_pilgrim.nid' => ['required_with:new_pilgrim', 'string', 'unique:users,nid'],
            'new_pilgrim.birth_certificate_number' => ['nullable', 'string', 'unique:users,birth_certificate_number'],
            'new_pilgrim.date_of_birth' => ['required_with:new_pilgrim', 'date'],

            'new_pilgrim.present_address' => ['required', 'array'],
            'new_pilgrim.present_address.house_no' => ['nullable', 'string', 'max:255'],
            'new_pilgrim.present_address.road_no' => ['nullable', 'string', 'max:255'],
            'new_pilgrim.present_address.village' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.post_office' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.police_station' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.district' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.division' => ['required', 'string', 'max:255'],
            'new_pilgrim.present_address.postal_code' => ['required', 'string', 'max:20'],
            'new_pilgrim.present_address.country' => ['nullable', 'string', 'max:255'],

            'same_as_present_address' => ['required', 'boolean'],

            'new_pilgrim.permanent_address' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'array'
            ],
            'new_pilgrim.permanent_address.house_no' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'nullable',
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.road_no' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'nullable',
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.village' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.post_office' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.police_station' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.district' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.division' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'string',
                'max:255'
            ],
            'new_pilgrim.permanent_address.postal_code' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'string',
                'max:20'
            ],
            'new_pilgrim.permanent_address.country' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->boolean('same_as_present_address');
                }),
                'nullable',
                'string',
                'max:255'
            ],

            'passport_type' => ['required', 'in:existing,new,none'],
            'passport_id' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->passport_type === 'existing';
                }),
                'exists:passports,id'
            ],
            'new_passport' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->passport_type === 'new';
                }),
                'array'
            ],
            'new_passport.passport_number' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->passport_type === 'new';
                }),
                'string',
                'unique:passports,passport_number'
            ],
            'new_passport.issue_date' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->passport_type === 'new';
                }),
                'date'
            ],
            'new_passport.expiry_date' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->passport_type === 'new';
                }),
                'date',
                'after:new_passport.issue_date'
            ],
            'new_passport.passport_type' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->passport_type === 'new';
                }),
                'in:ordinary,official,diplomatic'
            ],
            'new_passport.file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'new_passport.notes' => ['nullable', 'string'],
        ]);
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
                'bank_id' => $validated['bank_id'] ?? null,
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

    public function update(Request $request, PreRegistration $preRegistration): JsonResponse
    {
        $request->validate([
            "group_leader_id" => ["required", "integer", "exists:group_leaders,id"],
            "bank_id" => ["required", "integer", "exists:banks,id"],
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["nullable", "string", "max:255"],
            "mother_name" => ["nullable", "string", "max:255"],
            "father_name" => ["nullable", "string", "max:255"],
            "email" => ["nullable", "string", "email", "max:255", "unique:users,email," . $preRegistration->pilgrim->user->id],
            "phone" => ["nullable", "string", "max:20"],
            "gender" => ["required", "in:male,female,other"],
            "is_married" => ["required", "boolean"],
            'date_of_birth' => ['nullable', 'date'],
            'nid' => ['required', 'string', 'max:100', 'unique:users,nid,' . $preRegistration->pilgrim->user->id],
            'status' => ['required', Rule::in(PreRegistrationStatus::values())],
            'serial_no' => ['nullable', 'string', 'max:100'],
            'bank_voucher_no' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
        ]);

        $user = $preRegistration->pilgrim->user;

        $user->update([
            "first_name" => $request->first_name,
            "last_name" => $request->last_name ?? null,
            "mother_name" => $request->mother_name ?? null,
            "father_name" => $request->father_name ?? null,
            "email" => $request->email ?? null,
            "phone" => $request->phone ?? null,
            "gender" => $request->gender,
            "is_married" => $request->is_married,
            "nid" => $request->nid,
            "date_of_birth" => $request->date_of_birth ?? null,
        ]);

        $preRegistration->update([
            'group_leader_id' => $request->group_leader_id,
            'bank_id' => $request->bank_id,
            'serial_no' => $request->serial_no,
            'bank_voucher_no' => $request->bank_voucher_no ?? null,
            'date' => $request->date,
            'status' => $request->status,
        ]);

        return $this->success("Pre-registration updated successfully.");
    }

    public function destroy(PreRegistration $preRegistration): JsonResponse
    {
        $preRegistration->delete();
        return $this->success("Pre-registration deleted successfully.");
    }
}
