<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class CalendarEventController extends Controller
{
    public function index()
    {
        $data = CalendarEvent::where('isActive', true)->get();

        if (request()->ajax()) {
            return $this->datatable($data);
        }

        return view('calendar-event.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('calendar_events')->where(function ($query) {
                    return $query->where('isActive', true);
                }),
            ],
            'description' => 'required|string|max:60',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'all_day' => 'boolean',
            'color' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $event = CalendarEvent::create($validated);
            DB::commit();

            return response(['data' => $event, 'message' => 'Event created successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }

    public function edit(string $id)
    {
        $event = CalendarEvent::where('isActive', true)->findOrFail($id);
        return response(['data' => $event, 'message' => 'success'], 200);
    }

    public function update(Request $request, string $id)
    {
        $event = CalendarEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('calendar_events')->ignore($event->id)->where(function ($query) {
                    return $query->where('isActive', true);
                }),
            ],
            'description' => 'required|string|max:60',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'all_day' => 'boolean',
            'color' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $event->update($validated);
            DB::commit();

            return response(['data' => $event, 'message' => 'Event updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => $e->getMessage(), 'status' => 'update failed'], 500);
        }
    }

    public function destroy(string $id)
    {
        $event = CalendarEvent::findOrFail($id);
        $event->isActive = false;
        $event->save();

        return response(['data' => $event, 'message' => 'Event deactivated successfully.'], 200);
    }

    public function datatable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                $start = \Carbon\Carbon::parse($row->start)->format('M d, Y h:i A');

                if ($row->all_day) {
                    $startBadge = '<span class="badge bg-primary rounded-pill me-1">
                        <i class="fas fa-calendar-day me-1"></i>' . \Carbon\Carbon::parse($row->start)->format('M d, Y') . '
                    </span>';

                    if ($row->end) {
                        $endBadge = '<span class="badge bg-secondary rounded-pill">
                            <i class="fas fa-arrow-right mx-1"></i>' . \Carbon\Carbon::parse($row->end)->format('M d, Y') . '
                        </span>';
                        return $startBadge . $endBadge;
                    }

                    return $startBadge;
                }

                // If not all-day event (show time)
                $startBadge = '<span class="badge bg-primary rounded-pill me-1">
                    <i class="fas fa-clock me-1"></i>' . $start . '
                </span>';

                if ($row->end) {
                    $end = \Carbon\Carbon::parse($row->end)->format('M d, Y h:i A');
                    $endBadge = '<span class="badge bg-secondary rounded-pill">
                        <i class="fas fa-arrow-right mx-1"></i>' . $end . '
                    </span>';
                    return $startBadge . $endBadge;
                }

                return $startBadge;
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
            ->addColumn('color', function ($row) {
                if (!$row->color) {
                    return '<span class="badge bg-secondary">None</span>';
                }

                return '<span class="d-inline-block rounded-circle" style="width: 20px; height: 20px; background-color: ' . e($row->color) . '; border: 1px solid #ccc;" title="' . e($row->color) . '"></span>';
            })
            ->rawColumns(['date', 'description', 'color', 'actions'])
            ->make(true);
    }
}