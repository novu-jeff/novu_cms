<?php

namespace App\Http\Controllers;

use App\Models\BarangayOfficial;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BarangayOfficialController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Handle DataTable AJAX
        if ($request->ajax()) {
            $query = BarangayOfficial::with('barangay')
            ->orderBy('sort_order', 'asc')
            ->select('barangay_officials.*');

            // Filter by barangay
            if ($request->filled('barangay_id')) {
                $query->where('barangay_id', $request->barangay_id);
            }

            return $this->datatable($query);

        }

        // ✅ Render main view
        $barangays = Barangay::all();
        return view('barangay_officials.index', compact('barangays'));
    }

    public function updateOrder(Request $request)
    {
        $order = $request->input('order'); // array of IDs in new order

        foreach ($order as $index => $id) {
            BarangayOfficial::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'barangay_id' => 'required|exists:barangays,id',
            'image_path' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('barangays', 'public');
        }

        BarangayOfficial::create($validated);

        return response()->json(['message' => 'Barangay official added successfully!']);
    }

    public function edit($id)
    {
        $official = BarangayOfficial::with('barangay')->findOrFail($id);

        return response()->json([
            'data' => $official
        ]);
    }

    public function update(Request $request, $id)
    {
        $official = BarangayOfficial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'barangay_id' => 'required|exists:barangays,id',
            'image_path' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('barangays', 'public');
        }

        $official->update($validated);

        return response()->json(['message' => 'Barangay official updated successfully!']);
    }

    public function reorder(Request $request)
    {
        $order = $request->order;

       // var_dump($order );

        foreach ($order as $item) {
            BarangayOfficial::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Order updated successfully.']);
    }


    public function destroy($id)
    {
        $official = BarangayOfficial::findOrFail($id);
        $official->delete();

        return response()->json(['message' => 'Barangay official deleted successfully!']);
    }

   

    public function loadData(Request $request)
    {
        $query = BarangayOfficial::with('barangay');

        // Optional filter by barangay_id
        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }else{
            $query->where('barangay_id', 1); // default to barangay_id 1 if not provided
        }

        return response()->json([
            'data' => $query->orderBy('sort_order', 'asc')->get()
        ]);
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
