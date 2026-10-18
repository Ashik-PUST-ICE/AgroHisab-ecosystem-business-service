<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Visit\VisitCompetitorResource;
use App\Http\Resources\Business\Visit\VisitProductResource;
use App\Http\Resources\Business\Visit\VisitResource;
use App\Http\Requests\Business\Visit\CompleteVisitRequest;
use App\Http\Requests\Business\Visit\StartVisitRequest;
use App\Http\Requests\Business\Visit\VerifyLocationRequest;
use App\Http\Requests\Business\Visit\VisitRequest;
use App\Http\Requests\Business\Outlet\VerifyQrRequest;
use App\Models\Business\Visit;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Visit\VisitService;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct(protected VisitService $visitService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $visits = $this->visitService->index($request->only(['search', 'user_id', 'per_page']));

            return ApiResponse::success($visits, 'Visits retrieved successfully');
        });
    }

    public function store(VisitRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $visit = $this->visitService->store($request->validated());

            return ApiResponse::success(new VisitResource($visit), 'Visit created successfully', 201);
        });
    }

    public function show(Visit $visit)
    {
        return $this->handleRequest(function () use ($visit) {
            $visit = $this->visitService->show($visit);

            return ApiResponse::success(new VisitResource($visit), 'Visit retrieved successfully');
        });
    }

    public function update(VisitRequest $request, Visit $visit)
    {
        return $this->handleRequest(function () use ($request, $visit) {
            $visit = $this->visitService->update($visit, $request->validated());

            return ApiResponse::success(new VisitResource($visit), 'Visit updated successfully');
        });
    }

    public function destroy(Visit $visit)
    {
        return $this->handleRequest(function () use ($visit) {
            $this->visitService->destroy($visit);

            return ApiResponse::success(null, 'Visit deleted successfully');
        });
    }

    public function uploadPhoto(Request $request, Visit $visit)
    {
        return $this->handleRequest(function () use ($request, $visit) {
            $request->validate([
                'photo' => ['required', 'image', 'max:2048'],
                'caption' => ['nullable', 'string', 'max:255'],
            ]);

            $photo = $this->visitService->uploadPhoto($visit, $request->file('photo'), $request->caption);

            return ApiResponse::success($photo, 'Photo uploaded successfully', 201);
        });
    }

    public function photos(Visit $visit)
    {
        return $this->handleRequest(function () use ($visit) {
            $photos = $visit->photos()->get();

            return ApiResponse::success($photos, 'Visit photos retrieved successfully');
        });
    }

    public function competitors(Visit $visit)
    {
        return $this->handleRequest(function () use ($visit) {
            return ApiResponse::success($this->visitService->show($visit)->competitors, 'Visit competitors retrieved successfully');
        });
    }

    public function addCompetitor(Request $request, Visit $visit)
    {
        return $this->handleRequest(function () use ($request, $visit) {
            $request->validate([
                'competitor_id' => ['required', 'exists:competitors,id'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $competitor = $this->visitService->addCompetitor($visit, $request->only(['competitor_id', 'notes']));

            return ApiResponse::success(new VisitCompetitorResource($competitor), 'Competitor added to visit successfully', 201);
        });
    }

    public function removeCompetitor(Visit $visit, \App\Models\Business\VisitCompetitor $visitCompetitor)
    {
        return $this->handleRequest(function () use ($visit, $visitCompetitor) {
            $this->visitService->removeCompetitor($visit, $visitCompetitor);

            return ApiResponse::success(null, 'Competitor removed from visit successfully');
        });
    }

    public function products(Visit $visit)
    {
        return $this->handleRequest(function () use ($visit) {
            return ApiResponse::success($this->visitService->show($visit)->products, 'Visit products retrieved successfully');
        });
    }

    public function addProduct(Request $request, Visit $visit)
    {
        return $this->handleRequest(function () use ($request, $visit) {
            $request->validate([
                'product_id' => ['required', 'exists:products,id'],
                'quantity' => ['nullable', 'integer', 'min:0'],
                'availability' => ['nullable', 'boolean'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $product = $this->visitService->addProduct($visit, $request->only(['product_id', 'quantity', 'availability', 'notes']));

            return ApiResponse::success(new VisitProductResource($product), 'Product added to visit successfully', 201);
        });
    }

    public function removeProduct(Visit $visit, \App\Models\Business\VisitProduct $visitProduct)
    {
        return $this->handleRequest(function () use ($visit, $visitProduct) {
            $this->visitService->removeProduct($visit, $visitProduct);

            return ApiResponse::success(null, 'Product removed from visit successfully');
        });
    }

    public function verifyQr(VerifyQrRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $result = $this->visitService->verifyQr($request->qr_token);

            return ApiResponse::success($result, 'QR verified successfully');
        });
    }

    public function startVisit(StartVisitRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $visit = $this->visitService->startVisit($request->validated());

            return ApiResponse::success(new VisitResource($visit), 'Visit started successfully', 201);
        });
    }

    public function verifyLocation(VerifyLocationRequest $request, Visit $visit)
    {
        return $this->handleRequest(function () use ($request, $visit) {
            $visit = $this->visitService->verifyLocation($visit, $request->validated());

            return ApiResponse::success(new VisitResource($visit), 'Location verified successfully');
        });
    }

    public function completeVisit(CompleteVisitRequest $request, Visit $visit)
    {
        return $this->handleRequest(function () use ($request, $visit) {
            $visit = $this->visitService->completeVisit(
                $visit,
                $request->validated(),
                $request->file('photos'),
                $request->input('competitors'),
                $request->input('products')
            );

            return ApiResponse::success(new VisitResource($visit), 'Visit completed successfully');
        });
    }

    public function history(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $userId = $request->query('user_id');
            $visits = $this->visitService->getVisitHistory($userId);

            return ApiResponse::success($visits, 'Visit history retrieved successfully');
        });
    }

    public function sync(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'visits' => ['required', 'array'],
                'visits.*.client_id' => ['required', 'string'],
                'visits.*.outlet_id' => ['required', 'exists:outlets,id'],
                'visits.*.latitude' => ['required', 'string'],
                'visits.*.longitude' => ['required', 'string'],
                'visits.*.sync_status' => ['nullable', 'in:synced,pending,failed'],
            ]);

            $userId = authId();
            $companyId = request()->attributes->get('jwt_claims')['company_id'] ?? null;
            $syncedVisits = [];

            foreach ($request->visits as $visitData) {
                $existingVisit = Visit::where('client_id', $visitData['client_id'])->first();

                if ($existingVisit) {
                    $existingVisit->update([
                        'sync_status' => 'synced',
                        'device_info' => $visitData['device_info'] ?? null,
                    ]);
                    $syncedVisits[] = $existingVisit;
                } else {
                    $visit = Visit::create([
                        'company_id' => $companyId,
                        'outlet_id' => $visitData['outlet_id'],
                        'user_id' => $userId,
                        'client_id' => $visitData['client_id'],
                        'status' => $visitData['status'] ?? 'pending',
                        'verification_status' => $visitData['verification_status'] ?? false,
                        'latitude' => $visitData['latitude'],
                        'longitude' => $visitData['longitude'],
                        'outlet_latitude' => $visitData['outlet_latitude'] ?? null,
                        'outlet_longitude' => $visitData['outlet_longitude'] ?? null,
                        'distance_meters' => $visitData['distance_meters'] ?? null,
                        'allowed_radius_meters' => $visitData['allowed_radius_meters'] ?? null,
                        'sync_status' => 'synced',
                        'device_info' => $visitData['device_info'] ?? null,
                        'started_at' => $visitData['started_at'] ?? now(),
                        'completed_at' => $visitData['completed_at'] ?? null,
                    ]);
                    $syncedVisits[] = $visit;
                }
            }

            return ApiResponse::success($syncedVisits, 'Offline visits synced successfully');
        });
    }
}
