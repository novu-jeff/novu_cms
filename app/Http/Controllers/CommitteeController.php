<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommittee;
use App\Http\Requests\UpdateCommittee;
use App\Models\Committee;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Committee::where('isActive', true)->get();

        if (request()->ajax()) {
            return $this->datatable($data);
        }

        return view('committee.index');
    }

    public function loadData()
    {
        $data = Committee::where('isActive', true)->get();
        return response(['data' => $data, 'status' => 'success'], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommittee $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $committeeId = DB::table('standing_comittee')->insertGetId([
                'name'       => $data['name'],
                'isActive'   => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $memberRows = collect($data['committee'])
                ->map(function ($member) use ($committeeId) {
                    return [
                        'standing_comittee_id' => $committeeId,
                        'name'       => $member['name'],
                        'position'   => $member['position'] ?? null,
                        'isActive'   => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->toArray();
            DB::table('standing_comittee_member')->insert($memberRows);

            DB::commit();

            return response()->json([
                'message' => 'Standing committee saved successfully.',
                'committee_id' => $committeeId,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to save committee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Get the committee
        $committee = DB::table('standing_comittee')
            ->where('isActive', true)
            ->where('id', $id)
            ->first();

        if (!$committee) {
            return response()->json(['message' => 'Committee not found.'], 404);
        }

        // Get the related members
        $members = DB::table('standing_comittee_member')
            ->where('standing_comittee_id', $id)
            ->where('isActive', true)
            ->select('id', 'name', 'position')
            ->get();

        return response()->json([
            'data' => [
                'id' => $committee->id,
                'name' => $committee->name,
                'members' => $members,
            ],
            'message' => 'success'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommittee $request, string $id)
    {
        DB::beginTransaction();

        try {
            // Update the standing committee name
            DB::table('standing_comittee')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'updated_at' => now(),
                ]);

            // Delete existing members
            DB::table('standing_comittee_member')
                ->where('standing_comittee_id', $id)
                ->delete();

            // Prepare new members for bulk insert
            $members = collect($request->committee)->map(function ($member) use ($id) {
                return [
                    'standing_comittee_id' => $id,
                    'name' => $member['name'],
                    'position' => $member['position'] ?? null,
                    'isActive' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insert new members
            DB::table('standing_comittee_member')->insert($members);

            DB::commit();

            return response()->json(['message' => 'Committee updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Committee::findOrFail($id);

        $data->isActive = false;
        $data->save();

        return response([
            'data' => $data,
            'message' => 'Committee deactivated successfully.'
        ], 200);
    }


    public function datatable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()
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
