<?php

namespace App\Http\Controllers\Api;

use App\Enums\PackageType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Models\Umrah;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UmrahPackageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PackageResource::collection(
            Package::umrah()
                ->with('umrahs')
                ->paginate(perPage())
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ]);

        $validated['type'] = PackageType::Umrah;

        Package::create($validated);

        return $this->success('Package created successfully', 201);
    }

    public function update(Request $request, Package $package): JsonResponse
    {
        if (!$package->isUmrah()) return $this->error('Package not found', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ]);

        $package->update($validated);

        return $this->success('Package updated successfully');
    }

    public function destroy(Package $package): JsonResponse
    {
        if (!$package->isUmrah()) return $this->error('Package not found', 404);

        try {
            $package->delete();
            return $this->success('Package deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete package: ' . $e->getMessage(), 500);
        }
    }

    public function show(Package $package): PackageResource
    {
        return new PackageResource($package->load('umrahs'));
    }

    public function pilgrims(Package $package): AnonymousResourceCollection
    {
        $perPage = request()->get('per_page', 10);
        $umrahs = $package->umrahs()->with([
            'groupLeader',
            'pilgrim.user.presentAddress',
            'pilgrim.user.permanentAddress',
            'package',
            'passports'
        ])->latest()->paginate($perPage);

        return \App\Http\Resources\Api\UmrahResource::collection($umrahs);
    }

    public function pilgrimsForCollection(Package $package): AnonymousResourceCollection
    {
        $umrahs = $package->umrahs()
            ->whereHas('groupLeader', fn($q) => $q->where('pilgrim_required', true))
            ->with(['pilgrim.user'])
            ->latest()
            ->get();

        return \App\Http\Resources\Api\UmrahResource::collection($umrahs);
    }

    public function collection(Request $request, Package $package): JsonResponse
    {
        $request->validate([
            'umrah_id' => ['required', 'exists:umrahs,id'],
            'voucher_no' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:400'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ]);

        $umrahPilgrim = $package->umrahs()->where('id', $request->umrah_id)->first();
        $contract = $umrahPilgrim->package->price - $umrahPilgrim->discount;
        $totalPaid = $umrahPilgrim->totalPaid();
        $dueAmount = max($contract - $totalPaid, 0);

        if ($request->amount > $dueAmount) {
            return $this->error('Collection amount exceeds due amount', 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $umrahPilgrim) {
            $groupLeader = $umrahPilgrim->groupLeader;
            $section = $groupLeader->section;

            $transaction = $section->transactions()->create([
                'type' => 'income',
                'voucher_no' => $request->voucher_no,
                'title' => $request->title,
                'description' => $request->description,
                'before_balance' => $section->currentBalance(),
                'amount' => $request->amount,
                'after_balance' => $section->currentBalance() + $request->amount,
                'date' => $request->date,
            ]);

            $transaction->references()->create([
                'referenceable_type' => Umrah::class,
                'referenceable_id' => $umrahPilgrim->id,
            ]);
        });

        return $this->success('Collection recorded successfully', 201);
    }
}
