<?php
/**
 * Expiration CLI Command.
 *
 * @package Organizer\Cli
 */

namespace Organizer\Cli;

use WP_CLI;
use Organizer\Services\ExpirationService;

/**
 * Class ExpirationCommand
 */
class ExpirationCommand {

	/**
	 * Process expired registrations.
	 *
	 * ## EXAMPLES
	 *
	 *     wp organizer process-expirations
	 */
	public function process_expirations() {
		WP_CLI::log( 'Checking for expired registrations...' );

		$service = new ExpirationService();
		$count   = $service->process_expired_registrations();

		WP_CLI::success( "Processed $count expired registrations." );
	}
}
