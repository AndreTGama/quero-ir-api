<?php

namespace App\Http\Controllers\Api\User;

use App\Builder\ReturnMessage;
use App\Enums\HashsUsedsEnum;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\HashsUseds;
use Illuminate\Support\Str;
use App\Models\User;
use App\Service\ValidSlug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * index
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $id = auth()->user()->id;

        $user = User::find($id);

        if (empty($user)) return ReturnMessage::message(false, 'There no user in Database', null, null, [], 200);

        return ReturnMessage::message(false, 'Users found', null, null, $user, 200);
    }
    /**
     * list all users in database
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $users = User::get();

        if (empty($users)) return ReturnMessage::message(false, 'There no users in Database', null, null, [], 200);

        return ReturnMessage::message(false, 'Users found', null, null, $users, 200);
    }
    /**
     * Show user table information
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!isset($user)) return ReturnMessage::message(false, 'User not found in Database', null, null, null, 200);

        $user->type_user = $user->typeUser;

        if (empty($user)) return ReturnMessage::message(false, 'User not found in Database', null, null, null, 200);

        return ReturnMessage::message(false, 'User found', null, null, $user, 200);
    }
    /**
     * list all users in database
     *
     * @return JsonResponse
     */
    public function listDeleted(): JsonResponse
    {
        $users = User::onlyTrashed()->get();

        if ($users->isEmpty()) return ReturnMessage::message(false, 'There no users in Database', null, null, [], 200);

        return ReturnMessage::message(false, 'Users deleted found', null, null, $users, 200);
    }
    /**
     * store
     *
     * @param  StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        dd(HashsUsedsEnum::ActiveAccount);
        $data = $request->all();

        $data['password'] = bcrypt($data['password']);

        $data['slug'] = ValidSlug::slug('users', Str::slug($data['name']));

        $user = User::create($data);

        if (empty($user->id)) return ReturnMessage::message(true, 'Error creating user', null, null, [], 409);

        HashsUseds::create([
            'user_id' => $user->id,
            'hash' => uniqid(),
            'type' => HashsUsedsEnum::ActiveAccount
        ]);

        return ReturnMessage::message(false, 'User created successfully', null, null, null, 201);
    }
    /**
     * Update
     *
     * @param  UpdateUserRequest $request
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = $request->all();

        if (!empty($data['name'])) $data['slug'] = $this->countUpdateSlug(Str::slug($data['name']), $id);

        if (empty($data['profile_picture'])) $data['profile_picture'] = "profile_picture_default.png";

        $user = User::find($id)->update($data);

        if (!$user) return ReturnMessage::message(true, 'Error updating user', null, null, [], 409);

        return ReturnMessage::message(false, 'User successfully updated', null, null, null, 201);
    }
    /**
     * delete
     *
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $user = User::withTrashed()->find($id)->delete();

        if (!$user) return ReturnMessage::message(true, 'Error delete user', null, null, [], 409);

        return ReturnMessage::message(false, 'User deleted successfully', null, null, null, 201);
    }
    /**
     * restore
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::withTrashed()->find($id)->restore();

        if (!$user) return ReturnMessage::message(true, 'Error restore user', null, null, [], 409);

        return ReturnMessage::message(false, 'User restored with success', null, null, null, 200);
    }
    /**
     * restoreAll
     *
     * @return JsonResponse
     */
    public function restoreAll(): JsonResponse
    {
        User::onlyTrashed()->restore();

        return ReturnMessage::message(false, 'Users restored with success', null, null, null, 200);
    }
    /**
     * countUpdateSlug
     *
     * @param  string $slug
     * @param  int $id
     * @return string
     */
    public function countUpdateSlug(string $slug, int $id): string
    {
        $user = User::find($id);

        if ($user->slug == $slug) return $slug;

        $count = User::where('slug', $slug)->count();

        if ($count > 0) $slug = $slug . "$count";

        return $slug;
    }
    /**
     * activeAccount
     *
     * @param  string $slug
     * @return JsonResponse
     */
    public function activeAccount(string $slug): JsonResponse
    {
        try {
            $user = User::where('slug', $slug)->first();

            if (empty($user))
                throw new \Exception("User with slug:$slug not found in system");

            if ($user->email_verified_at)
                throw new \Exception("User with slug:$slug is already active");

            $user->update(['email_verified_at' => date('Y-m-d h:i:s')]);

            return ReturnMessage::message(false, 'Activated user', 'Activated user', null, null, 200);
        } catch (\Exception $e) {
            return ReturnMessage::message(false, 'User not found', $e->getMessage(), $e, null, 401);
        }
    }
}
