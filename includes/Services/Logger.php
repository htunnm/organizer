<?php
/**
 * Logger Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Log;

/**
 * Class Logger
 */
class Logger {

	/**
	 * Log an action.
	 *
	 * @param string $action     Action name.
	 * @param string $message    Log message.
	 * @param int    $event_id   Event ID.
	 * @param int    $session_id Session ID.
	 * @return int|false Log ID.
	 */
	public static function log( $action, $message, $event_id = 0, $session_id = 0 ) {
		return Log::create(
			array(
				'action'     => $action,
				'message'    => $message,
				'event_id'   => $event_id,
				'session_id' => $session_id,
				'user_id'    => get_current_user_id(),
			)
		);
	}
}
