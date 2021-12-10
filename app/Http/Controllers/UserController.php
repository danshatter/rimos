<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Events\Registered;

class UserController extends Controller
{
	// Number of seconds till the expiration of the JWT token
	private $tokenDuration = 7200;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	public function register()
	{
		$data = $this->sanitize(request()->all());

		$validator = validator($data, [
			'first_name' => 'required|alpha',
			'last_name' => 'required|alpha',
			'date_of_birth' => 'required|date',
			'phone' => 'required|numeric',
			'address' => 'required',	
			'email' => 'required|email|unique:users',
			'password' => 'required|string|min:5|confirmed',
			'password_confirmation' => 'required|required_with:password'
		]);

		if ($validator->fails()) {
			return response($validator->errors(), 422);
		}

		// Create the user the user and get the user a token
		$user = User::create([
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'date_of_birth' => $data['date_of_birth'],
			'phone' => $data['phone'],
			'address' => $data['address'],
			'password' => Hash::make($data['password'])
		]);

		// Fire the event to tell our application that registration was successful
		event(new Registered($user));

		return response([
			'message' => 'User created successfully',
		], 201);
	}

	public function login()
	{
		$data = $this->sanitize(request()->only('email'));

		// Get the untrimmed password
		$data['password'] = request()->input('password');

		$validator = validator($data, [
			'email' => 'required|email|exists:users',
			'password' => 'required'
		], [
			'email.exists' => 'This email does not belong to a registered user'
		]);

		if ($validator->fails()) {
			return response($validator->errors(), 422);
		}

		// Get the user that the email belongs to
		$user = User::firstWhere('email', $data['email']);

		// Check if the password is correct
		if (!Hash::check($data['password'], $user->password)) {
			return response([
				'message' => 'Invalid email and password combination'
			], 400);
		}

		// Login the user
		return response([
			'message' => 'Login successful',
			'token' => $this->makeToken($user->id)
		]);
	}

	public function update()
	{
		$data = $this->sanitize(request()->all());

		$validator = validator($data, [
			'first_name' => 'required|alpha',
			'last_name' => 'required|alpha',
			'email' => 'required|email|unique:users,email,'.auth()->id(),
			'date_of_birth' => 'required|date',
			'phone' => 'required|numeric',
			'address' => 'required',
		]);

		if ($validator->fails()) {
			return response($validator->errors(), 422);
		}

		auth()->user()->update($data);

		return response([
			'message' => 'User updated successfully',
		]);
	}

	public function destroy($userId)
	{
		if (User::where('id', $userId)->doesntExist()) {
			return response([
				'message' => 'User does not exist or has been deleted',
			]);
		}

		User::destroy($userId);

		return response([
			'message' => 'User deleted successfully',
		]);
	}

	public function storeDepartment($userId)
	{
		$validator = validator(request()->only('departments'), [
			'departments' => 'required|array|distinct'
		]);

		if ($validator->fails()) {
			return response($validator->errors(), 422);
		}

		// Add the users to the department
		$user = User::find($userId);

		if (is_null($user)) {
			return response([
				'message' => 'User does not exist'
			]);
		}

		$departments = request()->input('departments');

		$user->departments()->sync($departments);

		return response([
			'message' => ucwords($user->first_name.' '.$user->last_name).' successfully added to departments with ID '.implode(', ', $departments)
		]);
	}

	public function departments()
	{
		$user = auth()->user()->load('departments');

		return response($user->departments);
	}

	private function sanitize($data)
	{
		$info = [];

		foreach ($data as $key => $value)
		{
			// Don't trim passwords
			if ($key === 'password' || $key === 'password_confirmation') {
				$info[$key] = $value;
				
				continue;
			}

			if (trim($value) === "") {
				$info[$key] = null;
			} else {
				$info[$key] = trim($value);
			}
		}

		return $info;
	}

	/**
	 * Create the JWT token
	 */
	private function makeToken($id)
	{
		$payload = [
			'iss' => config('app.url'),
			'iat' => time(),
			'exp' => time() + $this->tokenDuration,
			'userId' => $id
		];

		return JWT::encode($payload, config('services.jwt.secret'));
	}

}
