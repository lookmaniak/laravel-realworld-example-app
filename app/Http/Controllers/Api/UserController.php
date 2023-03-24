<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UserAccountRequest;
use App\Http\Requests\Api\DeleteUserAccountRequest;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\UserAccountResource;
use App\Http\Resources\Api\ProfileResource;
use App\Http\Resources\Api\UserAccountResourceCollection;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\Api\UserResource
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\UpdateUserRequest $request
     * @return \App\Http\Resources\Api\UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request)
    {
        if (empty($attrs = $request->validated())) {
            return response()->json([
                'message' => trans('validation.invalid'),
                'errors' => [
                    'any' => [trans('validation.required_at_least_one')],
                ],
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        $userB = $user;
        $user->update($attrs);
        return response()->json([
            'userB' => $userB,
            'attrs' => $attrs,
            'user' => $user,
        ], 200);
        //return new UserResource($user);
    }

    /**
     * Display the profile resource.
     *
     * @return \App\Http\Resources\Api\UserResource
     */
    public function showProfile(Request $request)
    {
        //return new ProfileResource($request->user());
        return new ProfileResource($request->user()->profile());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\UpdateUserRequest $request
     * @return \App\Http\Resources\Api\UserResource|\Illuminate\Http\JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        if (empty($attrs = $request->validated())) {
            return response()->json([
                'message' => trans('validation.invalid'),
                'errors' => [
                    'any' => [trans('validation.required_at_least_one')],
                ],
            ], 422);
        }

        /** @var \App\Models\User $user */
        $profile = $request->user()->profile();
        $_profile = $profile;
        $profile->update($attrs);
        return response()->json([
            '_profile' => $_profile,
            'attrs' => $attrs,
            'profile' => $profile,
        ], 200);
        //return new UserResource($user);
    }

    public function getAccounts(Request $request) {
        return new UserAccountResourceCollection($request->user()->accounts);
    }

    public function saveUserAccount(UserAccountRequest $r) {
        if (empty($attrs = $r->validated())) {
            return response()->json([
                'message' => trans('validation.invalid'),
                'errors' => [
                    'any' => [trans('validation.required_at_least_one')],
                ],
            ], 422);
        }

        $user = new User();
        $user->user_id = $r->user()->id;
        $user->username = $attrs['username'];
        $user->name = $attrs['name'];
        $user->email = $attrs['username'] . '@' . $r->user()->username . '.com';
        $user->password = Hash::make($attrs['password']);
        
        $user->save();

        return (new UserAccountResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function destroyAccount(Request $request) {
        $user = \Auth::user();
        $accounts = $user->accounts;
        $deletion = $accounts->where('username', $request->username)->where('is_deleted', false)->first();
        $deletion->is_deleted = true;
        $deletion->deleted_by = $user->id;
        $deletion->deleted_at = date("Y-m-d H:i:s");
        $deletion->save();

        return response()->json([
            'success' => $deletion->save(),
        ], 200);;
    }
}
