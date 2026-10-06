<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembersAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $query = Member::with('account')->orderBy('sort_order', 'asc');
            return $this->datatable($query);
        }

        return view('member.index');
    }

    public function updateOrder(Request $request)
    {
        $order = $request->input('order'); // array of IDs in new order

        foreach ($order as $index => $id) {
            Member::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }


    public function loadData()
    {
         $data = Member::where('isActive', true)
        ->orderBy('sort_order', 'asc') // ✅ Sort members by sort_order ascending
        ->get();

        return response([
            'data' => $data,
            'status' => 'success'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:members,name',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date',
            'achievements' => 'nullable|string',
            'priority_projects' => 'nullable|string',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'isActive' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            // Handle profile image
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('members', 'public');
                $validated['image_path'] = $imagePath;
            }

            // Ensure isActive is boolean
            $validated['isActive'] = $request->has('isActive') ? true : false;

            // Create member
            $member = Member::create($validated);

            DB::commit();

            return response([
                'data' => $member,
                'message' => 'Member created successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
                'status' => 'store_failed'
            ], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Member::findOrFail($id);
            return response([
                'data' => $data,
                'message' => 'success'
            ], 200);
        }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        //var_dump($member);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:members,name,' . $member->id,
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date',
            'achievements' => 'nullable|string',
            'priority_projects' => 'nullable|string',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'isActive' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            // Handle new profile image
            if ($request->hasFile('image_path')) {
                // Optionally delete old image
                if ($member->image_path && \Storage::disk('public')->exists($member->image_path)) {
                    \Storage::disk('public')->delete($member->image_path);
                }

                $imagePath = $request->file('image_path')->store('members', 'public');
                $validated['image_path'] = $imagePath;
            }

            // Ensure isActive is boolean
            $validated['isActive'] = $request->has('isActive') ? true : false;

            // Update member
            $member->update($validated);

            DB::commit();

            return response([
                'data' => $member,
                'message' => 'Member updated successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
                'status' => 'update_failed'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $member = Member::findOrFail($id);

        // If the member has an image, delete it from storage
        if ($member->image_path && \Storage::disk('public')->exists($member->image_path)) {
            \Storage::disk('public')->delete($member->image_path);
        }

        // Permanently delete the record
        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully.'
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
                $hasAccount = $row->account ? true : false;

                return '<div class="d-flex">
                    <button data-id="' . $row->id . '" class="btn btn-outline-primary btn-sm ms-1 account-button" title="' . ($hasAccount ? 'Edit Account' : 'Add Account') . '">
                        <i class="fas fa-user"></i>
                    </button>
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


    public function account($memberId)
    {
        $member = Member::with('account')->findOrFail($memberId);

        if (!$member->account) {
            return response()->json([
                'message' => 'Account not found for this member.',
            ], 404);
        }

        return response()->json([
            'data' => $member->account,
            'message' => 'success',
        ], 200);
    }

    public function saveAccount(Request $request, $memberId)
    {
        $member = Member::with('account')->findOrFail($memberId);
        $account = $member->account;

        $rules = [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('members_accounts', 'email')->ignore($account?->id),
            ],
            'password' => [
                $account ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'is_active' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            if ($account) {
                $account->email = $validated['email'];

                if (!empty($validated['password'])) {
                    $account->password = Hash::make($validated['password']);
                }

                $account->is_active = $request->has('is_active');
                $account->save();
            } else {
                $account = MembersAccount::create([
                    'member_id' => $member->id,
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'is_active' => $request->has('is_active'),
                ]);
            }

            DB::commit();

            return response()->json([
                'data' => $account,
                'message' => 'Account saved successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'account_save_failed',
            ], 500);
        }
    }


}
