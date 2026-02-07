<?php
/**
 * Revenue Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Services\AnalyticsService;

/**
 * Class RevenuePage
 */
class RevenuePage {

	/**
	 * Initialize the page.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_organizer_export_revenue', array( __CLASS__, 'handle_export' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public static function add_menu() {
		add_submenu_page(
			'edit.php?post_type=organizer_event',
			__( 'Revenue', 'organizer' ),
			__( 'Revenue', 'organizer' ),
			'manage_options',
			'organizer-revenue',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		$analytics = new AnalyticsService();
		$stats     = $analytics->get_revenue_stats();
		$by_event  = $analytics->get_revenue_by_event();
		$currency  = get_option( 'organizer_currency', 'USD' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Event Revenue', 'organizer' ); ?></h1>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=organizer_export_revenue' ), 'organizer_export_revenue', 'organizer_revenue_nonce' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Export CSV', 'organizer' ); ?>
			</a>

			<div class="organizer-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
				<div class="organizer-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
					<span class="organizer-stat-value" style="font-size: 24px; font-weight: bold; display: block;"><?php echo esc_html( number_format( $stats['total_revenue'], 2 ) . ' ' . $currency ); ?></span>
					<span class="organizer-stat-label" style="color: #666; text-transform: uppercase; font-size: 12px;"><?php esc_html_e( 'Total Revenue', 'organizer' ); ?></span>
				</div>
				<div class="organizer-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
					<span class="organizer-stat-value" style="font-size: 24px; font-weight: bold; display: block;"><?php echo esc_html( number_format( $stats['avg_revenue'], 2 ) . ' ' . $currency ); ?></span>
					<span class="organizer-stat-label" style="color: #666; text-transform: uppercase; font-size: 12px;"><?php esc_html_e( 'Avg Revenue / Attendee', 'organizer' ); ?></span>
				</div>
			</div>

			<h3><?php esc_html_e( 'Revenue by Event', 'organizer' ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Event', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Registrations', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Revenue', 'organizer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $by_event ) ) : ?>
						<?php foreach ( $by_event as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['event_title'] ); ?></td>
								<td><?php echo esc_html( $row['count'] ); ?></td>
								<td><?php echo esc_html( number_format( $row['revenue'], 2 ) . ' ' . $currency ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="3"><?php esc_html_e( 'No revenue data found.', 'organizer' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Handle CSV export.
	 */
	public static function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'organizer' ) );
		}
		check_admin_referer( 'organizer_export_revenue', 'organizer_revenue_nonce' );

		$analytics = new AnalyticsService();
		$by_event  = $analytics->get_revenue_by_event();
		$filename  = 'revenue-report-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'Event ID', 'Event Title', 'Registrations', 'Revenue' ) );

		foreach ( $by_event as $row ) {
			fputcsv( $output, $row );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $output );
		exit;
	}
}
