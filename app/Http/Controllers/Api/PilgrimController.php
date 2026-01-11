<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PreRegistrationResource;
use App\Http\Resources\Api\RegistrationResource;
use App\Http\Resources\Api\UmrahResource;
use App\Models\PreRegistration;
use App\Models\Registration;
use App\Models\Umrah;
use Illuminate\Http\JsonResponse;

class PilgrimController extends Controller
{
    public function show(string $type, int $id): JsonResponse
    {
        return match ($type) {
            'pre-registration' => $this->getPreRegistration($id),
            'umrah' => $this->getUmrah($id),
            'registration' => $this->getRegistration($id),
            default => response()->json(['error' => 'Invalid pilgrim type'], 404)
        };
    }

    private function getPreRegistration(int $id): JsonResponse
    {
        $preReg = PreRegistration::with([
            'pilgrim.user.presentAddress',
            'pilgrim.user.permanentAddress',
            'groupLeader',
            'passports',
        ])->findOrFail($id);

        return response()->json([
            'pilgrim_type' => 'pre-registration',
            'data' => new PreRegistrationResource($preReg),
            'meta' => [
                'can_register' => true,
                'can_umrah' => false,
                'next_steps' => ['registration']
            ]
        ]);
    }

    private function getUmrah(int $id): JsonResponse
    {
        $umrah = Umrah::with([
            'pilgrim.user.presentAddress',
            'pilgrim.user.permanentAddress',
            'groupLeader',
            'package',
            'passports'
        ])->findOrFail($id);

        return response()->json([
            'pilgrim_type' => 'umrah',
            'data' => new UmrahResource($umrah),
            'meta' => [
                'can_register' => false,
                'can_umrah' => true,
                'next_steps' => []
            ]
        ]);
    }

    private function getRegistration(int $id): JsonResponse
    {
        $registration = Registration::with([
            'pilgrim.user.presentAddress',
            'pilgrim.user.permanentAddress',
            'preRegistration.groupLeader',
            'package',
            'bank'
        ])->findOrFail($id);

        return response()->json([
            'pilgrim_type' => 'registration',
            'data' => new RegistrationResource($registration),
            'meta' => [
                'can_register' => false,
                'can_umrah' => false,
                'next_steps' => []
            ]
        ]);
    }
}
