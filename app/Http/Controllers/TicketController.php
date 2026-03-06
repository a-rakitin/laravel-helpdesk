<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();

        $query = Ticket::query();

        // Scope access: customer only own tickets
        if ($user->isCustomer()) {
            $query->where('created_by', $user->id);
        }

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->query('assigned_to')) {
            $query->where('assigned_to', (int) $assignedTo);
        }

        if ($request->boolean('mine')) {
            // For agent/admin this means assigned_to = me; for customer it's already limited by created_by
            if (! $user->isCustomer()) {
                $query->where('assigned_to', $user->id);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        $allowedSorts = ['created_at', 'priority', 'status'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'in:low,medium,high'],
        ]);

        $ticket = Ticket::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? TicketPriority::MEDIUM,
            'status' => TicketStatus::OPEN,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return response()->json($ticket);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $data = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $assignee = User::findOrFail($data['assigned_to']);

        if (! $assignee->isAgent()) {
            return response()->json([
                'message' => 'Ticket can be assigned only to an agent.',
            ], 422);
        }

        $ticket->update([
            'assigned_to' => $assignee->id,
        ]);

        return response()->json($ticket);
    }

    public function changeStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('changeStatus', $ticket);

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,closed'],
        ]);

        $ticket->update([
            'status' => TicketStatus::from($data['status']),
        ]);

        return response()->json($ticket);
    }
}
