<?php

namespace App\Http\Controllers\Api\Web\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\YearResource;
use App\Models\Year;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class YearController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return YearResource::collection(Year::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', uniqueInAgency('years', 'name')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        Year::create($validated);

        return $this->success("Year created successfully.", 201);
    }

    public function update(Request $request, Year $year): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', uniqueInAgency('years', 'name', $year->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $year->update($validated);

        return $this->success("Year updated successfully.");
    }
    // Todo: add year updateDefault method to change default year
    //? Note: when a year is default, all other years should be default false
    public function updateDefault(Year $year): JsonResponse
    {
        // Set all years to default false
        Year::update(['default' => false]);

        // Set the selected year to default true
        $year->update(['default' => true]);

        return $this->success("Default year updated successfully.");
    }


}
