<?php

namespace App\Http\Controllers\Api;

use App\Enums\PackageType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UmrahPackageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PackageResource::collection(Package::umrah()->paginate(request()->get('per_page', 10)));
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
}
