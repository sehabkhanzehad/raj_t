<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SectionResource;
use App\Models\GroupLeader;
use App\Models\Pilgrim;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupLeaderSectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SectionResource::collection(Section::typeGroupLeader()->with(['groupLeader.profile'])->paginate(10));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "code" => ["required", "string", "unique:sections,code"],
            "description" => ["nullable", "string"],

            "group_name" => ["nullable", "string", "max:255"],

            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["nullable", "string", "max:255"],
            "mother_name" => ["nullable", "string", "max:255"],
            "father_name" => ["nullable", "string", "max:255"],
            "phone" => ["required", "string", "max:20"],
            "gender" => ["required", "in:male,female,other"],
        ]);

        $section = Section::create([
            "code" => $request->code,
            "name" => $request->first_name . ' ' . ($request->last_name ?? ''),
            "type" => SectionType::GroupLeader,
            "description" => $request->description,
        ]);

        $pilgrim = Pilgrim::create([
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "mother_name" => $request->mother_name,
            "father_name" => $request->father_name,
            "phone" => $request->phone,
            "gender" => $request->gender,
        ]);

        GroupLeader::create([
            "section_id" => $section->id,
            "pilgrim_id" => $pilgrim->id,
            "group_name" => $request->group_name,
        ]);

        return $this->success("Section created successfully.", 201);
    }
}
