<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Public branding/configuration consumed by every other microservice.
     *
     * GET /api/settings
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => Setting::publicPayload(),
        ]);
    }
}
