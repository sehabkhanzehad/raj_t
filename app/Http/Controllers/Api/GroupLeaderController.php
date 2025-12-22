<?php

namespace App\Http\Controllers\Api;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GroupLeaderResource;
use App\Models\GroupLeader;
use App\Models\Pilgrim;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupLeaderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GroupLeaderResource::collection(GroupLeader::with(['section', 'pilgrim'])->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            "code" => ["required", "string", "unique:sections,code"],
            "name" => ["required", "string", "max:255"],
            "group_name" => ["nullable", "string", "max:255"],
            "phone" => ["required", "string", "max:20"],
            "gender" => ["required", "in:male,female"],
        ]);

        $pilgrim = Pilgrim::create([
            "name" => $request->name,
            "phone" => $request->phone,
            "gender" => $request->gender,
        ]);

        $section = Section::create([
            "code" => $request->code,
            "name" => $request->name,
            "type" => SectionType::GroupLeader,
            "description" => $request->description,
        ]);

        GroupLeader::create([
            "section_id" => $section->id,
            "pilgrim_id" => $pilgrim->id,
            "group_name" => $request->group_name,
        ]);

        return $this->success("Group Leader created successfully.", 201);
    }

    public function update(Request $request, $groupLeader)
    {
        // Placeholder for updating group leader related data
        return response()->json([
            'message' => 'Update Group Leader endpoint is under construction.'
        ]);
    }

    public function destroy(Request $request, $groupLeader)
    {
        // Placeholder for deleting group leader related data
        return response()->json([
            'message' => 'Delete Group Leader endpoint is under construction.'
        ]);
    }
}
