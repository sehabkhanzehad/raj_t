<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Http\Resources\Api\PilgrimResource;
use App\Http\Resources\Api\SectionResource;
use App\Models\GroupLeader;
use App\Models\PreRegistration;
use App\Models\Section;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GroupLeaderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GroupLeaderResource::collection(
            GroupLeader::with([
                'user',
            ])->withCount([
                'activePreRegistrations as active_pre_registrations_count',
                'registrations'
            ])->paginate(perPage())
        );
    }

    public function sections(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::typeGroupLeader()->with('groupLeader.user')->orderBy('name')->get());
    }

    public function preRegistrations(): JsonResponse
    {
        $preRegistrations = PreRegistration::with('pilgrim.user', 'groupLeader')->get()->map(function ($preRegistration) {
            return [
                "type" => "pre-registration",
                "id" => $preRegistration->id,
                "attributes" => [
                    "serialNo" => $preRegistration->serial_no,
                    "bankVoucherNo" => $preRegistration->bank_voucher_no,
                    "date" => $preRegistration->date,
                ],
                "relationships" => [
                    "pilgrim" => new PilgrimResource($preRegistration->relationLoaded('pilgrim') ? $preRegistration->pilgrim : null),
                    "groupLeader" => new GroupLeaderResource($preRegistration->relationLoaded('groupLeader') ? $preRegistration->groupLeader : null),
                ],
            ];
        });

        return response()->json(['data' => $preRegistrations]);
    }

    public function collection(Request $request)
    {
        $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'type' => ['required', 'in:income,expense'],
            "voucher_no" => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);

        $section = Section::find($request->section_id);

        if (!$section->isGroupLeader()) return $this->error("Invalid section for group leader transaction.");

        $groupLeader = $section->groupLeader;

        if ($groupLeader?->pilgrim_required) {
            $request->validate([
                'pre_registration_id' =>  ['required', 'exists:pre_registrations,id']
            ]);
        }

        $transaction = $section->transactions()->create([
            'type' => $request->type,
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description,
            'before_balance' => $section->currentBalance(),
            'amount' => $request->amount,
            'after_balance' => $request->type === 'income'
                ? $section->currentBalance() + $request->amount
                : $section->currentBalance() - $request->amount,
            'date' => $request->date,
        ]);

        if ($request->pre_registration_id) {
            $transaction->references()->create([
                'referenceable_type' => PreRegistration::class,
                'referenceable_id' => $request->pre_registration_id,
            ]);
        }

        return $this->success('Transaction recorded successfully.', 201);
    }
}
