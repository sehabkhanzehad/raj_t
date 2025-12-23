<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SectionResource;
use App\Models\GroupLeader;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class GroupLeaderSectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::typeGroupLeader()->with(['groupLeader.user'])->paginate(10));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "code" => ["required", "string", "unique:sections,code"],
            "description" => ["nullable", "string"],
            "group_name" => ["required", "string", "max:255"],
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["nullable", "string", "max:255"],
            "mother_name" => ["nullable", "string", "max:255"],
            "father_name" => ["nullable", "string", "max:255"],
            "email" => ["nullable", "string", "email", "max:255", "unique:users,email"],
            "phone" => ["nullable", "string", "max:20"],
            "gender" => ["required", "in:male,female,other"],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'boolean']
        ]);

        DB::transaction(function () use ($request) {
            $section = Section::create([
                "code" => $request->code,
                "name" => $request->group_name,
                "type" => SectionType::GroupLeader,
                "description" => $request->description,
            ]);

            $user = User::create([
                "first_name" => $request->first_name,
                "last_name" => $request->last_name ?? null,
                "mother_name" => $request->mother_name ?? null,
                "father_name" => $request->father_name ?? null,
                "email" => $request->email ?? null,
                "phone" => $request->phone ?? null,
                "gender" => $request->gender,
                "date_of_birth" => $request->date_of_birth ?? null,
            ]);

            GroupLeader::create([
                "section_id" => $section->id,
                "user_id" => $user->id,
                "group_name" => $request->group_name,
                'status' => $request->status,
            ]);
        });

        return $this->success("Section created successfully.", 201);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $request->validate([
            "code" => ["required", "string", Rule::unique('sections', 'code')->ignore($section->id)],
            "description" => ["nullable", "string"],
            "group_name" => ["required", "string", "max:255"],

            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["nullable", "string", "max:255"],
            "mother_name" => ["nullable", "string", "max:255"],
            "father_name" => ["nullable", "string", "max:255"],
            "email" => ["nullable", "string", "email", "max:255", Rule::unique('users', 'email')->ignore($section->groupLeader->user->id)],
            "phone" => ["nullable", "string", "max:20"],
            "gender" => ["required", "in:male,female,other"],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'boolean']
        ]);

        DB::transaction(function () use ($request, $section) {
            $section->update([
                "code" => $request->code,
                "name" => $request->group_name,
                "description" => $request->description,
            ]);

            $user = $section->groupLeader->user;

            $user->update([
                "first_name" => $request->first_name,
                "last_name" => $request->last_name ?? null,
                "mother_name" => $request->mother_name ?? null,
                "father_name" => $request->father_name ?? null,
                "email" => $request->email ?? null,
                "phone" => $request->phone ?? null,
                "gender" => $request->gender,
                "date_of_birth" => $request->date_of_birth ?? null,
            ]);

            $section->groupLeader->update([
                "group_name" => $request->group_name,
                'status' => $request->status,
            ]);
        });

        return $this->success("Section updated successfully.");
    }
}
