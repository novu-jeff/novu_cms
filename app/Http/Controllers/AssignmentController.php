<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AssignmentController extends Controller
{
    public function index()
    {
        $data = Assignment::where('isActive', true)->get();

        if (request()->ajax()) {
            return $this->datatable($data);
        }

        return view('assignments.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssignmentRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {

            if ($request->hasFile('image_path')) {

                $imagePath = $request->file('image_path')->store('assignments', 'public');

                $data['image_path'] = $imagePath;
            }

            $assignmentId = DB::table('assignments')->insertGetId([
                'name'       => $data['name'],
                'image_path'       => $data['image_path'],
                'isActive'   => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $memberRows = collect($data['members'])
                ->map(function ($member) use ($assignmentId) {
                    return [
                        'assignment_id' => $assignmentId,
                        'name'       => $member['name'],
                        'position'   => $member['position'] ?? null,
                        'isActive'   => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->toArray();
            
            DB::table('assignment_member')->insert($memberRows);

            DB::commit();

            return response()->json([
                'message' => 'Assignment saved successfully.',
                'assignment_id' => $assignmentId,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to save assignment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        // Fetch assignment with its members
        $assignment = DB::table('assignments')
            ->where('isActive', true)
            ->where('id', $id)
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found.'], 404);
        }

        // Fetch related members
        $members = DB::table('assignment_member')
            ->where('assignment_id', $id)
            ->where('isActive', true)
            ->get();

        return response([
            'data' => [
                'id' => $assignment->id,
                'name' => $assignment->name,
                'image_path' => $assignment->image_path,
                'members' => $members
            ],
            'message' => 'success'
        ], 200);
    }

    public function update(UpdateAssignmentRequest $request, string $id)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // Fetch the assignment
            $assignment = DB::table('assignments')->where('id', $id)->first();

            if (!$assignment) {
                return response()->json([
                    'message' => 'Assignment not found.'
                ], 404);
            }

            // Handle image upload (replace old image if new one is uploaded)
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('assignments', 'public');

                $data['image_path'] = $imagePath;
            } else {
                $data['image_path'] = $assignment->image_path;
            }

            // Update the main assignment record
            DB::table('assignments')->where('id', $id)->update([
                'name'       => $data['name'],
                'image_path' => $data['image_path'],
                'updated_at' => now(),
            ]);

            // Delete all old members
            DB::table('assignment_member')->where('assignment_id', $id)->delete();

            // Insert new members
            $memberRows = collect($data['members'])
                ->map(function ($member) use ($id) {
                    return [
                        'assignment_id' => $id,
                        'name'          => $member['name'],
                        'position'      => $member['position'] ?? null,
                        'isActive'      => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                })->toArray();

            DB::table('assignment_member')->insert($memberRows);

            DB::commit();

            return response()->json([
                'message' => 'Assignment updated successfully.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update assignment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $data = Assignment::findOrFail($id);

        $data->isActive = false;
        $data->save();

        return response([
            'data' => $data,
            'message' => 'Member deactivated successfully.'
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
