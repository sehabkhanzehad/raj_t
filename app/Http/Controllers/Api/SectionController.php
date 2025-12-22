<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SectionResource;
use App\Models\Section;
use App\Enums\SectionType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SectionController extends Controller
{

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->get('per_page', 10);
        return SectionResource::collection(Section::typeOther()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "code" => ["required", "string", "unique:sections,code"],
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
        ]);

        Section::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description ?? null,
            'type' => SectionType::Other,
        ]);

        return $this->success("Section created successfully.", 201);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $request->validate([
            "code" => ["required", "string", Rule::unique("sections", "code")->ignore($section)],
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
        ]);

        $section->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description ?? null,
            'type' => SectionType::Other,
        ]);

        return $this->success("Section updated successfully.");
    }

    public function destroy(Section $section): JsonResponse
    {
        try {
            $section->delete();
            return $this->success("Section deleted successfully.");
        } catch (\Exception $e) {
            return $this->error("Failed to delete section. It may be linked to other records.");
        }
    }
}
