<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
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
