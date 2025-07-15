<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;

class UserController extends Controller
{
    /**
     * Display a listing of users with advanced filtering, sorting, and includes.
     * 
     * Example usage:
     * GET /api/users?filter[name]=john&filter[email]=gmail.com&sort=-created_at&include=roles,permissions
     * GET /api/users?filter[created_after]=2024-01-01&sort=name&page[size]=10&page[number]=1
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

        return UserResource::collection($users);
    }

    /**
     * Display the specified user with optional includes.
     * 
     * Example usage:
     * GET /api/users/1?include=roles,permissions
     */
    public function show(Request $request, User $user)
    {
        $user = QueryBuilder::for(User::where('id', $user->id))
            ->allowedIncludes([
                'roles',
                'permissions',
                'roles.permissions',
            ])
            ->first();

        return new UserResource($user);
    }

    /**
     * Get users with specific roles.
     * 
     * Example usage:
     * GET /api/users/with-roles?filter[role]=admin&sort=name
     */
    public function withRoles(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::scope('has_role', 'role'),
                AllowedFilter::scope('has_any_role', 'roles'),
            ])
            ->allowedSorts([
                'name',
                'email',
                'created_at',
            ])
            ->allowedIncludes([
                'roles',
                'permissions',
            ])
            ->defaultSort('name')
            ->paginate($request->input('page.size', 15))
            ->appends($request->query());

        return UserResource::collection($users);
    }


} 