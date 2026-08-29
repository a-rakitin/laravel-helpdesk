<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ChangeUserRoleRequest;
use App\Http\Requests\ListUsersRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    #[Endpoint(title: 'List users', description: 'Returns a paginated list of users ordered by ID. Only admins can list users.')]
    #[Response(
        status: 200,
        description: 'Paginated users.',
        examples: [[
            'data' => [[
                'id' => 3,
                'name' => 'Support Agent',
                'email' => 'agent@example.com',
                'role' => 'agent',
                'created_at' => '2026-08-19T10:25:30.000000Z',
                'updated_at' => '2026-08-29T10:20:00.000000Z',
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
    public function index(ListUsersRequest $request): UserCollection
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);

        $paginator = User::query()
            ->orderBy('id')
            ->paginate($perPage);

        return UserCollection::make($paginator);
    }

    #[Endpoint(title: 'Change user role', description: 'Changes a user\'s role. Only admins can change user roles. At least one admin must remain.')]
    #[Response(
        status: 200,
        description: 'The user after the role change.',
        examples: [[
            'data' => [
                'id' => 3,
                'name' => 'Support Agent',
                'email' => 'agent@example.com',
                'role' => 'agent',
                'created_at' => '2026-08-19T10:25:30.000000Z',
                'updated_at' => '2026-08-30T10:30:00.000000Z',
            ],
        ]],
    )]
    #[PathParameter('user', description: 'User ID.', example: 3)]
    public function changeRole(ChangeUserRoleRequest $request, User $user): UserResource
    {
        $data = $request->validated();
        $role = UserRole::from($data['role']);

        $updatedUser = DB::transaction(function () use ($user, $role): User {
            $lockedAdmins = collect();

            if ($role !== UserRole::ADMIN) {
                $lockedAdmins = User::query()
                    ->where('role', UserRole::ADMIN->value)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
            }

            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedUser->role === UserRole::ADMIN
                && $role !== UserRole::ADMIN
                && $lockedAdmins->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'role' => ['At least one admin user must remain.'],
                ]);
            }

            $lockedUser->update([
                'role' => $role,
            ]);

            return $lockedUser->refresh();
        });

        return UserResource::make($updatedUser);
    }
}
