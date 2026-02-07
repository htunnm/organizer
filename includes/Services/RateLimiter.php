<?php
/**
 * Rate Limiter Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

/**
 * Class RateLimiter
 */
class RateLimiter {

	/**
	 * Check if the request is allowed.
	 *
	 * @param string $ip_address IP address.
	 * @param int    $limit      Limit of requests.
	 * @param int    $window     Time window in seconds.
	 * @return bool True if allowed, false if limit exceeded.
	 */
	public static function check( $ip_address, $limit = 10, $window = 60 ) {
		$transient_key = 'organizer_rate_limit_' . md5( $ip_address );
		$current_count = get_transient( $transient_key );

		if ( false === $current_count ) {
			set_transient( $transient_key, 1, $window );
			return true;
		}

		if ( $current_count >= $limit ) {
			return false;
		}

		set_transient( $transient_key, $current_count + 1, $window );

		return true;
	}
}
