<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\LocationRequest;
use App\Models\Business\Location;
use App\Services\Applications\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    /**
     * One endpoint powers every level of the cascading dropdown.
     *
     * GET /locations                          -> all divisions
     * GET /locations?type=upazila&parent_id=12 -> upazilas under district 12
     * GET /locations?search=Dhaka             -> typeahead for the top level
     */
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $type = $request->query('type', 'division');

            // Guard against a bogus type being used to probe the table.
            if (! in_array($type, Location::TYPES, true)) {
                $type = 'division';
            }

            $query = Location::query()->where('type', $type);

            // A parent is only meaningful from the second level downwards.
            if ($type !== 'division') {
                $query->where('parent_id', $request->query('parent_id'));
            }

            if ($search = trim((string) $request->query('search', ''))) {
                $query->where('name', 'like', '%'.$search.'%');
            }

            $locations = $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(500)
                ->get(['id', 'parent_id', 'name', 'name_bn', 'type']);

            return ApiResponse::success($locations, 'Locations retrieved successfully');
        });
    }

    /**
     * Full breadcrumb for a location, used to show the selected path back to
     * the user when they edit an outlet that was created earlier.
     */
    public function show(Request $request, Location $location)
    {
        return $this->handleRequest(function () use ($location) {
            $trail = [];
            $node = $location;

            // Walk up to the root; six levels means the loop is short.
            while ($node) {
                array_unshift($trail, [
                    'id' => $node->id,
                    'name' => $node->name,
                    'type' => $node->type,
                ]);
                $node = $node->parent;
            }

            return ApiResponse::success(
                ['id' => $location->id, 'name' => $location->name, 'type' => $location->type, 'trail' => $trail],
                'Location retrieved successfully'
            );
        });
    }

    /**
     * Adds a missing place, which is how villages get filled in.
     *
     * Villages have no open national dataset, so they are entered here by hand
     * under the ward they belong to.
     */
    public function store(LocationRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $request->validated();

            // Two places with the same name under one parent is a double entry.
            $exists = Location::where('parent_id', $data['parent_id'] ?? null)
                ->where('type', $data['type'])
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'name' => 'That name already exists under this parent.',
                ]);
            }

            $location = Location::create($data);

            return ApiResponse::success($location, 'Location created successfully', 201);
        });
    }

    public function update(LocationRequest $request, Location $location)
    {
        return $this->handleRequest(function () use ($request, $location) {
            $location->update($request->validated());

            return ApiResponse::success($location->refresh(), 'Location updated successfully');
        });
    }

    /**
     * Deletes a place, but refuses while anything still sits under it.
     *
     * Silently cascading would take a whole district's worth of villages with
     * it, so the caller is told to clear the children first.
     */
    public function destroy(Location $location)
    {
        return $this->handleRequest(function () use ($location) {
            $childCount = Location::where('parent_id', $location->id)->count();

            if ($childCount > 0) {
                throw ValidationException::withMessages([
                    'id' => "This {$location->type} still has {$childCount} place(s) under it. Remove them first.",
                ]);
            }

            $location->delete();

            return ApiResponse::success(null, 'Location deleted successfully');
        });
    }

    /**
     * Bulk import, used when a whole ward's villages are typed in one go.
     *
     * Accepts a pasted list, so blank lines and duplicates are dropped before
     * validation rather than failing the whole batch - otherwise one stray
     * empty line in a 30 item paste loses all thirty.
     */
    public function bulkStore(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->merge(['items' => $this->cleanItems($request->input('items', []))]);

            $validated = $request->validate([
                'parent_id' => ['required', 'integer', 'exists:locations,id'],
                'type' => ['required', 'string', 'in:'.implode(',', Location::TYPES)],
                'items' => ['required', 'array', 'min:1', 'max:500'],
                'items.*.name' => ['required', 'string', 'max:120'],
                'items.*.name_bn' => ['nullable', 'string', 'max:120'],
            ]);

            $parent = Location::find($validated['parent_id']);
            $expected = Location::parentTypeFor($validated['type']);

            if ($expected === null || $parent->type !== $expected) {
                throw ValidationException::withMessages([
                    'parent_id' => "These entries must be placed under a {$expected}.",
                ]);
            }

            $created = DB::transaction(function () use ($validated, $parent) {
                // Names already saved under this parent must not be duplicated
                // by a paste that happens to include them again. The names have
                // to become keys, otherwise isset() looks them up by index.
                $existing = Location::where('parent_id', $parent->id)
                    ->where('type', $validated['type'])
                    ->pluck('name')
                    ->mapWithKeys(fn ($n) => [mb_strtolower($n) => true])
                    ->all();

                $rows = [];
                $seen = $existing;

                foreach ($validated['items'] as $item) {
                    $name = trim($item['name']);
                    $key = mb_strtolower($name);

                    // Skip a repeat within this paste, or one already on file.
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;

                    $rows[] = [
                        'parent_id' => $parent->id,
                        'name' => $name,
                        'name_bn' => $item['name_bn'] ?? null,
                        'type' => $validated['type'],
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($rows) {
                    DB::table('locations')->insert($rows);
                }

                return count($rows);
            });

            return ApiResponse::success(['created' => $created], 'Locations created successfully', 201);
        });
    }

    /**
     * Trims names and drops blank rows from a pasted list.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cleanItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $clean = [];

        foreach ($items as $item) {
            $name = is_array($item) ? trim((string) ($item['name'] ?? '')) : '';

            if ($name === '') {
                continue;
            }

            $clean[] = [
                'name' => $name,
                'name_bn' => is_array($item) ? (($item['name_bn'] ?? null) ?: null) : null,
            ];
        }

        return $clean;
    }
}