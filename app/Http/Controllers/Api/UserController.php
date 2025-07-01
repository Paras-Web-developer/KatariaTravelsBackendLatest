<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\UsersRepository;
use App\Http\Resources\UserResource;
use App\Http\Resources\LoginUserResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use SebastianBergmann\Type\NullType;

class UserController extends BaseController
{
	protected $userRepo;

	public function __construct(UsersRepository $userRepo)
	{
		$this->userRepo = $userRepo;
	}
	public function login(LoginRequest $request)
	{
		$request->authenticate();
		$user = auth()->user();

		if (is_null($user->employee_verified_at) && $user->status !== 'active') {
			return response()->json([
				'message' => 'Your account is not verified or is inactive. Please contact the administrator.',
			], 403);
		}


		$response = $this->userRepo->login($request);
		$user_id = $response->id;
		$user = User::find($user_id);
		// $userUpdate = $user->update(['user_login' => $request->user_login]);
		$user->update(['user_login' => $request->user_login]);

		return $this->successWithData(new LoginUserResource($response), 'Login successfully');
	}
	public function userList(Request $request)
	{
		$user = auth()->user();
		$limit = $request->has('limit') ? $request->limit : 1000;


		// $response = $this->userRepo
		//     ->filter()
		//     ->whereHas('enquiries', function ($query) {
		//         $query->whereNull('parent_id');
		//     })
		//     ->with(['enquiries' => function ($query) {
		//         $query->whereNull('parent_id');
		//     }, 'department', 'branch'])
		//     ->paginate($limit);
		$response = $this->userRepo->filter()->with('department', 'branch')->paginate($limit);

		return $this->successWithPaginateData(UserResource::collection($response), $response);
	}

	public function adminUserList(Request $request)
	{
		$user = auth()->user();
		if (!in_array($user->role_id, [1, 3, 4])) {
			return response()->json([
				'message' => 'Unauthorized: Only admins, super admins, and employees can see this record.'
			], 403);
		}
		$validRoleIds = [1, 3, 4];
		$limit = $request->has('limit') ? $request->limit : 1000;
		$response = $this->userRepo->filter()
			->whereIn('role_id', $validRoleIds)
			->with('enquiries', 'department', 'branch')  // Include related data
			->paginate($limit);
		return $this->successWithPaginateData(UserResource::collection($response), $response);
	}

	public function changeUserPassword(Request $request)
	{
		$user = auth()->user();

		if ($user->role_id != 4) {
			return response()->json([
				'message' => "Unauthorized: Only super admin can change users's password."
			], 403);
		}

		$validated = $request->validate([
			'id' => 'required|integer|exists:users,id',
			'password' => ['required', 'confirmed', Password::defaults()]
		]);

		$foundUser = User::findOrFail($validated['id']);

		$foundUser->password = bcrypt($validated['password']);
		$foundUser->save();

		return $this->success("Password changed successfully");
	}

	public function findById(Request $request, $id)
	{

		$user = User::with('enquiries', 'department', 'branch')->find($id);
		if (!$user) {
			return $this->success('User not found');
		}

		return $this->successWithData(new LoginUserResource($user));
	}

	public function register(Request $request)
	{
		$authUser = Auth::user();

		if (!in_array($authUser->role_id, [1, 4])) {
			return response()->json([
				'status' => false,
				'message' => 'Unauthorized: Only Admin or Super Admin can register users.',
			], 403);
		}
		
		$request->validate([
			'name' => ['required', 'string', 'max:255'],
			'role_id' => 'required|integer|exists:roles,id',
			'branch_id' => 'required|integer|exists:branches,id',
			'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
			'image' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg,jpg,webp|max:2048',
			'password' => ['required', 'confirmed', Password::defaults()],
			'description' => ['nullable', 'string'],
			'phone_no' => 'required|string|unique:users,phone_no',
			'employee_verified_at' => 'nullable|date',
			'user_login' => 'nullable|boolean|in:0,1',
		]);


		$newRequest = new Request($request->except('image'));

		if ($request->hasFile('image')) {
			$fileResponse = $request->image->store('upload', 'public');
			$newRequest->merge(['image' => $fileResponse]);
		}
		// Set employee_verified_at to the provided value or the current timestamp if null
		$newRequest->merge(['employee_verified_at' => $request->employee_verified_at ?? now()]);
		$user = User::create($newRequest->all());
		return $this->success(new LoginUserResource($user), 'Register successfully');
	}



	public function updateUser(Request $request)
	{

		$request->validate([
			'id' => 'required|integer|exists:users,id',
			'name' => ['nullable', 'string', 'max:255'],
			'role_id' => 'sometimes|integer|exists:roles,id',
			'branch_id' => 'nullable|integer|exists:branches,id',
			'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class)->ignore($request->id)],
			'image' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg,jpg,webp|max:2048',
			'description' => ['nullable', 'string'],
			'user_login' => 'nullable|boolean|default:0|in:0,1',
		]);

		$user = User::findOrFail($request->id);
		if ($request->name) {
			$user->name = $request->name;
		}
		if ($request->email) {
			$user->email = $request->email;
		}
		if ($request->phone_no) {
			$user->phone_no = $request->phone_no;
		}
		if ($request->age) {
			$user->age = $request->age;
		}
		if ($request->salary) {
			$user->salary = $request->salary;
		}
		if ($request->description) {
			$user->description = $request->description;
		}
		if ($request->role_id) {
			$user->role_id = $request->role_id;
		}
		if ($request->branch_id) {
			$user->branch_id = $request->branch_id;
		}

		if ($request->user_login) {
			$user->user_login = $request->user_login;
		}

		if ($request->hasFile('image')) {
			$fileResponse = $request->image->store('upload', 'public');
			$user->image = $fileResponse;
		}
		$user->save();

		//$response = $this->userRepo->update($request->id, $request);
		return $this->successWithData(new LoginUserResource($user), 'Update successfully');
	}

	public function getUserInfo(Request $request)
	{
		$response = $this->userRepo->activeUser($request);
		return $this->successWithData(new LoginUserResource($response));
	}

	public function updatePassword(Request $request)
	{
		$request->validate([
			'password' => ['required', 'confirmed', Password::defaults()]
		]);

		$this->userRepo->updatePassword($request);
		return $this->success("Password update successfully");
	}

	public function forgotPassword(Request $request)
	{
		$request->validate([
			'email' => ['required', 'email'],
		]);

		$response = $this->userRepo->passwordReset($request);
		return $this->successWithData(__($response));
	}

	public function logout(Request $request)
	{
		$user = auth()->user();
		$user->update(['user_login' => 0]);
		$this->userRepo->logoutCurrentSession($request);
		return $this->success("Successfully logout");
	}


	public function employeeRegister(Request $request)
	{
		$request->validate([
			'name' => ['required', 'string', 'max:255'],
			'role_id' => 'required|integer|exists:roles,id',
			'branch_id' => 'required|integer|exists:branches,id',
			'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
			'image' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg,jpg,webp|max:2048',
			'employee_verified_at' => 'nullable|date',
			'password' => ['required', 'confirmed', Password::defaults()],
			'description' => ['nullable', 'string'],
			'phone_no' => 'required|string|unique:users,phone_no',
			'department_id' => 'required|integer|exists:departments,id',
			'age' => 'required|integer',
			'salary' => ['required', 'numeric'],
		]);

		$newRequest = new Request($request->except('image'));

		// Handle image upload
		if ($request->hasFile('image')) {
			$fileResponse = $request->image->store('upload', 'public');
			$newRequest->merge(['image' => $fileResponse]);
		}

		$user = User::create($newRequest->all());


		$user->employee_verified_at = now();
		$user->save();

		return $this->success(new LoginUserResource($user), 'Register successfully');
	}


	public function verifyEmployee($id)
	{
		$authUser = auth()->user();
		if (!in_array($authUser->role_id, [1, 4])) {
			return response()->json([
				'message' => 'Unauthorized: Only admins and super admins can verify employees/user.'
			], 403);
		}
		$user = User::findOrFail($id);

		if (is_null($user->employee_verified_at)) {
			$user->employee_verified_at = now();
			$user->status = 'active';
			$user->save();

			return response()->json([
				'message' => 'Employee/User verified successfully'
			], 200);
		} else {
			$user->employee_verified_at = null;
			$user->status = 'inactive';
			$user->save();

			return response()->json([
				'message' => 'Employee/User unverified successfully'
			], 200);
		}
	}

	public function delete($id)
	{

		$userModule = User::find($id);

		if (!$userModule) {
			return response()->json([
				'message' => 'User record not found',
			], 404);
		}


		$authUser = auth()->user();
		if (!in_array($authUser->role_id, [1, 4])) {
			return response()->json([
				'message' => 'You can only delete users with roles of admin or super admin.',
			], 403);
		}

		$userModule->delete();

		return response()->json([
			'flash_type' => 'success',
			'flash_message' => 'User deleted successfully',
			'flash_description' => $userModule->name
		]);
	}

	public function employeeStatus(Request $request, $id)
	{
		$authUser = auth()->user();

		if (!in_array($authUser->role_id, [1, 4])) {
			return response()->json([
				'message' => 'Unauthorized: Only admins and super admins can verify employees/user.'
			], 403);
		}
		$user = User::findOrFail($id);

		if (is_null($user->employee_verified_at)) {
			$user->employee_verified_at = now();
			$user->status = 'active';
			$user->save();

			return response()->json([
				'message' => 'Employee/User verified successfully'
			], 200);
		} else {
			$user->employee_verified_at = null;
			$user->status = 'inactive';
			$user->save();

			return response()->json([
				'message' => 'Employee/User unverified successfully'
			], 200);
		}
	}
}
