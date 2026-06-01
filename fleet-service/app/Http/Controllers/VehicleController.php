<?php

namespace App\Http\Controllers;

use App\Enums\VehicleCategory;
use App\Enums\VehicleStatus;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleRepository $vehicles) {}

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'category' => $request->string('category')->toString() ?: null,
        ];

        return Inertia::render('vehicles/Index', [
            'vehicles' => VehicleResource::collection($this->vehicles->paginate($filters)),
            'stats' => $this->vehicles->stats(),
            'filters' => $filters,
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function show(Vehicle $vehicle): Response
    {
        return Inertia::render('vehicles/Show', [
            'vehicle' => new VehicleResource($vehicle),
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $data = $this->withoutImageFields($request->validated());

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeImage($request->file('image'));
        }

        Vehicle::create($data);

        return back()->with('success', 'Vehicle added to the fleet.');
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $this->withoutImageFields($request->validated());

        if ($request->hasFile('image')) {
            $this->deleteImage($vehicle->image_url);
            $data['image_url'] = $this->storeImage($request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($vehicle->image_url);
            $data['image_url'] = null;
        }

        $vehicle->update($data);

        return back()->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'staff'], true), 403);

        $this->deleteImage($vehicle->image_url);
        $vehicle->delete();

        return to_route('vehicles.index')->with('success', 'Vehicle removed from the fleet.');
    }

    /**
     * Drop the upload-control fields so they are never mass-assigned; the photo
     * is persisted as image_url by the upload handlers below.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutImageFields(array $data): array
    {
        unset($data['image'], $data['remove_image']);

        return $data;
    }

    /**
     * Store an uploaded vehicle photo under public/vehicles and return a
     * root-relative URL (resolves against whatever host serves the fleet UI,
     * so it works in dev and behind the gateway without an APP_URL dependency).
     */
    private function storeImage(UploadedFile $file): string
    {
        // Stored under /uploads/* (NOT /vehicles) so the upload directory does
        // not shadow the `/vehicles` page route on the PHP built-in server.
        $dir = public_path('uploads/vehicles');
        File::ensureDirectoryExists($dir);

        $name = 'vehicle-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($dir, $name);

        return '/uploads/vehicles/'.$name;
    }

    /**
     * Remove a previously-uploaded photo. Only deletes files we stored locally
     * (the seeded vehicles use external image URLs, which we leave untouched).
     */
    private function deleteImage(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/uploads/vehicles/')) {
            return;
        }

        $path = public_path(ltrim($url, '/'));

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return array_map(
            fn (VehicleCategory $c) => ['value' => $c->value, 'label' => $c->label()],
            VehicleCategory::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (VehicleStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            VehicleStatus::cases(),
        );
    }
}
