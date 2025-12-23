<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SectionResource;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeSectionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::typeEmployee()->with('employee.user')->paginate($request->get('per_page', 10)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:sections,code'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'in:male,female,other', 'max:10'],

            'position' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = \App\Models\User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'],
            ]);

            $section = Section::create([
                'code' => $validated['code'],
                'name' => $validated['first_name'] . ' ' . ($validated['last_name'] ?? ''),
                'description' => $validated['description'] ?? null,
                'type' => \App\Enums\SectionType::Employee,
            ]);

            $section->employee()->create([
                'user_id' => $user->id,
                'position' => $validated['position'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
                'status' => $validated['status'] ?? true,
            ]);
        });

        return $this->success("Employee section created successfully.", 201);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('sections', 'code')->ignore($section->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($section->employee->user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'in:male,female,other', 'max:10'],
            'position' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $section) {
            $section->update([
                'code' => $validated['code'],
                'name' => $validated['first_name'] . ' ' . ($validated['last_name'] ?? ''),
                'description' => $validated['description'] ?? null,
            ]);

            $section->employee->user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'],
            ]);

            $section->employee->update([
                'position' => $validated['position'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
                'status' => $validated['status'] ?? true,
            ]);
        });

        return $this->success("Employee section updated successfully.");
    }
}
