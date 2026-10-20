<?php

namespace App\Http\Controllers\Api\V1\Business\Diary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Diary\DiaryRequest;
use App\Http\Resources\Business\Diary\DiaryResource;
use App\Models\Business\BusinessDiary;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Diary\DiaryService;
use Illuminate\Http\Request;

class DiaryController extends Controller
{
    public function __construct(protected DiaryService $diaryService) {}

    /**
     * GET /api/v1/diary
     * Chronological business event timeline.
     * Spec §3.13: "Timeline of business events with date/time and notes"
     * Query: event_type, related_type, related_id, from_date, to_date, search, per_page
     */
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $entries = $this->diaryService->list(
                $request->only(['event_type', 'related_type', 'related_id', 'from_date', 'to_date', 'search', 'per_page'])
            );
            return ApiResponse::success(DiaryResource::collection($entries), 'Diary entries retrieved successfully');
        });
    }

    /**
     * POST /api/v1/diary
     * Record a new business diary event.
     */
    public function store(DiaryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $entry = $this->diaryService->store($request->validated());
            return ApiResponse::success(new DiaryResource($entry), 'Diary entry recorded successfully', 201);
        });
    }

    /**
     * PUT /api/v1/diary/{diary}
     * Update a diary entry.
     */
    public function update(DiaryRequest $request, BusinessDiary $diary)
    {
        return $this->handleRequest(function () use ($request, $diary) {
            $entry = $this->diaryService->update($diary, $request->validated());
            return ApiResponse::success(new DiaryResource($entry), 'Diary entry updated successfully');
        });
    }

    /**
     * DELETE /api/v1/diary/{diary}
     * Soft-delete a diary entry.
     */
    public function destroy(BusinessDiary $diary)
    {
        return $this->handleRequest(function () use ($diary) {
            $this->diaryService->destroy($diary);
            return ApiResponse::success(null, 'Diary entry deleted successfully');
        });
    }
}