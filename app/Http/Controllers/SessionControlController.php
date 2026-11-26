<?php
namespace App\Http\Controllers;

use App\Models\SessionAgendaAction;
use App\Models\MembersAccount;
use App\Models\SessionAttendance;
use App\Models\SessionDocument;
use App\Models\SessionMeeting;
use App\Models\MemberDocument;
use App\Models\SessionSuspension;
use App\Models\Committee;
use App\Models\CommitteeReferral;
use App\Models\CommitteeReportAgendaItem;
use App\Models\SessionPreviousMinutesAgendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class SessionControlController extends Controller
{
    
    public function show($id)
    {
        $session = SessionMeeting::findOrFail($id);

        // Get approved LIS document IDs.
        $approvedDocumentIds = MemberDocument::on('lis_mysql')
            ->where('status', 'approved')
            ->pluck('id');

        // Regular agenda documents.
        $documents = SessionDocument::with('document')
            ->where('session_id', $session->id)
            ->whereIn('document_id', $approvedDocumentIds)
            ->orderBy('agenda_order')
            ->get()
            ->map(function ($item) {
                $item->agenda_type = 'document';
                $item->title = $item->document?->title ?? 'Untitled Document';
                $item->file_path = $item->document?->file_path;
                $item->committee_name = null;
                $item->referral_id = null;
                $item->report_file_path = null;

                return $item;
            });

        // Committee report agenda items.
        $committeeReports = CommitteeReportAgendaItem::with([
            'referral.committee',
            'referral.sessionDocument.document',
        ])
            ->where('session_id', $session->id)
            ->orderBy('agenda_order')
            ->get()
            ->map(function ($item) {
                $referral = $item->referral;

                return (object) [
                    'id' => $item->id,
                    'agenda_type' => 'committee_report',
                    'agenda_order' => $item->agenda_order,
                    'status' => $item->status,
                    'title' => $referral?->sessionDocument?->document?->title
                        ?? 'Committee Report',
                    'file_path' => null,
                    'committee_name' => $referral?->committee?->name,
                    'referral_id' => $referral?->id,
                    'report_file_path' => $referral?->report_file_path,
                    'recommendation' => $referral?->recommendation,
                ];
            });

        // Previous minutes agenda items with finalized signed minutes.
    $previousMinutes = SessionPreviousMinutesAgendaItem::with('previousSession')
        ->where('session_id', $session->id)
        ->whereHas('previousSession', function ($query) {
            $query->where('minutes_status', 'finalized')
                ->whereNotNull('minutes_pdf_path');
        })
        ->orderBy('agenda_order')
        ->get()
        ->map(function ($item) {
            $previousSession = $item->previousSession;

            return (object) [
                'id' => $item->id,
                'agenda_type' => 'previous_minutes',
                'agenda_order' => $item->agenda_order,
                'status' => $item->status,
                'title' => 'Previous Minutes - '
                    . ($previousSession?->title ?? 'Previous Session'),
                'file_path' => null,
                'committee_name' => null,
                'referral_id' => null,
                'report_file_path' => null,
                'previous_session_id' => $item->previous_session_id,
                'previous_session_title' => $previousSession?->title,
                'previous_session_date' => $previousSession?->session_date,
                'decision' => $item->decision,
            ];
        });

    // Combine all agenda types.
    $agendaItems = $documents
        ->concat($committeeReports)
        ->concat($previousMinutes)
        ->sortBy('agenda_order')
        ->values();

        // Identify the active agenda item using both type and ID.
        $currentAgendaItem = null;

        if (
            in_array($session->status, ['ongoing', 'suspended'], true)
            && $session->current_agenda_item_id
        ) {
            $currentType = $session->current_agenda_item_type ?: 'document';

            $currentAgendaItem = $agendaItems->first(function ($item) use (
                $session,
                $currentType
            ) {
                return (int) $item->id === (int) $session->current_agenda_item_id
                    && $item->agenda_type === $currentType;
            });
        }

        // Retrieve recent agenda action history.
        $actions = SessionAgendaAction::with([
            'sessionDocument.document',
            'committeeReportAgendaItem.referral.committee',
            'committeeReportAgendaItem.referral.sessionDocument.document',
        ])
            ->where('session_id', $session->id)
            ->latest('acted_at')
            ->limit(30)
            ->get();

        $committees = Committee::where('isActive', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Eligible previous sessions for Previous Minutes agenda.
        $alreadyLinkedPreviousSessionIds = SessionPreviousMinutesAgendaItem::where(
            'session_id',
            $session->id
        )->pluck('previous_session_id');

        $eligiblePreviousSessions = SessionMeeting::where('id', '!=', $session->id)
            ->where('session_date', '<', $session->session_date)
            ->where('minutes_status', 'finalized')
            ->whereNotNull('minutes_pdf_path')
            ->whereNotIn('id', $alreadyLinkedPreviousSessionIds)
            ->orderByDesc('session_date')
            ->get(['id', 'title', 'session_date']);    

        // Quorum monitoring
        $totalMembers = MembersAccount::where('is_active', 1)
            ->whereHas('member', function ($query) {
                $query->whereNotNull('position')
                    ->where('position', '!=', '');
            })
            ->count();

        $presentCount = SessionAttendance::where('session_id', $session->id)
            ->whereNotNull('member_account_id')
            ->whereIn('attendance_status', ['present', 'late'])
            ->distinct()
            ->count('member_account_id');

        $absentCount = max(0, $totalMembers - $presentCount);

        $quorumRequired = (int) ($session->quorum_required ?? 0);

        $quorumMet = $quorumRequired > 0
            ? $presentCount >= $quorumRequired
            : false;

        $quorumConfigured = $quorumRequired > 0;  
        
        // Suspension history
        $suspensions = SessionSuspension::with([
        'suspendedBy:id,name',
        'resumedBy:id,name',
            ])
            ->where('session_id', $session->id)
            ->latest('suspended_at')
            ->get();

        $activeSuspension = SessionSuspension::with('suspendedBy:id,name')
            ->where('session_id', $session->id)
            ->whereNull('resumed_at')
            ->latest('suspended_at')
            ->first();

        return view('secretary.session-control', compact(
                'session',
                'agendaItems',
                'currentAgendaItem',
                'actions',
                'committees',
                'totalMembers',
                'presentCount',
                'absentCount',
                'quorumRequired',
                'quorumMet',
                'quorumConfigured',
                'suspensions',
                'activeSuspension',
                'eligiblePreviousSessions'
            ));
    }


    
    public function start($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $connection = $session->getConnectionName()
            ?: config('database.default');

        $result = DB::connection($connection)->transaction(
            function () use ($session) {

                $lockedSession = SessionMeeting::whereKey($session->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSession->status !== 'upcoming') {
                    return [
                        'success' => false,
                        'message' => 'Only an upcoming session can be started.',
                    ];
                }

                // Require a configured quorum threshold.
                $quorumRequired = (int) ($lockedSession->quorum_required ?? 0);

                if ($quorumRequired <= 0) {
                    return [
                        'success' => false,
                        'message' => 'Please configure the required quorum before starting the session.',
                    ];
                }

                // Count eligible active council members.
                $totalMembers = MembersAccount::where('is_active', 1)
                    ->whereHas('member', function ($query) {
                        $query->whereNotNull('position')
                            ->where('position', '!=', '');
                    })
                    ->count();

                // Count members marked present or late.
                $presentCount = SessionAttendance::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereNotNull('member_account_id')
                    ->whereIn('attendance_status', ['present', 'late'])
                    ->distinct()
                    ->count('member_account_id');

                if ($presentCount < $quorumRequired) {
                    return [
                        'success' => false,
                        'message' => "Quorum not achieved. {$presentCount} member(s) present; {$quorumRequired} required.",
                    ];
                }

                // Check for approved regular documents.
                $approvedDocumentIds = MemberDocument::on('lis_mysql')
                    ->where('status', 'approved')
                    ->pluck('id');

                $hasApprovedDocuments = SessionDocument::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereIn('document_id', $approvedDocumentIds)
                    ->exists();

                // Check for committee reports.
                $hasCommitteeReports = CommitteeReportAgendaItem::where(
                    'session_id',
                    $lockedSession->id
                )->exists();

                if (! $hasApprovedDocuments && ! $hasCommitteeReports) {
                    return [
                        'success' => false,
                        'message' => 'Add at least one approved document or committee report before starting the session.',
                    ];
                }

                $lockedSession->status = 'ongoing';
                $lockedSession->started_at = now();
                $lockedSession->ended_at = null;
                $lockedSession->current_agenda_item_id = null;
                $lockedSession->current_agenda_item_type = null;
                $lockedSession->save();

                return [
                    'success' => true,
                    'message' => 'Session started successfully. Quorum achieved.',
                ];
            }
        );

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function suspend(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $session = SessionMeeting::findOrFail($id);

        $connection = $session->getConnectionName()
            ?: config('database.default');

        $result = DB::connection($connection)->transaction(function () use (
            $session,
            $validated
        ) {
            $lockedSession = SessionMeeting::whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== 'ongoing') {
                return [
                    'success' => false,
                    'message' => 'Only an ongoing session can be suspended.',
                ];
            }

            $activeSuspension = SessionSuspension::where(
                'session_id',
                $lockedSession->id
            )
                ->whereNull('resumed_at')
                ->lockForUpdate()
                ->exists();

            if ($activeSuspension) {
                return [
                    'success' => false,
                    'message' => 'This session already has an active suspension.',
                ];
            }

            SessionSuspension::create([
                'session_id' => $lockedSession->id,
                'reason' => $validated['reason'],
                'suspended_by' => auth()->id(),
                'suspended_at' => now(),
            ]);

            $lockedSession->status = 'suspended';
            $lockedSession->save();

            return [
                'success' => true,
                'message' => 'Session suspended successfully. The current agenda item has been preserved.',
            ];
        });

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()
            ->route('secretary.session-control.show', $id)
            ->with('success', $result['message']);
    }

    public function resume($id)
    {
        $session = SessionMeeting::findOrFail($id);

        $connection = $session->getConnectionName()
            ?: config('database.default');

        $result = DB::connection($connection)->transaction(function () use ($session) {

            $lockedSession = SessionMeeting::whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== 'suspended') {
                return [
                    'success' => false,
                    'message' => 'Only a suspended session can be resumed.',
                ];
            }

            $activeSuspension = SessionSuspension::where(
                'session_id',
                $lockedSession->id
            )
                ->whereNull('resumed_at')
                ->lockForUpdate()
                ->latest('suspended_at')
                ->first();

            if (! $activeSuspension) {
                return [
                    'success' => false,
                    'message' => 'No active suspension record was found.',
                ];
            }

            $activeSuspension->resumed_by = auth()->id();
            $activeSuspension->resumed_at = now();
            $activeSuspension->save();

            $lockedSession->status = 'ongoing';
            $lockedSession->save();

            return [
                'success' => true,
                'message' => 'Session resumed successfully. You may continue the current agenda item.',
            ];
        });

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()
            ->route('secretary.session-control.show', $id)
            ->with('success', $result['message']);
    }
    

    public function end($id)
    {
        $session = SessionMeeting::findOrFail($id);

        if ($session->status !== 'ongoing') {
            return back()->with(
                'error',
                'Only an ongoing session can be ended.'
            );
        }

        DB::transaction(function () use ($session) {
            $session = SessionMeeting::whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status !== 'ongoing') {
                throw new \RuntimeException(
                    'This session has already ended or is no longer ongoing.'
                );
            }

            if ($session->current_agenda_item_id) {
                $currentType = $session->current_agenda_item_type ?: 'document';

                if ($currentType === 'committee_report') {
                    $current = CommitteeReportAgendaItem::where(
                        'session_id',
                        $session->id
                    )
                        ->whereKey($session->current_agenda_item_id)
                        ->lockForUpdate()
                        ->first();

                } else {
                    $current = SessionDocument::where(
                        'session_id',
                        $session->id
                    )
                        ->whereKey($session->current_agenda_item_id)
                        ->lockForUpdate()
                        ->first();
                }

                if ($current && $current->status === 'in_progress') {
                    $current->status = 'pending';
                    $current->save();
                }
            }

            $session->status = 'completed';
            $session->current_agenda_item_id = null;
            $session->current_agenda_item_type = null;
            $session->ended_at = now();
            $session->save();
        });

        return redirect()
            ->route('secretary.session-control.show', $id)
            ->with('success', 'Session ended successfully.');
    }




    public function activateAgendaItem(Request $request, $id, $itemId)
    {
        $session = SessionMeeting::findOrFail($id);

        if ($session->status !== 'ongoing') {
            return back()->with(
                'error',
                'Agenda items can only be activated while the session is ongoing.'
            );
        }

        $type = $request->input('type', 'document');

        if (!in_array($type, ['document', 'committee_report', 'previous_minutes'], true)) {
            abort(422, 'Invalid agenda item type.');
        }

        if ($type === 'committee_report') {
            $item = CommitteeReportAgendaItem::where('session_id', $session->id)
                ->whereKey($itemId)
                ->firstOrFail();

        } elseif ($type === 'previous_minutes') {
            $item = SessionPreviousMinutesAgendaItem::with('previousSession')
                ->where('session_id', $session->id)
                ->whereKey($itemId)
                ->firstOrFail();

            $previousSession = $item->previousSession;

            if (
                ! $previousSession
                || $previousSession->minutes_status !== 'finalized'
                || ! $previousSession->minutes_pdf_path
            ) {
                return back()->with(
                    'error',
                    'Previous minutes can only be activated when the signed minutes are finalized and available.'
                );
            }

        } else {
            $item = SessionDocument::where('session_id', $session->id)
                ->whereKey($itemId)
                ->firstOrFail();

            $isApproved = MemberDocument::on('lis_mysql')
                ->where('id', $item->document_id)
                ->where('status', 'approved')
                ->exists();

            if (! $isApproved) {
                return back()->with(
                    'error',
                    'This agenda item is not linked to an approved document.'
                );
            }
        }

        if (in_array($item->status, ['postponed', 'referred', 'tabled'], true)) {
            return back()->with(
                'error',
                'This item is postponed, referred, or tabled. Change its disposition before activating it.'
            );
        }

        DB::transaction(function () use ($session, $item, $type) {

            // Lock the session and recheck its status.
            $lockedSession = SessionMeeting::whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== 'ongoing') {
                throw ValidationException::withMessages([
                    'session' => 'Agenda items cannot be activated while the session is suspended or not ongoing.',
                ]);
            }

            // Lock the selected agenda item.
            // Lock the selected agenda item.
            if ($type === 'committee_report') {
                $lockedItem = CommitteeReportAgendaItem::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereKey($item->id)
                    ->lockForUpdate()
                    ->firstOrFail();

            } elseif ($type === 'previous_minutes') {
                $lockedItem = SessionPreviousMinutesAgendaItem::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereKey($item->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $previousSession = SessionMeeting::whereKey(
                    $lockedItem->previous_session_id
                )->firstOrFail();

                if (
                    $previousSession->minutes_status !== 'finalized'
                    || ! $previousSession->minutes_pdf_path
                ) {
                    throw ValidationException::withMessages([
                        'previous_minutes' => 'The previous session minutes are not finalized.',
                    ]);
                }

            } else {
                $lockedItem = SessionDocument::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereKey($item->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $previous = null;
            $previousType = null;

            // Retrieve and lock the previous active agenda item.
            if ($lockedSession->current_agenda_item_id) {

                $previousType = $lockedSession->current_agenda_item_type ?: 'document';

                if ($previousType === 'committee_report') {
                    $previous = CommitteeReportAgendaItem::where(
                        'session_id',
                        $lockedSession->id
                    )
                        ->whereKey($lockedSession->current_agenda_item_id)
                        ->lockForUpdate()
                        ->first();

                } elseif ($previousType === 'previous_minutes') {
                    $previous = SessionPreviousMinutesAgendaItem::where(
                        'session_id',
                        $lockedSession->id
                    )
                        ->whereKey($lockedSession->current_agenda_item_id)
                        ->lockForUpdate()
                        ->first();

                } else {
                    $previous = SessionDocument::where(
                        'session_id',
                        $lockedSession->id
                    )
                        ->whereKey($lockedSession->current_agenda_item_id)
                        ->lockForUpdate()
                        ->first();
                }
            }

            // Reset the previous item if a different item is selected.
            if (
                $previous &&
                (
                    $previousType !== $type ||
                    (int) $previous->id !== (int) $lockedItem->id
                ) &&
                $previous->status === 'in_progress'
            ) {
                $previous->status = 'pending';
                $previous->save();
            }

            // Activate the selected item.
            $lockedItem->status = 'in_progress';
            $lockedItem->save();

            // Update the current agenda pointer.
            $lockedSession->current_agenda_item_id = $lockedItem->id;
            $lockedSession->current_agenda_item_type = $type;
            $lockedSession->save();
        });

        return back()->with('success', 'Current agenda item updated.');
    }

    public function nextAgendaItem($id)
    {
        $session = SessionMeeting::findOrFail($id);

        if ($session->status !== 'ongoing') {
            return back()->with(
                'error',
                'Agenda progression is unavailable while the session is not ongoing.'
            );
        }

        $approvedDocumentIds = MemberDocument::on('lis_mysql')
            ->where('status', 'approved')
            ->pluck('id');

        // Load regular approved documents.
        $documents = SessionDocument::where('session_id', $session->id)
            ->whereIn('document_id', $approvedDocumentIds)
            ->where('status', 'pending')
            ->get()
            ->map(function ($item) {
                $item->agenda_type = 'document';
                return $item;
            });

        // Load pending committee reports.
        $committeeReports = CommitteeReportAgendaItem::where('session_id', $session->id)
            ->where('status', 'pending')
            ->get()
            ->map(function ($item) {
                $item->agenda_type = 'committee_report';
                return $item;
            });

        // Load pending Previous Minutes agenda items.
        $previousMinutes = SessionPreviousMinutesAgendaItem::where(
            'session_id',
            $session->id
        )
            ->where('status', 'pending')
            ->whereHas('previousSession', function ($query) {
                $query->where('minutes_status', 'finalized')
                    ->whereNotNull('minutes_pdf_path');
            })
            ->get()
            ->map(function ($item) {
                $item->agenda_type = 'previous_minutes';
                return $item;
            });    

        // Combine both types and sort by agenda order.
        $pendingItems = $documents
            ->concat($committeeReports)
            ->concat($previousMinutes)
            ->sortBy([
                ['agenda_order', 'asc'],
                ['agenda_type', 'asc'],
            ])
            ->values();

        $current = null;

        if ($session->current_agenda_item_id) {
            $currentType = $session->current_agenda_item_type ?: 'document';

            if ($currentType === 'committee_report') {
                $current = CommitteeReportAgendaItem::where(
                    'session_id',
                    $session->id
                )
                    ->whereKey($session->current_agenda_item_id)
                    ->first();

            } elseif ($currentType === 'previous_minutes') {
                $current = SessionPreviousMinutesAgendaItem::where(
                    'session_id',
                    $session->id
                )
                    ->whereKey($session->current_agenda_item_id)
                    ->first();

            } else {
                $current = SessionDocument::where(
                    'session_id',
                    $session->id
                )
                    ->whereKey($session->current_agenda_item_id)
                    ->first();
            }
        }

        $currentType = $session->current_agenda_item_type ?: 'document';

        $next = $pendingItems->first(function ($item) use ($current, $currentType) {
            if (!$current) {
                return true;
            }

            $itemOrder = (int) $item->agenda_order;
            $currentOrder = (int) $current->agenda_order;

            if ($itemOrder !== $currentOrder) {
                return $itemOrder > $currentOrder;
            }

            return strcmp($item->agenda_type, $currentType) > 0;
        });

        if (!$next) {
            return back()->with(
                'error',
                $current
                    ? 'No later pending item exists. Select an item manually or end the session.'
                    : 'No pending agenda items are available.'
            );
        }

        DB::transaction(function () use ($session, $current, $next) {

                // Lock session and verify it is still ongoing.
                $lockedSession = SessionMeeting::whereKey($session->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSession->status !== 'ongoing') {
                    throw ValidationException::withMessages([
                        'session' => 'Agenda progression is unavailable while the session is suspended or not ongoing.',
                    ]);
                }

                // Complete the current agenda item.
                if ($current) {
                    $currentType = $lockedSession->current_agenda_item_type ?: 'document';

                    if ($currentType === 'committee_report') {
                        $lockedCurrent = CommitteeReportAgendaItem::where(
                            'session_id',
                            $lockedSession->id
                        )
                            ->whereKey($current->id)
                            ->lockForUpdate()
                            ->first();

                    } elseif ($currentType === 'previous_minutes') {
                        $lockedCurrent = SessionPreviousMinutesAgendaItem::where(
                            'session_id',
                            $lockedSession->id
                        )
                            ->whereKey($current->id)
                            ->lockForUpdate()
                            ->first();

                    } else {
                        $lockedCurrent = SessionDocument::where(
                            'session_id',
                            $lockedSession->id
                        )
                            ->whereKey($current->id)
                            ->lockForUpdate()
                            ->first();
                    }

                    if ($lockedCurrent && $lockedCurrent->status === 'in_progress') {
                        $lockedCurrent->status = 'completed';
                        $lockedCurrent->save();
                    }
                }

                // Lock and activate the next agenda item.
                // Lock and activate the next agenda item.
                if ($next->agenda_type === 'committee_report') {
                    $lockedNext = CommitteeReportAgendaItem::where(
                        'session_id',
                        $lockedSession->id
                    )
                        ->whereKey($next->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                } elseif ($next->agenda_type === 'previous_minutes') {
                    $lockedNext = SessionPreviousMinutesAgendaItem::where(
                        'session_id',
                        $lockedSession->id
                    )
                        ->whereKey($next->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $previousSession = SessionMeeting::whereKey(
                        $lockedNext->previous_session_id
                    )->firstOrFail();

                    if (
                        $previousSession->minutes_status !== 'finalized'
                        || ! $previousSession->minutes_pdf_path
                    ) {
                        throw ValidationException::withMessages([
                            'previous_minutes' => 'The previous session minutes are not finalized.',
                        ]);
                    }

                } else {
                    $lockedNext = SessionDocument::where(
                        'session_id',
                        $lockedSession->id
                    )
                        ->whereKey($next->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                $lockedNext->status = 'in_progress';
                $lockedNext->save();

                $lockedSession->current_agenda_item_id = $lockedNext->id;
                $lockedSession->current_agenda_item_type = $next->agenda_type;
                $lockedSession->save();
            });

        return back()->with(
            'success',
            'Advanced to the next agenda item.'
        );
    }


    public function recordDisposition(Request $request, $id, $itemId)
    {
        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in(['postponed', 'referred', 'tabled', 'completed']),
            ],
            'reason' => ['required', 'string', 'min:3', 'max:5000'],
            'committee_id' => [
                Rule::requiredIf($request->input('action') === 'referred'),
                'nullable',
                'integer',
                'exists:standing_comittee,id',
            ],
        ]);

        $session = SessionMeeting::findOrFail($id);

        if ($session->status !== 'ongoing') {
            return back()->with(
                'error',
                'Agenda actions can only be recorded while the session is ongoing.'
            );
        }

        // Read the agenda type from the query string.
        $type = $request->query('type', 'document');

        if (!in_array($type, ['document', 'committee_report'], true)) {
            abort(422, 'Invalid agenda item type.');
        }

        $committee = null;

        if ($validated['action'] === 'referred') {
            $committee = Committee::where('id', $validated['committee_id'])
                ->where('isActive', 1)
                ->first();

            if (!$committee) {
                return back()
                    ->withErrors([
                        'committee_id' => 'Please select an active committee.',
                    ])
                    ->withInput();
            }
        }

        // Retrieve the correct agenda record.
        if ($type === 'committee_report') {
            $item = CommitteeReportAgendaItem::where('session_id', $session->id)
                ->whereKey($itemId)
                ->firstOrFail();
        } else {
            $approvedDocumentIds = MemberDocument::on('lis_mysql')
                ->where('status', 'approved')
                ->pluck('id');

            $item = SessionDocument::where('session_id', $session->id)
                ->whereKey($itemId)
                ->whereIn('document_id', $approvedDocumentIds)
                ->firstOrFail();
        }

       DB::transaction(function () use (
            $session,
            $item,
            $type,
            $validated,
            $committee
        ) {

            // Lock the session and verify that it is still ongoing.
            $lockedSession = SessionMeeting::whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== 'ongoing') {
                throw ValidationException::withMessages([
                    'session' => 'Agenda actions cannot be recorded while the session is suspended or not ongoing.',
                ]);
            }

            // Lock the selected agenda item.
            if ($type === 'committee_report') {
                $lockedItem = CommitteeReportAgendaItem::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereKey($item->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            } else {
                $lockedItem = SessionDocument::where(
                    'session_id',
                    $lockedSession->id
                )
                    ->whereKey($item->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            // Apply the disposition.
            $lockedItem->status = $validated['action'];
            $lockedItem->save();

            // Clear the active agenda pointer only if both ID and type match.
            $currentType = $lockedSession->current_agenda_item_type ?: 'document';

            if (
                (int) $lockedSession->current_agenda_item_id === (int) $lockedItem->id
                && $currentType === $type
            ) {
                $lockedSession->current_agenda_item_id = null;
                $lockedSession->current_agenda_item_type = null;
                $lockedSession->save();
            }

            // Record the action history.
            SessionAgendaAction::create([
                'session_id' => $lockedSession->id,
                'session_document_id' => $type === 'document'
                    ? $lockedItem->id
                    : null,
                'committee_report_agenda_item_id' => $type === 'committee_report'
                    ? $lockedItem->id
                    : null,
                'action' => $validated['action'],
                'reason' => $validated['reason'],
                'referred_to' => $committee?->name,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
            ]);

            // Create referrals only for regular documents.
            if ($committee && $type === 'document') {
                CommitteeReferral::create([
                    'session_document_id' => $lockedItem->id,
                    'committee_id' => $committee->id,
                    'referral_remarks' => $validated['reason'],
                    'status' => 'referred',
                    'referred_by' => auth()->id(),
                    'referred_at' => now(),
                ]);
            }
        });

        if ($committee && $type === 'committee_report') {
            return back()->with(
                'success',
                'Committee report disposition recorded. No duplicate referral was created. Committee noted: ' . $committee->name . '.'
            );
        }

        return back()->with(
            'success',
            $committee
                ? 'Agenda item referred to ' . $committee->name . '.'
                : 'Agenda item disposition recorded.'
        );
    }

    public function addPreviousMinutes(Request $request, $id)
    {
        $validated = $request->validate([
            'previous_session_id' => ['required', 'integer'],
        ]);

        $session = SessionMeeting::findOrFail($id);

        if ($session->status !== 'ongoing') {
            return back()->with(
                'error',
                'Previous Minutes can only be added while the session is ongoing.'
            );
        }

        $previousSession = SessionMeeting::whereKey(
            $validated['previous_session_id']
        )
            ->where('session_date', '<', $session->session_date)
            ->where('minutes_status', 'finalized')
            ->whereNotNull('minutes_pdf_path')
            ->first();

        if (! $previousSession) {
            return back()->with(
                'error',
                'The selected session is not eligible for Previous Minutes.'
            );
        }

        $alreadyExists = SessionPreviousMinutesAgendaItem::where(
            'session_id',
            $session->id
        )
            ->where('previous_session_id', $previousSession->id)
            ->exists();

        if ($alreadyExists) {
            return back()->with(
                'error',
                'This session has already been added to the agenda.'
            );
        }

        $maxDocumentOrder = SessionDocument::where(
            'session_id',
            $session->id
        )->max('agenda_order') ?? 0;

        $maxCommitteeOrder = CommitteeReportAgendaItem::where(
            'session_id',
            $session->id
        )->max('agenda_order') ?? 0;

        $maxPreviousMinutesOrder = SessionPreviousMinutesAgendaItem::where(
            'session_id',
            $session->id
        )->max('agenda_order') ?? 0;

        $nextOrder = max(
            $maxDocumentOrder,
            $maxCommitteeOrder,
            $maxPreviousMinutesOrder
        ) + 1;

        SessionPreviousMinutesAgendaItem::create([
            'session_id' => $session->id,
            'previous_session_id' => $previousSession->id,
            'agenda_order' => $nextOrder,
            'status' => 'pending',
        ]);

        return back()->with(
            'success',
            'Previous Minutes added to the agenda successfully.'
        );
    }

    public function recordPreviousMinutesDecision(Request $request, $id, $itemId)
    {
        $validated = $request->validate([
            'decision' => [
                'required',
                Rule::in([
                    'approved',
                    'approved_with_corrections',
                    'deferred',
                ]),
            ],
            'motion_moved_by' => ['nullable', 'string', 'max:255'],
            'motion_seconded_by' => ['nullable', 'string', 'max:255'],
            'remarks' => [
                Rule::requiredIf(
                    $request->input('decision') === 'approved_with_corrections'
                ),
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $session = SessionMeeting::findOrFail($id);

        if ($session->status !== 'ongoing') {
            return back()->with(
                'error',
                'Previous Minutes decisions can only be recorded while the session is ongoing.'
            );
        }

        $item = SessionPreviousMinutesAgendaItem::where('session_id', $session->id)
            ->whereKey($itemId)
            ->firstOrFail();

        DB::transaction(function () use ($session, $item, $validated) {
            $lockedSession = SessionMeeting::whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== 'ongoing') {
                throw ValidationException::withMessages([
                    'session' => 'The session is no longer ongoing.',
                ]);
            }

            $lockedItem = SessionPreviousMinutesAgendaItem::where(
                'session_id',
                $lockedSession->id
            )
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousSession = SessionMeeting::whereKey(
                $lockedItem->previous_session_id
            )->firstOrFail();

            if (
                $previousSession->minutes_status !== 'finalized'
                || ! $previousSession->minutes_pdf_path
            ) {
                throw ValidationException::withMessages([
                    'previous_minutes' => 'The previous session minutes are not finalized.',
                ]);
            }

            $lockedItem->decision = $validated['decision'];
            $lockedItem->motion_moved_by = $validated['motion_moved_by'] ?? null;
            $lockedItem->motion_seconded_by = $validated['motion_seconded_by'] ?? null;
            $lockedItem->remarks = $validated['remarks'] ?? null;
            $lockedItem->status = 'completed';
            $lockedItem->decision_recorded_by = auth()->id();
            $lockedItem->decision_recorded_at = now();
            $lockedItem->save();

            // Keep the legacy session field synchronized.
            $lockedSession->previous_minutes_status = $validated['decision'];

            // Clear the active agenda pointer only if it points to this item.
            if (
                (int) $lockedSession->current_agenda_item_id === (int) $lockedItem->id
                && ($lockedSession->current_agenda_item_type ?: 'document') === 'previous_minutes'
            ) {
                $lockedSession->current_agenda_item_id = null;
                $lockedSession->current_agenda_item_type = null;
            }

            $lockedSession->save();
        });

        return back()->with(
            'success',
            'Previous Minutes decision recorded successfully.'
        );
    }


}
