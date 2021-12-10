<?php

namespace App\Http\Controllers;

use App\Models\Department;

class DepartmentController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	public function store($departmentId)
	{
		$validator = validator(request()->only('users'), [
			'users' => 'required|array|distinct',
		]);

		if ($validator->fails()) {
			return response($validator->errors(), 422);
		}

		// Add the users to the department
		$department = Department::find($departmentId);

		if (is_null($department)) {
			return response([
				'message' => 'Department does not exist'
			]);
		}

		$users = request()->input('users');

		$department->users()->sync($users);

		return response([
			'message' => 'Users with ID '.implode(', ', $users).' Added to '.$department->name.' successfully'
		]);
	}
}
