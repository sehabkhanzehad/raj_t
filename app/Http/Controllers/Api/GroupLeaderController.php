<?php

namespace App\Http\Controllers\Api;

use App\Enums\PilgrimLogType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Http\Resources\Api\PilgrimResource;
use App\Http\Resources\Api\SectionResource;
use App\Http\Resources\PackageResource;
use App\Models\GroupLeader;
use App\Models\Package;
use App\Models\PilgrimLog;
use App\Models\PreRegistration;
use App\Models\Section;
use App\Models\Umrah;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GroupLeaderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = GroupLeader::with([
            'user',
            'section'
        ])->withCount([
            'activePreRegistrations as active_pre_registrations_count',
            'registrations'
        ]);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('group_name', 'like', "%$search%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', "%$search%")
                            ->orWhere('full_name_bangla', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%");
                    })
                    ->orWhereHas('section', function ($sectionQuery) use ($search) {
                        $sectionQuery->where('name', 'like', "%$search%");
                    });
            });
        }

        return GroupLeaderResource::collection($query->paginate(perPage()));
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

    public function umrahPackages(): JsonResponse
    {
        $umrahs = Package::umrah()->with('umrahs.pilgrim.user')->get()->map(function ($package) {
            return [
                "type" => "package",
                "id" => $package->id,
                "attributes" => [
                    "name" => $package->name,
                    "startDate" => $package->start_date,
                    "endDate" => $package->end_date,
                    "price" => $package->price,
                ],
                "relationships" => [
                    "umrahs" => $package->umrahs->map(function ($umrah) {
                        return [
                            'type' => 'umrah',
                            'id' => $umrah->id,
                            'attributes' => [
                                'status' => $umrah->status,
                            ],
                            'relationships' => [
                                'groupLeader' => [
                                    'type' => 'group-leader',
                                    'id' => $umrah->groupLeader->id,
                                    'attributes' => [],
                                ],
                                'pilgrim' => [
                                    'type' => 'pilgrim',
                                    'id' => $umrah->pilgrim->id,
                                    'attributes' => [
                                        'createdAt' => $umrah->pilgrim->created_at,
                                    ],
                                    'relationships' => [
                                        'user' => [
                                            'type' => 'user',
                                            'id' => $umrah->pilgrim->user->id,
                                            'attributes' => [
                                                'name' => $umrah->pilgrim->user->name,
                                                'avatar' => $umrah->pilgrim->user->avatar,
                                                'phone' => $umrah->pilgrim->user->phone,
                                            ],
                                        ]
                                    ]
                                ],
                            ],
                        ];
                    }),
                ],
            ];
        });

        return response()->json(['data' => $umrahs]);
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
            'pre_registration_id' => ['nullable', 'exists:pre_registrations,id'],
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

            $preRegistration = PreRegistration::find($request->pre_registration_id);

            PilgrimLog::add(
                $preRegistration->pilgrim,
                $preRegistration->id,
                PreRegistration::class,
                PilgrimLogType::HajjCollection,
                "Hajj collection has been recorded."
            );
        }

        return $this->success('Transaction recorded successfully.', 201);
    }

    public function umrahCollection(Request $request)
    {
        $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'type' => ['required', 'in:income,expense'],
            "voucher_no" => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'umrah_id' => ['nullable', 'integer', 'exists:umrahs,id'],
        ]);

        $section = Section::find($request->section_id);

        if (!$section->isGroupLeader()) return $this->error("Invalid section for group leader transaction.");

        $groupLeader = $section->groupLeader;

        if ($groupLeader?->pilgrim_required) {
            $request->validate([
                'umrah_id' =>  ['required', 'exists:umrahs,id']
            ]);
        }

        if ($request->umrah_id) {
            $umrah = Umrah::find($request->umrah_id);
            if ($umrah->groupLeader->id !== $groupLeader->id) {
                return $this->error("The selected umrah does not belong to the group leader's pilgrim.");
            }
        }

        if ($request->umrah_id && $umrah->package->id !==  (int) $request->package_id) {
            return $this->error("The selected umrah does not belong to the specified package.");
        }

        if ($groupLeader->pilgrim_required) {
            $contract = $umrah->package->price - $umrah->discount;
            $totalPaid = $umrah->totalPaid();
            $dueAmount = max($contract - $totalPaid, 0);
            if ($request->type === 'income' && $request->amount > $dueAmount) {
                return $this->error("Collection amount exceeds due amount of {$dueAmount}.", 422);
            }

            if ($request->type === 'expense' && $request->amount > $totalPaid) {
                return $this->error("Refund amount exceeds total paid amount of {$totalPaid}.", 422);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $section) {
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

            $transaction->references()->create([
                'referenceable_type' => Package::class,
                'referenceable_id' => $request->package_id,
            ]);

            if ($request->umrah_id) {
                $transaction->references()->create([
                    'referenceable_type' => Umrah::class,
                    'referenceable_id' => $request->umrah_id,
                ]);

                $umrah = Umrah::find($request->umrah_id);

                PilgrimLog::add(
                    $umrah->pilgrim,
                    $umrah->id,
                    Umrah::class,
                    PilgrimLogType::UmrahCollection,
                    "Umrah collection has been recorded."
                );
            }
        });

        return $this->success('Transaction recorded successfully.', 201);
    }
}
