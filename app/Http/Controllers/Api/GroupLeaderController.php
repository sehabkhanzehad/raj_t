<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Models\GroupLeader;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class GroupLeaderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GroupLeaderResource::collection(GroupLeader::with(['user', 'section'])->paginate(perPage()));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:sections,code'],
            'description' => ['nullable', 'string'],
            'group_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:male,female,other'],
            'is_married' => ['required', 'boolean'],
            'nid' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'boolean'],
            'pilgrim_required' => ['required', 'boolean']
        ]);

        DB::transaction(function () use ($request) {
            $section = Section::create([
                'code' => $request->code,
                'name' => $request->group_name,
                'type' => SectionType::GroupLeader,
                'description' => $request->description ?? null,
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name ?? null,
                'mother_name' => $request->mother_name ?? null,
                'father_name' => $request->father_name ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'gender' => $request->gender,
                'is_married' => $request->is_married,
                'nid' =>  $request->nid ?? null,
                'date_of_birth' => $request->date_of_birth ?? null,
            ]);

            GroupLeader::create([
                'user_id' => $user->id,
                'section_id' => $section->id,
                'group_name' => $request->group_name,
                'status' => $request->status,
                'pilgrim_required' => $request->pilgrim_required,
            ]);
        });

        return $this->success('Group Leader created successfully.', 201);
    }

    public function update(Request $request, GroupLeader $groupLeader): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', Rule::unique('sections', 'code')->ignore($groupLeader->section->id)],
            'description' => ['nullable', 'string'],
            'group_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($groupLeader->user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'boolean'],
            'pilgrim_required' => ['required', 'boolean']
        ]);

        DB::transaction(function () use ($request, $groupLeader) {
            $section = $groupLeader->section;

            $section->update([
                'code' => $request->code,
                'name' => $request->group_name,
                'description' => $request->description ?? null,
            ]);

            $user = $groupLeader->user;

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name ?? null,
                'mother_name' => $request->mother_name ?? null,
                'father_name' => $request->father_name ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth ?? null,
            ]);

            $groupLeader->update([
                'group_name' => $request->group_name,
                'status' => $request->status,
                'pilgrim_required' => $request->pilgrim_required,
            ]);
        });

        return $this->success('Group Leader updated successfully.');
    }

    public function destroy(GroupLeader $groupLeader): JsonResponse
    {
        try {
            $groupLeader->delete();

            return $this->success('Group Leader deleted successfully.');
        } catch (\Exception $e) {
            return $this->error('Failed to delete Group Leader: ' . $e->getMessage(), 500);
        }
    }
}
