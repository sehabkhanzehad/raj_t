<?php

namespace App\Http\Controllers\Api\Web\Accounts;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TransactionResource;
use App\Models\GroupLeader;
use App\Models\Package;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UmrahSectionController extends Controller
{
    private Section $section;

    public function __construct()
    {
        $this->section = Section::firstOrCreate(
            ['type' => SectionType::UmrahCost],
            ['name' => 'Umrah Costs', 'code' => 'UMRAH']
        );
    }

    public function index(): AnonymousResourceCollection
    {
        return TransactionResource::collection($this->section->transactions()->latest()->paginate(perPage()));
    }

    public function packages(): JsonResponse
    {
        $packages = Package::umrah()->get(['id', 'name', 'price']);

        return response()->json([
            'data' => $packages->map(function ($package) {
                return [
                    'type' => 'package',
                    'id' => $package->id,
                    'attributes' => [
                        'name' => $package->name,
                        'price' => $package->price,
                    ],
                ];
            }),
        ]);
    }

    public function groupLeaders(): JsonResponse
    {
        $groupLeaders = GroupLeader::with('user')->get()->map(function ($groupLeader) {
            return [
                "type" => "group-leader",
                "id" => $groupLeader->id,
                "attributes" => [
                    "groupName" => $groupLeader->group_name,
                ],
                "relationships" => [
                    "user" => [
                        'type' => 'user',
                        'id' => $groupLeader->user->id,
                        'attributes' => [
                            'name' => $groupLeader->user->name,
                            'avatar' => $groupLeader->user->avatar,
                            'phone' => $groupLeader->user->phone,
                        ],
                    ],
                ],
            ];
        });

        return response()->json(['data' => $groupLeaders]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:income,expense'],
            "voucher_no" => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
            'package_id' => ['required', 'exists:packages,id'],
            'group_leader_id' => ['nullable', 'exists:group_leaders,id'],
        ]);

        // Todo: Add validation the group leder belongs to the selected package.

        $umrahCostSection = $this->section;

        $transaction = $umrahCostSection->transactions()->create([
            'type' => $request->type,
            'voucher_no' => $request->voucher_no,
            'title' => $request->title,
            'description' => $request->description ?? null,
            'before_balance' => $umrahCostSection->currentBalance(),
            'after_balance' => $request->type === 'expense'
                ? $umrahCostSection->currentBalance() + $request->amount
                : $umrahCostSection->currentBalance() - $request->amount,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);

        $transaction->references()->create([
            'referenceable_type' => Package::class,
            'referenceable_id' => $request->package_id,
        ]);

        if ($request->group_leader_id) {
            $transaction->references()->create([
                'referenceable_type' => GroupLeader::class,
                'referenceable_id' => $request->group_leader_id,
            ]);
        }

        return $this->success('Umrah expense transaction recorded successfully.', 201);
    }
}
