<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\ChangeTicketStatusRequest;
use App\Http\Requests\ListTicketsRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketCollection;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Database\Eloquent\Builder;

class TicketController extends Controller
{
    #[Endpoint(
        title: 'List tickets',
        description: 'Returns tickets available to the authenticated user as { data: TicketResource[], meta }. Customers only see tickets they created. Agents and admins can see all tickets and can filter, search, sort, and paginate the result set. For agents and admins, mine=true limits the result to tickets assigned to the current user.'
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

        return TicketCollection::make($paginator);
    }

    #[Endpoint(
        title: 'Create ticket',
        description: 'Creates a ticket for the authenticated user and returns { data: TicketResource }. The ticket starts with status open, and priority defaults to medium when omitted.'
    )]
    public function store(StoreTicketRequest $request)
    {
        $data = $request->validated();

        $ticket = Ticket::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? TicketPriority::MEDIUM,
            'status' => TicketStatus::OPEN,
            'created_by' => $request->user()->id,
        ])->refresh();

        return TicketResource::make($ticket)
            ->response()
            ->setStatusCode(201);
    }

    #[Endpoint(
        title: 'Show ticket',
        description: 'Returns { data: TicketResource } when the ticket is visible to the authenticated user. Customers can view only their own tickets; agents and admins can view any ticket.'
    )]
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return TicketResource::make($ticket);
    }

    #[Endpoint(
        title: 'Assign ticket',
        description: 'Assigns a ticket to an agent and returns { data: TicketResource }. Only agents and admins can assign tickets. Customer accounts receive 403. The assignee ID must exist and must belong to an agent, otherwise the endpoint returns 422.'
    )]
    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        $assignee = User::findOrFail($data['assigned_to']);

        if (! $assignee->isAgent()) {
            return response()->json([
                'message' => 'Ticket can be assigned only to an agent.',
            ], 422);
        }

        $ticket->update([
            'assigned_to' => $assignee->id,
        ]);

        return TicketResource::make($ticket->refresh());
    }

    #[Endpoint(
        title: 'Change ticket status',
        description: 'Changes ticket status and returns { data: TicketResource }. Only agents and admins can change status. Customer accounts receive 403.'
    )]
    public function changeStatus(ChangeTicketStatusRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        $ticket->update([
            'status' => TicketStatus::from($data['status']),
        ]);

        return TicketResource::make($ticket->refresh());
    }
}
