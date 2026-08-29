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
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Database\Eloquent\Builder;

class TicketController extends Controller
{
    #[Endpoint(title: 'List tickets', description: 'Returns a paginated list of tickets accessible to the authenticated user. Customers see only tickets they created; agents and admins see all tickets.')]
    #[Response(
        status: 200,
        description: 'Paginated tickets accessible to the authenticated user.',
        examples: [[
            'data' => [[
                'id' => 42,
                'title' => 'Cannot access account',
                'description' => 'The user cannot sign in after resetting the password.',
                'status' => 'open',
                'priority' => 'high',
                'created_by' => 1,
                'assigned_to' => 3,
                'created_at' => '2026-08-27T09:15:30.000000Z',
                'updated_at' => '2026-08-27T09:15:30.000000Z',
            ]],
            'meta' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 1,
                'last_page' => 1,
            ],
        ]],
    )]
    #[QueryParameter('page', description: 'Page number.', type: 'integer', default: 1, example: 1)]
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
            $searchOperator = $query->getModel()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function (Builder $q) use ($search, $searchOperator) {
                $q->where('title', $searchOperator, "%{$search}%")
                    ->orWhere('description', $searchOperator, "%{$search}%");
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

    #[Endpoint(title: 'Create ticket', description: 'Creates a ticket for the authenticated user. New tickets have open status and medium priority by default.')]
    #[Response(
        status: 201,
        description: 'The created ticket.',
        examples: [[
            'data' => [
                'id' => 42,
                'title' => 'Cannot sign in',
                'description' => 'Login fails after password reset.',
                'status' => 'open',
                'priority' => 'high',
                'created_by' => 1,
                'assigned_to' => null,
                'created_at' => '2026-08-28T09:15:30.000000Z',
                'updated_at' => '2026-08-28T09:15:30.000000Z',
            ],
        ]],
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

    #[Endpoint(title: 'Show ticket', description: 'Returns a ticket accessible to the authenticated user. Customers can view only tickets they created; agents and admins can view any ticket.')]
    #[Response(
        status: 200,
        description: 'The requested ticket.',
        examples: [[
            'data' => [
                'id' => 42,
                'title' => 'Cannot access account',
                'description' => 'The user cannot sign in after resetting the password.',
                'status' => 'open',
                'priority' => 'high',
                'created_by' => 1,
                'assigned_to' => 3,
                'created_at' => '2026-08-29T09:15:30.000000Z',
                'updated_at' => '2026-08-29T09:15:30.000000Z',
            ],
        ]],
    )]
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return TicketResource::make($ticket);
    }

    #[Endpoint(title: 'Assign ticket', description: 'Assigns a ticket to an agent. Only agents and admins can assign tickets.')]
    #[Response(
        status: 200,
        description: 'The ticket with its new assignee.',
        examples: [[
            'data' => [
                'id' => 42,
                'title' => 'Cannot access account',
                'description' => 'The user cannot sign in after resetting the password.',
                'status' => 'open',
                'priority' => 'high',
                'created_by' => 1,
                'assigned_to' => 2,
                'created_at' => '2026-08-28T09:15:30.000000Z',
                'updated_at' => '2026-08-29T10:20:00.000000Z',
            ],
        ]],
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

    #[Endpoint(title: 'Change ticket status', description: 'Changes a ticket\'s status. Only agents and admins can change ticket status.')]
    #[Response(
        status: 200,
        description: 'The ticket with its updated status.',
        examples: [[
            'data' => [
                'id' => 42,
                'title' => 'Cannot access account',
                'description' => 'The user cannot sign in after resetting the password.',
                'status' => 'in_progress',
                'priority' => 'high',
                'created_by' => 1,
                'assigned_to' => 2,
                'created_at' => '2026-08-28T09:15:30.000000Z',
                'updated_at' => '2026-08-29T10:30:00.000000Z',
            ],
        ]],
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
