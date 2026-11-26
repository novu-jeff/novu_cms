<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommittee;
use App\Http\Requests\UpdateCommittee;
use App\Models\Committee;
use App\Models\Member;
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
        // Get all active committees with their members
        $data = DB::table('standing_comittee as sc')
                ->where('sc.isActive', true)
                ->leftJoin('standing_comittee_member as scm', 'sc.id', '=', 'scm.standing_comittee_id')
                ->leftJoin('members as m', 'm.id', '=', 'scm.member_id')
                ->select(
                    'sc.id as committee_id',
                    'sc.name as committee_name',
                    'sc.isActive as committee_active',
                    'scm.id as assignment_id',
                    'scm.member_id',
                    DB::raw('COALESCE(m.name, scm.name) as member_name'),
                    'scm.position as member_position',
                    'scm.isActive as member_active'
                )
            ->get()
            ->groupBy('committee_id')
            ->map(function ($group) {
                $committee = $group->first();
                return [
                    'id' => $committee->committee_id,
                    'name' => $committee->committee_name,
                    'isActive' => $committee->committee_active,
                    'members' => $group->map(function ($member) {
                        return [
                            'id' => $member->assignment_id,
                            'member_id' => $member->member_id,
                            'name' => $member->member_name,
                            'position' => $member->member_position,
                            'isActive' => $member->member_active,
                        ];
                    })->filter(fn ($m) => $m['id'] !== null)->values()
                ];
            })->values();

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
                    $sbMember = DB::table('members')
                        ->where('id', $member['member_id'])
                        ->first();

                    return [
                        'standing_comittee_id' => $committeeId,
                        'member_id' => $member['member_id'],
                        'name' => $sbMember->name,
                        'position' => $member['position'],
                        'isActive' => true,
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
       $members = DB::table('standing_comittee_member as scm')
        ->leftJoin('members as m', 'm.id', '=', 'scm.member_id')
        ->where('scm.standing_comittee_id', $id)
        ->where('scm.isActive', true)
        ->select(
            'scm.id',
            'scm.member_id',
            DB::raw('COALESCE(m.name, scm.name) as name'),
            'scm.position'
        )
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
        $data = $request->validated();

        DB::beginTransaction();

        try {
            DB::table('standing_comittee')
                ->where('id', $id)
                ->update([
                    'name' => $data['name'],
                    'updated_at' => now(),
                ]);

            $existingAssignments = DB::table('standing_comittee_member')
                ->where('standing_comittee_id', $id)
                ->where('isActive', true)
                ->whereNotNull('member_id')
                ->get()
                ->keyBy('member_id');

            $incomingMembers = collect($data['committee'])
                ->keyBy('member_id');

            // Deactivate assignments removed from the committee.
            foreach ($existingAssignments as $memberId => $assignment) {
                if (!$incomingMembers->has($memberId)) {
                    DB::table('standing_comittee_member')
                        ->where('id', $assignment->id)
                        ->update([
                            'isActive' => false,
                            'term_end' => now()->toDateString(),
                            'updated_at' => now(),
                        ]);
                }
            }

            // Update existing assignments or create new ones.
            foreach ($incomingMembers as $memberId => $memberData) {
                if ($existingAssignments->has($memberId)) {
                    DB::table('standing_comittee_member')
                        ->where('id', $existingAssignments[$memberId]->id)
                        ->update([
                            'position' => $memberData['position'],
                            'updated_at' => now(),
                        ]);

                    continue;
                }

                $sbMember = DB::table('members')
                    ->where('id', $memberId)
                    ->first();

                DB::table('standing_comittee_member')->insert([
                    'standing_comittee_id' => $id,
                    'member_id' => $memberId,
                    'name' => $sbMember->name,
                    'position' => $memberData['position'],
                    'isActive' => true,
                    'term_start' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Committee updated successfully.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed.',
                'error' => $e->getMessage(),
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
