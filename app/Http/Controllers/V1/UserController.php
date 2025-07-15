<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class UserController extends ApiController
{
    /**
     * Display a listing of users with advanced filtering, sorting, and includes.
     */
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                'name',
                'email',
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::scope('created_after'),
                AllowedFilter::scope('created_before'),
                AllowedFilter::scope('has_role'),
                AllowedFilter::scope('has_permission'),
            ])
            ->allowedSorts([
                'name',
                'email',
                'created_at',
                'updated_at',
                AllowedSort::field('latest', 'created_at'),
            ])
            ->allowedIncludes([
                'roles',
                'permissions',
                'roles.permissions',
            ])
            ->defaultSort('-created_at')
            ->paginate($request->input('page.size', 15))
            ->appends($request->query());

        return $this->success(UserResource::collection($users));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return $this->created(new UserResource($user), 'User created successfully');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user = QueryBuilder::for(User::where('id', $user->id))
            ->allowedIncludes([
                'roles',
                'permissions',
                'roles.permissions',
            ])
            ->first();

        return $this->success(new UserResource($user));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        $user->update($request->only(['name', 'email']) + 
            ($request->password ? ['password' => bcrypt($request->password)] : []));

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }
} 