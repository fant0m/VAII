<?php namespace controllers;

use models\User;
use lib\Auth;

class NotificationController extends Controller
{
	public function get() {
		$user = Auth::get();
		if ($user) {
			return json($user->newNotifications(), true);
		}
	}

	public function update() {
		$user = Auth::get();
		if ($user) {
			$notifications = $user->notifications();
			foreach ($notifications as $notification) {
				$notification->new = 0;
				$notification->update();
			}
		}
	}
}