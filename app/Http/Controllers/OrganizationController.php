<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Organization::where('isActive', true)->get();

        if (request()->ajax()) {
            return $this->datatable($data);
        }

        return view('organization.index');
    }

    public function loadNodes()
    {
        $data = Organization::where('isActive', true)->get();
        
        return response([
            'data' => $data, 
            'status' => 'success'
        ], 200);
    }

    public function loadData()
    {  
        $data = Organization::where('isActive', true)->get();
        return response([
            'data' => $data, 
            'status' => 'success',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organization_node')->where(function ($query) {
                    return $query->where('isActive', true);
                }),
            ],
            'position' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_node,id',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {

            if ($request->hasFile('image_path')) {

                $imagePath = $request->file('image_path')->store('organization', 'public');

                $validated['image_path'] = $imagePath;
            }

            $data = Organization::create($validated);

            DB::commit();

            return response(['data' => $data, 'message' => 'success'], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response(['message' => $e->getMessage(), 'status' => 'store failed'], 500);

        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Organization::where('isActive', true)->findOrFail($id);
        return response(['data' => $data, 'message', 'success'], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $member = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organization_node')->ignore($member->id)->where(function ($query) {
                    return $query->where('isActive', true);
                }),
            ],
            'position' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_node,id',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('organization', 'public');
                $validated['image_path'] = $imagePath;
            }

            $member->update($validated);

            DB::commit();

            return response(['data' => $member, 'message' => 'Organization updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
                'status' => 'update failed'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Organization::findOrFail($id);

        $data->isActive = false;
        $data->save();

        return response([
            'data' => $data,
            'message' => 'Organizatin deleted successfully.'
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
            ->addColumn('parent', function ($row) {
                $parent = Organization::where('id', $row->parent_id)->first();
                if($parent) {
                    return $parent->name;
                }
                return 'No parent';
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
