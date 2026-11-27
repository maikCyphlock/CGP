<?php

namespace App\Http\Controllers\Oac;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Directorio de funcionarios responsables (área/dirección, cargo, figura y funcionario)
 * usado en la pestaña de Valoración y Asignación de expedientes.
 */
class FuncionarioController extends Controller
{
    public function index(Request $request)
    {
        $editarId = $request->query('editar');

        return view('oac.funcionarios', [
            'funcionarios' => Funcionario::orderByDesc('activo')
                ->orderBy('area_direccion')
                ->orderBy('nombre_completo')
                ->get(),
            'fila' => $editarId ? Funcionario::find((int) $editarId) : null,
            'unidades' => DB::table('referral_unit')->where('active', true)->orderBy('name')->get(),
            'cargos' => DB::table('job_position')->where('active', true)->orderBy('title')->get(),
            'usuarios' => DB::table('staff_user')->where('active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request);
        Funcionario::create($d + ['activo' => true]);

        return redirect()
            ->route('oac.funcionarios.index')
            ->with('ok', 'Funcionario agregado al directorio.');
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $d = $this->validar($request);
        $funcionario->update($d + ['activo' => $request->boolean('activo')]);

        return redirect()
            ->route('oac.funcionarios.index')
            ->with('ok', 'Datos del funcionario actualizados.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'area_direccion' => 'required|string|max:180',
            'cargo' => 'required|string|max:150',
            'figura' => 'required|string|max:150',
            'nombre_completo' => 'required|string|min:3|max:160',
            'cedula' => 'nullable|string|max:20',
            'referral_unit_id' => 'nullable|integer|exists:referral_unit,id',
            'job_position_id' => 'nullable|integer|exists:job_position,id',
            'staff_user_id' => 'nullable|uuid|exists:staff_user,id',
        ], [
            'area_direccion.required' => 'Indique el área o dirección.',
            'cargo.required' => 'Indique el cargo del funcionario.',
            'figura.required' => 'Indique la figura procedimental.',
            'nombre_completo.required' => 'Indique el nombre completo del funcionario.',
            'nombre_completo.min' => 'El nombre del funcionario debe tener al menos 3 caracteres.',
        ]);
    }
}
