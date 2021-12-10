<?php

return [
	/**
	 * The credentials for the firebase service. It should not be empty
	 */
	'jwt' => [
		'secret' => env('JWT_SECRET', 'default')
	]

];
