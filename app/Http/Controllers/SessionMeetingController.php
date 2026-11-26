<?php

namespace App\Http\Controllers;

use App\Models\SessionAttendance;
use App\Models\SessionMeeting;
use App\Models\CommitteeReportAgendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\MembersAccount;

class SessionMeetingController extends Controller
{
    /** Display all sessions and their related record counts. */
    public function index()
    {
        $sessions = SessionMeeting::withCount('documents')
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->get();

        $committeeReportCounts = CommitteeReportAgendaItem::query()
            ->selectRaw('session_id, COUNT(*) as aggregate')
            ->groupBy('session_id')
            ->pluck('aggregate', 'session_id');

        $attendanceCounts = SessionAttendance::query()
            ->selectRaw('session_id, COUNT(*) as aggregate')
            ->whereNotNull('member_account_id')
            ->whereIn('attendance_status', ['present', 'late'])
            ->groupBy('session_id')
            ->pluck('aggregate', 'session_id');

        $sessions->each(function ($session) use (
            $attendanceCounts,
            $committeeReportCounts
        ) {
            $session->attendance_count = (int) (
                $attendanceCounts[$session->id] ?? 0
            );

            $session->agenda_count = (int) $session->documents_count
                + (int) ($committeeReportCounts[$session->id] ?? 0);
        });

        return view('secretary.session-meetings.index', compact('sessions'));
    }

    /** The create form is handled by the modal on the index page. */
    public function create()
    {
        return redirect()->route('session-meetings.index');
    }

    /** Store a new session. */
    public function store(Request $request)
    {
        $validated = $this->validateSession($request);
        $validated['status'] = $validated['status'] ?? 'upcoming';

        SessionMeeting::create($validated);

        return redirect()->route('session-meetings.index')
            ->with('success', 'Session created successfully.');
    }

    /** The current session management screen handles session details. */
    public function show(SessionMeeting $session_meeting)
    {
        return redirect()->route('session-meetings.index');
    }

    public function edit(SessionMeeting $session_meeting)
    {
        return view('secretary.session-meetings.edit', compact('session_meeting'));
    }

    /** Update session scheduling and status without touching minutes or adjournment fields. */
    public function update(Request $request, SessionMeeting $session_meeting)
    {
        $validated = $this->validateSession(
            $request,
            $session_meeting->id
        );

        $session_meeting->fill($validated);

        if (! $session_meeting->isDirty()) {
            return redirect()
                ->route('session-meetings.index')
                ->with('info', 'No changes were detected.');
        }

        $session_meeting->save();

        return redirect()
            ->route('session-meetings.index')
            ->with('success', 'Session details updated successfully.');
    }

    public function destroy(SessionMeeting $session_meeting)
    {
        if ($session_meeting->documents()->exists()) {
            return back()->with(
                'error',
                'Cannot delete this session because it has agenda documents. Remove those documents first.'
            );
        }

        if (SessionAttendance::where('session_id', $session_meeting->id)->exists()) {
            return back()->with(
                'error',
                'Cannot delete this session while attendance records exist. Remove the attendance records first.'
            );
        }

        $session_meeting->delete();

        return redirect()->route('session-meetings.index')
            ->with('success', 'Session deleted successfully.');
    }

    /** Show the attendance register and quorum status for a session. */
    public function attendance($id)
    {
        $session = SessionMeeting::findOrFail($id);
        $attendances = SessionAttendance::where('session_id', $session->id)
            ->orderBy('attendee_name')
            ->get();

        $presentCount = SessionAttendance::where('session_id', $session->id)
            ->whereNotNull('member_account_id')
            ->whereIn('attendance_status', ['present', 'late'])
            ->count();
        $quorumRequired = (int) ($session->quorum_required ?? 0);
        $quorumMet = $quorumRequired > 0 ? $presentCount >= $quorumRequired : null;

        $memberAccounts = MembersAccount::with('member')
            ->where('is_active', 1)
            ->whereHas('member', function ($query) {
                $query->whereNotNull('position')
                    ->where('position', '!=', '');
            })
            ->get()
            ->sortBy(fn ($account) => $account->member?->name)
            ->values();

        return view('secretary.session-meetings.attendance', compact(
            'session',
            'attendances',
            'presentCount',
            'quorumRequired',
            'quorumMet',
            'memberAccounts'
        ));
    }

    /** Save attendance rows and the quorum requirement. */
    public function saveAttendance(Request $request, $id)
    {
        $session = SessionMeeting::findOrFail($id);

        $validated = $request->validate([
            'attendees' => ['sometimes', 'array'],
            'attendees.*.id' => ['nullable', 'integer'],
            'attendees.*.attendee_name' => ['required', 'string', 'max:150'],
            'attendees.*.attendee_role' => ['nullable', 'string', 'max:100'],
            'attendees.*.attendance_status' => [
                'required',
                Rule::in(['present', 'absent', 'late']),
            ],
            'attendees.*.remarks' => ['nullable', 'string', 'max:2000'],
            'quorum_required' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'attendees.*.member_account_id' => [
                'nullable',
                'integer',
                'exists:members_accounts,id',
            ],

        ]);


        $attendees = $validated['attendees'] ?? [];

        $memberAccountIds = collect($attendees)
            ->pluck('member_account_id')
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id);

        if ($memberAccountIds->duplicates()->isNotEmpty()) {
            return back()
                ->withErrors([
                    'attendees' => 'A member account can only be assigned once per session.',
                ])
                ->withInput();
        }


        DB::connection($session->getConnectionName() ?: config('database.default'))
            ->transaction(function () use ($session, $validated, $request) {
                $session->quorum_required = $validated['quorum_required'] ?? null;
                $session->save();

                $submittedIds = [];

                foreach (($validated['attendees'] ?? []) as $row) {

                    $memberAccountId = $row['member_account_id'] ?? null;

                    if ($memberAccountId) {
                        $account = MembersAccount::whereKey($memberAccountId)
                            ->where('is_active', 1)
                            ->first();

                        if (! $account) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'attendees' => 'The selected member account is inactive or does not exist.',
                            ]);
                        }
                    }

                    $duplicate = SessionAttendance::where('session_id', $session->id)
                        ->where('member_account_id', $row['member_account_id'] ?? null)
                        ->when(!empty($row['id']), function ($query) use ($row) {
                            $query->where('id', '!=', $row['id']);
                        })
                        ->exists();

                    if (!empty($row['member_account_id']) && $duplicate) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'attendees' => 'This member account is already assigned to an attendance record for this session.',
                        ]);
                    }


                    if (!empty($row['id'])) {
                        // Restrict edits to attendance rows belonging to this session.
                        $attendance = SessionAttendance::where('session_id', $session->id)
                            ->whereKey($row['id'])
                            ->firstOrFail();

                        $attendance->update([
                            'member_account_id' => $row['member_account_id'] ?? null,      
                            'attendee_name' => $row['attendee_name'],
                            'attendee_role' => $row['attendee_role'] ?? null,
                            'attendance_status' => $row['attendance_status'],
                            'remarks' => $row['remarks'] ?? null,
                        ]);
                    } else {
                        $attendance = SessionAttendance::create([
                            'session_id' => $session->id,
                            'member_account_id' => $row['member_account_id'] ?? null,
                            'attendee_name' => $row['attendee_name'],
                            'attendee_role' => $row['attendee_role'] ?? null,
                            'attendance_status' => $row['attendance_status'],
                            'remarks' => $row['remarks'] ?? null,
                        ]);
                    }

                    $submittedIds[] = $attendance->id;
                }

                // Only reconcile/delete omitted rows if the form actually submitted an attendees array.
                // Delete existing attendance records removed from the submitted form.
                if ($request->input('attendees_submitted') === '1') {
                    $query = SessionAttendance::where('session_id', $session->id);

                    if (!empty($submittedIds)) {
                        $query->whereNotIn('id', $submittedIds);
                    }

                    $query->delete();
                }
            });

        return redirect()->route('secretary.session-meetings.attendance', $session->id)
            ->with('success', 'Attendance and quorum requirement saved.');
    }

    public function deleteAttendance($sessionId, $attendanceId)
    {
        $session = SessionMeeting::findOrFail($sessionId);

        $attendance = SessionAttendance::where('session_id', $session->id)
            ->where('id', $attendanceId)
            ->firstOrFail();

        $attendance->delete();

        return redirect()
            ->route('secretary.session-meetings.attendance', $session->id)
            ->with('success', 'Attendance record deleted successfully.');
    }

    private function validateSession(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'session_type' => ['required', Rule::in(['regular', 'special', 'committee'])],
            'session_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue' => ['nullable', 'string', 'max:255'],
            'session_number' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'legislative_term' => ['nullable', 'string', 'max:50'],
            'presiding_officer' => ['nullable', 'string', 'max:150'],
            'status' => [
                $ignoreId ? 'required' : 'nullable',
                Rule::in(['upcoming', 'ongoing', 'completed']),
            ],
        ]);
    }
}
