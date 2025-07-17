<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BarangayController extends Controller
{
    public function index()
    {
        $data = Barangay::where('isActive', true)->get();

        if (request()->ajax()) {
            return $this->datatable($data);
        }

        return view('barangay.index');
    }

    public function loadData()
    {
        $data = Barangay::where('isActive', true)->get();
        return response(['data' => $data, 'stauts' => 'success'], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barangay_officials,name',
            'position' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('barangays', 'public');
                $validated['image_path'] = $imagePath;
            }

            $data = Barangay::create($validated);

            DB::commit();

            return response(['data' => $data, 'message' => 'Barangay member created successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage(), 'status' => 'store failed'], 500);
        }
    }

    public function edit(string $id)
    {
        $data = Barangay::where('isActive', true)->findOrFail($id);
        return response(['data' => $data, 'message' => 'success'], 200);
    }

    public function update(Request $request, string $id)
    {
        $record = Barangay::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barangay_officials,name,' . $record->id,
            'position' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('barangays', 'public');
                $validated['image_path'] = $imagePath;
            }

            $record->update($validated);

            DB::commit();

            return response(['data' => $record, 'message' => 'Updated successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage(),
                'status' => 'update failed'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $data = Barangay::findOrFail($id);
        $data->isActive = false;
        $data->save();

        return response([
            'data' => $data,
            'message' => 'Deactivated successfully.'
        ], 200);
    }

    public function datatable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image', function ($row) {
                $url = $row->image_path
                    ? asset('storage/' . $row->image_path)
                    : asset('default/profile.png');

                return '<img src="' . $url . '" alt="Image" width="50" height="50" style="object-fit: cover; border-radius: 0.25rem;">';
            })
            ->addColumn('actions', function ($row) {
                return '<div class="d-flex">
                    <button data-id="' . $row->id . '" class="btn btn-outline-warning btn-sm ms-1 edit-button" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button data-id="' . $row->id . '" class="btn btn-outline-danger btn-sm ms-1 delete-button" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['image', 'actions'])
            ->make(true);
    }
}
