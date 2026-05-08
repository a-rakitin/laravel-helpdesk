<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\ListTicketsRequest;
use App\Models\Ticket;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    #[Endpoint(
        title: 'List tickets',
        description: 'Returns tickets available to the authenticated user. Customers only see their own tickets. Agents and admins can filter, search, sort, and paginate the result set.'
    )]
    public function index(ListTicketsRequest $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();
        $validated = $request->validated();

        $query = Ticket::query();

        // Scope access: customer only own tickets
        if ($user->isCustomer()) {
            $query->where('created_by', $user->id);
        }

        // Filters
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['priority'])) {
            $query->where('priority', $validated['priority']);
        }

        if (isset($validated['assigned_to'])) {
            $query->where('assigned_to', (int) $validated['assigned_to']);
        }

        if ($request->boolean('mine')) {
            // For agent/admin this means assigned_to = me; for customer it's already limited by created_by
            if (! $user->isCustomer()) {
                $query->where('assigned_to', $user->id);
            }
        }

        if (isset($validated['search'])) {
            $search = $validated['search'];

            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sort = $validated['sort'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'desc';

        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = (int) ($validated['per_page'] ?? 15);

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
