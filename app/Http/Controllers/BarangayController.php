<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarangayController extends Controller
{
    public function index(): View|JsonResponse
    {
        $query = Barangay::query()->orderBy('name');

        if (request()->ajax()) {
            return $this->datatable($query);
        }

        return view('barangays.index');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('barangays', 'name')],
        ]);

        $barangay = Barangay::create([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'data' => $barangay,
            'message' => 'Barangay added successfully.',
        ], 201);
    }

    public function edit(string $id): JsonResponse
    {
        $barangay = Barangay::findOrFail($id);

        return response()->json([
            'data' => $barangay,
            'message' => 'Barangay loaded successfully.',
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $barangay = Barangay::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('barangays', 'name')->ignore($barangay->id),
            ],
        ]);

        $barangay->update([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'data' => $barangay,
            'message' => 'Barangay updated successfully.',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $barangay = Barangay::findOrFail($id);
        $barangay->delete();

        return response()->json([
            'message' => 'Barangay deleted successfully.',
        ]);
    }

    protected function datatable($query)
    {
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('officials_count', fn (Barangay $row) => (int) $row->officials_count)
            ->addColumn('actions', function (Barangay $row) {
                $id = (int) $row->id;

                return '<div class="d-flex justify-content-center gap-1">'
                    . '<button type="button" data-id="' . $id . '" class="btn btn-sm btn-outline-primary edit-button" title="Edit barangay" aria-label="Edit barangay">'
                    . '<i class="fas fa-pen-to-square"></i></button>'
                    . '<button type="button" data-id="' . $id . '" data-name="' . e($row->name) . '" class="btn btn-sm btn-outline-danger delete-button" title="Deactivate barangay" aria-label="Deactivate barangay">'
                    . '<i class="fas fa-ban"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
