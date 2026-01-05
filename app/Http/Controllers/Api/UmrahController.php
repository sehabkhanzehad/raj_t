<?php

namespace App\Http\Controllers\Api;

use App\Enums\PilgrimLogType;
use App\Enums\UmrahStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UmrahResource;
use App\Models\GroupLeader;
use App\Models\Package;
use App\Models\Passport;
use App\Models\Pilgrim;
use App\Models\PilgrimLog;
use App\Models\Umrah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UmrahController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UmrahResource::collection(Umrah::with(['year', 'groupLeader', 'pilgrim.user', 'package', 'passports'])->paginate(request()->get('per_page', 10)));
    }

    public function packages(): JsonResponse
    {
        $packages = Package::umrah()->active()->get()->map(function ($package) {
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
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
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

    public function addPassport(Request $request, Umrah $umrah): JsonResponse
    {
        $validated = $request->validate([
            'passport_number' => ['required', 'string', 'unique:passports,passport_number'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            'passport_type' => ['required', 'in:ordinary,official,diplomatic'],
            'file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        // Get pilgrim from umrah
        $pilgrimId = $umrah->pilgrim_id;

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

        // Attach passport to umrah
        $umrah->assignPassport($passport);

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_leader_id' => ['required', 'exists:group_leaders,id'],
            'pilgrim_id' => ['nullable', 'exists:pilgrims,id'],
            'new_pilgrim' => ['required_without:pilgrim_id', 'array'],
            'new_pilgrim.first_name' => ['required_with:new_pilgrim', 'string'],
            'new_pilgrim.first_name_bangla' => ['required_with:new_pilgrim', 'string'],
            'new_pilgrim.last_name' => ['nullable', 'string'],
            'new_pilgrim.last_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.mother_name' => ['nullable', 'string'],
            'new_pilgrim.mother_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.father_name' => ['nullable', 'string'],
            'new_pilgrim.father_name_bangla' => ['nullable', 'string'],
            'new_pilgrim.email' => ['nullable', 'email', 'unique:users,email'],
            'new_pilgrim.phone' => ['nullable', 'string'],
            'new_pilgrim.gender' => ['required_with:new_pilgrim', 'in:male,female,other'],
            'new_pilgrim.is_married' => ['required_with:new_pilgrim', 'boolean'],
            'new_pilgrim.nid' => ['nullable', 'string', 'unique:users,nid'],
            'new_pilgrim.birth_certificate_number' => ['nullable', 'string', 'unique:users,birth_certificate_number'],
            'new_pilgrim.date_of_birth' => ['nullable', 'date'],
            'package_id' => ['required', 'exists:packages,id'],

            // Passport validation
            'passport_id' => ['nullable', 'exists:passports,id'],
            'new_passport' => ['nullable', 'array'],
            'new_passport.passport_number' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->passport_id && $request->has('new_passport') && collect($request->new_passport)->filter()->isNotEmpty();
                }),
                'string',
                'unique:passports,passport_number'
            ],
            'new_passport.issue_date' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->passport_id && $request->has('new_passport') && collect($request->new_passport)->filter()->isNotEmpty();
                }),
                'date'
            ],
            'new_passport.expiry_date' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->passport_id && $request->has('new_passport') && collect($request->new_passport)->filter()->isNotEmpty();
                }),
                'date',
                'after:new_passport.issue_date'
            ],
            'new_passport.passport_type' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->passport_id && $request->has('new_passport') && collect($request->new_passport)->filter()->isNotEmpty();
                }),
                'in:ordinary,official,diplomatic'
            ],
            'new_passport.file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'new_passport.notes' => ['nullable', 'string'],
        ]);

        // Handle Pilgrim, Passport, and Umrah creation in a transaction
        try {
            DB::beginTransaction();

            // Handle Pilgrim
            if ($request->has('pilgrim_id')) {
                $pilgrimId = $validated['pilgrim_id'];
                $pilgrim = Pilgrim::find($pilgrimId);
            } else {
                $user = User::create([
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
            }

            // Handle Passport
            $passport = null;
            if ($request->has('passport_id')) {
                // Use existing passport
                $passport = Passport::find($validated['passport_id']);
            } elseif ($request->has('new_passport') && collect($request->new_passport)->filter()->isNotEmpty()) {
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

            // Create Umrah
            $umrah = Umrah::create([
                'group_leader_id' => $validated['group_leader_id'],
                'pilgrim_id' => $pilgrimId,
                'package_id' => $validated['package_id'],
                'status' => UmrahStatus::Registered,
            ]);

            // Attach passport to Umrah if exists
            if ($passport) $umrah->assignPassport($passport);

            PilgrimLog::add(
                $pilgrim,
                $umrah->id,
                Umrah::class,
                PilgrimLogType::UmrahRegistered,
                "উমরাহ রেজিস্ট্রেশন সম্পন্ন হয়েছে।"
            );

            DB::commit();

            return $this->success("Umrah created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed to create Umrah: " . $e->getMessage());
        }
    }

    public function show(Umrah $umrah): UmrahResource
    {
        $umrah->load(['year', 'groupLeader', 'pilgrim.user', 'package', 'passports']);
        return new UmrahResource($umrah);
    }

    // public function update(Request $request, Umrah $umrah): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'group_leader_id' => ['required', 'exists:group_leaders,id'],
    //         'pilgrim_id' => ['required', 'exists:pilgrims,id'],
    //         'package_id' => ['required', 'exists:packages,id'],
    //     ]);

    //     $umrah->update($validated);

    //     return $this->success("Umrah updated successfully.");
    // }

    public function destroy(Umrah $umrah): JsonResponse
    {
        return $this->error("Umrah deletion is currently under maintenance.");

        // $umrah->delete();
        //delete pilgrim if no other records
        // deltete user if no other records

        // return $this->success("Umrah deleted successfully.");
    }
}
