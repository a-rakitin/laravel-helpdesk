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
    #[Endpoint(
        title: 'List users',
        description: 'Returns a paginated list of users as { data: UserResource[], meta }. Only admins can list users. Agent and customer accounts receive 403.'
    )]
    #[QueryParameter(
        'per_page',
        description: 'Number of users per page. Minimum 1, maximum 100.',
        type: 'integer',
        example: 50
    )]
    #[Response(status: 401, description: 'Unauthenticated. A valid Sanctum bearer token is required.')]
    #[Response(status: 403, description: 'Forbidden. Only admin users can list users.')]
    #[Response(status: 422, description: 'Validation error. The pagination query parameters are invalid.')]
    public function index(ListUsersRequest $request): UserCollection
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);

        $paginator = User::query()
            ->orderBy('id')
            ->paginate($perPage);

        return UserCollection::make($paginator);
    }

    #[Endpoint(
        title: 'Change user role',
        description: 'Changes a user role and returns { data: UserResource }. Only admins can change roles. The role must be admin, agent, or customer. The endpoint refuses changes that would leave the system with zero admin users.'
    )]
    #[PathParameter(
        'user',
        description: 'User ID.',
        type: 'integer',
        example: 3
    )]
    #[Response(status: 401, description: 'Unauthenticated. A valid Sanctum bearer token is required.')]
    #[Response(status: 403, description: 'Forbidden. Only admin users can change roles.')]
    #[Response(status: 404, description: 'Not found. The requested user does not exist.')]
    #[Response(status: 422, description: 'Validation error. The role is invalid or the change would remove the last admin.')]
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
