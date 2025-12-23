<?php

namespace App\Http\Controllers\Api;

use App\Enums\PreRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PreRegistrationResource;
use App\Models\Bank;
use App\Models\GroupLeader;
use App\Models\PreRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PreRegistrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return PreRegistrationResource::collection(PreRegistration::with('pilgrim.user', 'groupLeader', 'bank')
            ->latest()
            ->paginate($request->get('per_page', 10)));
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "group_leader_id" => ["required", "integer", "exists:group_leaders,id"],
            "bank_id" => ["required", "integer", "exists:banks,id"],
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["nullable", "string", "max:255"],
            "mother_name" => ["nullable", "string", "max:255"],
            "father_name" => ["nullable", "string", "max:255"],
            "email" => ["nullable", "string", "email", "max:255", "unique:users,email"],
            "phone" => ["nullable", "string", "max:20"],
            "gender" => ["required", "in:male,female,other"],
            "is_married" => ["required", "boolean"],
            'date_of_birth' => ['nullable', 'date'],
            'nid' => ['required', 'string', 'max:100', 'unique:users,nid'],
            'serial_no' => ['required', 'string', 'max:100'],
            'bank_voucher_no' => ['nullable', 'string', 'max:100'],
            'date' => ['required', 'date'],
        ]);

        $user = User::create([
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

        $pilgrim = $user->pilgrim()->create();

        $pilgrim->preRegistrations()->create([
            'group_leader_id' => $request->group_leader_id,
            'bank_id' => $request->bank_id,
            'serial_no' => $request->serial_no,
            'bank_voucher_no' => $request->bank_voucher_no ?? null,
            'date' => $request->date,
            'status' => PreRegistrationStatus::Active,
        ]);

        return $this->success("Pre-registration created successfully.", 201);
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
            'serial_no' => ['required', 'string', 'max:100'],
            'bank_voucher_no' => ['nullable', 'string', 'max:100'],
            'date' => ['required', 'date'],
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
        ]);

        return $this->success("Pre-registration updated successfully.");
    }

    public function destroy(PreRegistration $preRegistration): JsonResponse
    {
        $preRegistration->delete();
        return $this->success("Pre-registration deleted successfully.");
    }
}
