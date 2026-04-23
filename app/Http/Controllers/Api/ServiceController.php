<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCustomAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('schedules');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $services = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|max:3072',
            'is_active'   => 'nullable|boolean',
            'schedules'   => 'nullable|array',
            'schedules.*.day'        => 'required_with:schedules|string',
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end_time'   => 'required_with:schedules|date_format:H:i',
            'schedules.*.capacity'   => 'nullable|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('photo')) {
                $validated['photo_path'] = $request->file('photo')->store('services', 'public');
            }

            $service = Service::create($validated);

            if (!empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $s) {
                    $service->schedules()->create([
                        'day'        => $s['day'],
                        'start_time' => $s['start_time'],
                        'end_time'   => $s['end_time'],
                        'capacity'   => $s['capacity'] ?? 1,
                    ]);
                }
            }

            return response()->json($service->load('schedules'), 201);
        });
    }

    public function show(Service $service)
    {
        return response()->json($service->load('schedules'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|max:3072',
            'is_active'   => 'nullable|boolean',
            'schedules'   => 'nullable|array',
            'schedules.*.day'        => 'required_with:schedules|string',
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end_time'   => 'required_with:schedules|date_format:H:i',
            'schedules.*.capacity'   => 'nullable|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $service, $validated) {
            if ($request->hasFile('photo')) {
                if ($service->photo_path) Storage::disk('public')->delete($service->photo_path);
                $validated['photo_path'] = $request->file('photo')->store('services', 'public');
            }

            $service->update($validated);

            if ($request->has('schedules')) {
                $service->schedules()->delete();
                foreach ($validated['schedules'] as $s) {
                    $service->schedules()->create([
                        'day'        => $s['day'],
                        'start_time' => $s['start_time'],
                        'end_time'   => $s['end_time'],
                        'capacity'   => $s['capacity'] ?? 1,
                    ]);
                }
            }

            return response()->json($service->load('schedules'));
        });
    }

    public function destroy(Service $service)
    {
        if ($service->photo_path) Storage::disk('public')->delete($service->photo_path);
        $service->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }

    // ── Custom Availabilities ─────────────────────────────────────────────

    public function getCustomAvailabilities(Service $service)
    {
        $customs = $service->customAvailabilities()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        return response()->json($customs);
    }

    public function storeCustomAvailability(Request $request, Service $service)
    {
        $validated = $request->validate([
            'date'              => 'required|date',
            'start_time'        => 'required_if:is_available,true|nullable|date_format:H:i',
            'end_time'          => 'required_if:is_available,true|nullable|date_format:H:i',
            'capacity_override' => 'nullable|integer|min:1',
            'is_available'      => 'boolean',
        ]);

        if (!($validated['is_available'] ?? true)) {
            $validated['start_time'] = '00:00:00';
            $validated['end_time']   = '00:00:00';
        }

        $custom = $service->customAvailabilities()->updateOrCreate(
            ['date' => $validated['date']],
            $validated
        );

        return response()->json($custom, 201);
    }

    public function destroyCustomAvailability(Service $service, ServiceCustomAvailability $custom)
    {
        $custom->delete();
        return response()->json(['message' => 'Fecha especial eliminada']);
    }
}
