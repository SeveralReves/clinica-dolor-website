<?php
// app/Http/Controllers/Api/SpecialistController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialist;
use App\Models\SpecialistCustomAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpecialistController extends Controller
{
    public function index()
    {
        return response()->json(Specialist::with('schedules')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string',
            'email' => 'required|email|unique:specialists,email',
            'phone' => 'nullable|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'schedules' => 'nullable|array' 
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('photo')) {
                $validated['photo_path'] = $request->file('photo')->store('specialists', 'public');
            }

            $specialist = Specialist::create($validated);

            if ($request->has('schedules')) {
                foreach ($request->schedules as $schedule) {
                    $specialist->schedules()->create($schedule);
                }
            }

            return response()->json($specialist->load('schedules'), 201);
        });
    }

    public function show(Specialist $specialist)
    {
        return response()->json($specialist->load('schedules'));
    }

    public function update(Request $request, Specialist $specialist)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:specialists,email,' . $specialist->id,
            'specialty' => 'sometimes|string',
            'phone' => 'nullable|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'schedules' => 'nullable|array'
        ]);

        return DB::transaction(function () use ($request, $specialist, $validated) {
            if ($request->hasFile('photo')) {
                if ($specialist->photo_path) Storage::disk('public')->delete($specialist->photo_path);
                $validated['photo_path'] = $request->file('photo')->store('specialists', 'public');
            }

            $specialist->update($validated);

            if ($request->has('schedules')) {
                $specialist->schedules()->delete(); // Limpiamos y recreamos horarios
                foreach ($request->schedules as $schedule) {
                    $specialist->schedules()->create($schedule);
                }
            }

            return response()->json($specialist->load('schedules'));
        });
    }

    public function destroy(Specialist $specialist)
    {
        if ($specialist->photo_path) Storage::disk('public')->delete($specialist->photo_path);
        $specialist->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }

    // ─── Custom Availabilities ───────────────────────────────────────────────

    /**
     * Lista las disponibilidades custom de un especialista (solo fechas futuras).
     */
    public function getCustomAvailabilities(Specialist $specialist)
    {
        $items = $specialist->customAvailabilities()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        return response()->json($items);
    }

    /**
     * Crea o actualiza la disponibilidad custom para una fecha concreta.
     * Si ya existe un registro para esa fecha, lo reemplaza.
     */
    public function storeCustomAvailability(Request $request, Specialist $specialist)
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'is_available' => 'required|boolean',
            'start_time' => 'nullable|required_if:is_available,true|date_format:H:i',
            'end_time'   => 'nullable|required_if:is_available,true|date_format:H:i|after:start_time',
        ]);

        $custom = $specialist->customAvailabilities()->updateOrCreate(
            ['date' => $validated['date']],
            $validated
        );

        return response()->json($custom, 201);
    }

    /**
     * Elimina una disponibilidad custom.
     */
    public function destroyCustomAvailability(Specialist $specialist, SpecialistCustomAvailability $custom)
    {
        $custom->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}