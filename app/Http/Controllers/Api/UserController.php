<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar usuarios con su rol y permisos calculados
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }
        
        if ($request->filled('sort') || $request->filled('order')) {
            $order = $request->get('order', 'asc') === 'desc' ? 'desc' : 'asc';
            $sortColumn = $request->get('sort', 'name'); // Si no hay sort, ordenamos por name
            
            $query->orderBy($sortColumn, $order);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        return response()->json([
            'data' => $query->paginate($perPage),
        ]);
    }

    /**
     * Crear un nuevo usuario (Solo Admin/SuperAdmin)
     */
    public function store(Request $request)
    {

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['required', Rule::in('admin', 'assist', 'doctor', 'superadmin')],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => $request->role,
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user'    => $user
        ], 201);
    }

    /**
     * Actualizar datos y rol
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'string|max:255',
            'email'    => ['email', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', Rule::in(['admin', 'assist', 'doctor', 'superadmin'])],
            // 'confirmed' verifica que 'password' coincida con 'password_confirmation'
            'password' => 'nullable|confirmed|min:8', 
        ]);

        // Actualizamos los datos básicos
        $user->fill($request->only(['name', 'email', 'role']));

        // Solo asignamos el password si el usuario escribió algo en el campo
        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user'    => $user
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        // Evitar que un admin se elimine a sí mismo
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}