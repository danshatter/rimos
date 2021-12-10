<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Events\Registered;
use App\Mail\Welcome as WelcomeMail;

class SendWelcomeEmail
{
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  \App\Events\Registered  $event
	 * @return void
	 */
	public function handle(Registered $event)
	{
		Mail::to($event->user->email)
				->send((new WelcomeMail($event->user))
				->subject('Welcome to Rimo Technologies'));
	}

}
