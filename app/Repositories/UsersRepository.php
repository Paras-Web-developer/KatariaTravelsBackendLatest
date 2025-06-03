<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UsersRepository extends AppRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function login(Request $request)
    {
        $user = $this->findByArray($request->only('email'));
        $tokenName = uniqid();
        $token = $user->createToken($tokenName);
        $user->plain_text_token = $token->plainTextToken;
        return $user;
    }

    public function register(Request $request)
    {
        $user = $this->store($request);
        return $user;
    }

    public function activeUser(Request $request)
    {
        $user = $request->user();
        if ($user->role_type == 4) {
            $user = $user->load('attendance', 'todayAttendance');
        }
        return $user;
    }

    public function logoutCurrentSession(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();
    }

    public function logoutAllSession(Request $request)
    {
        // Revoke all tokens...
        $request->user()->tokens()->delete();
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();
    }

    public function filter()
    {
        $request = request();
        $model = $this->query();

        if ($request->has('keyword') && isset($request->keyword)) {
            $model->whereLike(['name', 'email', 'phone', 'role_id'], $request->keyword);
        }

        if ($request->has('oldest') && isset($request->oldest)) {
            $model->oldest();
        } else {
            $model->latest();
        }

        if ($request->has('role_type') && isset($request->role_type)) {
            $model->userType($request->role_type);
        }
        if ($request->has('role_id') && isset($request->role_id)) {
            $model->where('role_id', $request->role_id);
        }
        if ($request->has('branch_id') && isset($request->branch_id)) {
            $model->where('branch_id', $request->branch_id);
        }
        if ($request->has('email') && isset($request->email)) {
            $model->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('name') && isset($request->name)) {
            $model->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('phone_no') && isset($request->phone_no)) {
            $model->where('phone_no', 'like', '%' . $request->phone_no . '%');
        }

        return $model;
    }

    public function paginate(Request $request)
    {
        return $this->filter()->paginate($request->input('limit', 10));
    }

    public function passwordReset(Request $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status;
    }

    /**
     * set payload data for posts table.
     * 
     * @param Request $request [description]
     * @return array of data for saving.
     */
    protected function setDataPayload(Request $request)
    {
        if ($request->has('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password')),
            ]);
        }

        return $request->all();
    }
}
