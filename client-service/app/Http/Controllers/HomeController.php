<?php

namespace App\Http\Controllers;

use App\Services\VehicleGateway;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(VehicleGateway $vehicles): Response
    {
        return Inertia::render('Welcome', [
            'featured' => collect($vehicles->available())->take(6)->values()->all(),
            'fleetCount' => count($vehicles->all()),
        ]);
    }
}
