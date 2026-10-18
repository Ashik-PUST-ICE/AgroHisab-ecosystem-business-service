<?php

namespace App\Services\Business\Visit;

use App\Events\Business\VisitCompleted;
use App\Http\Resources\Business\Outlet\OutletResource;
use App\Models\Business\Beat;
use App\Models\Business\Outlet;
use App\Models\Business\OutletAssignment;
use App\Models\Business\Visit;
use App\Models\Business\VisitCompetitor;
use App\Models\Business\VisitPhoto;
use App\Models\Business\VisitProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Business\Beat\BeatService;

class VisitService
{
    public function __construct(protected BeatService $beatService) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return Visit::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            })
            ->when($filters['user_id'] ?? null, fn($q, $userId) => $q->where('user_id', $userId))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Visit
    {
        return Visit::create($data);
    }

    public function show(Visit $visit): Visit
    {
        return $visit->load('photos', 'competitors', 'products');
    }

    public function update(Visit $visit, array $data): Visit
    {
        $visit->update($data);

        return $visit;
    }

    public function destroy(Visit $visit): void
    {
        $visit->delete();
    }

    public function verifyQr(string $qrToken): array
    {
        $outlet = Outlet::where('qr_token', $qrToken)->firstOrFail();
        $userId = authId();

        $assigned = OutletAssignment::where('outlet_id', $outlet->id)
            ->where('user_id', $userId)
            ->where('status', 1)
            ->exists();

        if (! $assigned) {
            throw new \RuntimeException('This outlet is not assigned to you.', 403);
        }

        $existingPending = Visit::where('outlet_id', $outlet->id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            throw new \RuntimeException('You already have a pending visit for this outlet.', 409);
        }

        return [
            'outlet' => new OutletResource($outlet),
            'can_visit' => true,
        ];
    }

    public function startVisit(array $data): Visit
    {
        $userId = authId();
        $outlet = Outlet::findOrFail($data['outlet_id']);

        return Visit::create([
            'company_id' => $outlet->company_id,
            'outlet_id' => $outlet->id,
            'user_id' => $userId,
            'client_id' => $data['client_id'] ?? null,
            'status' => 'pending',
            'verification_status' => false,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'outlet_latitude' => $outlet->latitude,
            'outlet_longitude' => $outlet->longitude,
            'allowed_radius_meters' => $outlet->geofence_radius,
            'started_at' => now(),
            'sync_status' => $data['sync_status'] ?? 'synced',
            'device_info' => $data['device_info'] ?? null,
        ]);
    }

    public function verifyLocation(Visit $visit, array $data): Visit
    {
        if ($visit->status !== 'pending') {
            throw new \RuntimeException('Visit is not in pending state.', 400);
        }

        $outlet = $visit->outlet;

        $distance = $this->calculateDistance(
            (float) $data['latitude'],
            (float) $data['longitude'],
            (float) $outlet->latitude,
            (float) $outlet->longitude
        );

        $allowedRadius = $outlet->geofence_radius ?? 100;
        $isVerified = $distance <= $allowedRadius;

        $visit->update([
            'verification_status' => $isVerified,
            'distance_meters' => round($distance, 2),
            'allowed_radius_meters' => $allowedRadius,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        if (! $isVerified) {
            throw new \RuntimeException("You are {$distance} meters away. Allowed radius: {$allowedRadius} meters.", 422);
        }

        return $visit;
    }

    public function completeVisit(Visit $visit, array $data, ?array $photos = null, ?array $competitors = null, ?array $products = null): Visit
    {
        if (! $visit->verification_status) {
            throw new \RuntimeException('Location verification is required before completing visit.', 422);
        }

        $visit->update([
            'status' => 'completed',
            'completed_at' => now(),
            'remarks' => $data['remarks'] ?? null,
            'display_condition' => $data['display_condition'] ?? null,
            'display_quantity' => $data['display_quantity'] ?? null,
        ]);

        if ($photos) {
            foreach ($photos as $photo) {
                $path = $photo->store('visit-photos', 'public');
                $visit->photos()->create([
                    'company_id' => $visit->company_id,
                    'path' => $path,
                    'caption' => null,
                ]);
            }
        }

        if ($competitors) {
            foreach ($competitors as $competitorData) {
                $visit->competitors()->create([
                    'company_id' => $visit->company_id,
                    'competitor_id' => $competitorData['competitor_id'] ?? null,
                    'notes' => $competitorData['notes'] ?? null,
                ]);
            }
        }

        if ($products) {
            foreach ($products as $productData) {
                $visit->products()->create([
                    'company_id' => $visit->company_id,
                    'product_id' => $productData['product_id'] ?? null,
                    'quantity' => $productData['quantity'] ?? null,
                    'availability' => $productData['availability'] ?? null,
                    'notes' => $productData['notes'] ?? null,
                ]);
            }
        }

        $beat = Beat::whereDate('date', now()->toDateString())
            ->where('assigned_user_id', $visit->user_id)
            ->first();

        if ($beat) {
            $this->beatService->markOutletVisited($beat->id, $visit->outlet_id, $visit->id);
        }

        event(new VisitCompleted($visit->id, $visit->user_id, $visit->company_id));

        return $visit;
    }

    public function uploadPhoto(Visit $visit, UploadedFile $photo, ?string $caption = null): VisitPhoto
    {
        $path = $photo->store('visit-photos', 'public');

        return $visit->photos()->create([
            'company_id' => $visit->company_id,
            'path' => $path,
            'caption' => $caption,
        ]);
    }

    public function getVisitHistory(?int $userId = null): LengthAwarePaginator
    {
        $userId = $userId ?? authId();

        return Visit::where('user_id', $userId)
            ->with('outlet')
            ->latest()
            ->paginate(15);
    }

    public function addCompetitor(Visit $visit, array $data): VisitCompetitor
    {
        return $visit->competitors()->create([
            'company_id' => $visit->company_id,
            'competitor_id' => $data['competitor_id'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function removeCompetitor(Visit $visit, VisitCompetitor $visitCompetitor): void
    {
        if ($visitCompetitor->visit_id !== $visit->id) {
            return;
        }

        $visitCompetitor->delete();
    }

    public function addProduct(Visit $visit, array $data): VisitProduct
    {
        return $visit->products()->create([
            'company_id' => $visit->company_id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'] ?? null,
            'availability' => $data['availability'] ?? true,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function removeProduct(Visit $visit, VisitProduct $visitProduct): void
    {
        if ($visitProduct->visit_id !== $visit->id) {
            return;
        }

        $visitProduct->delete();
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c * 1000;
    }
}
