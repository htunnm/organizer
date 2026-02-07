<?php
/**
 * Import Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Services\SeriesGenerator;

/**
 * Class ImportPage
 */
class ImportPage {

	/**
	 * Initialize the page.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_organizer_import_events', array( __CLASS__, 'handle_import' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public static function add_menu() {
		add_submenu_page(
			'edit.php?post_type=organizer_event',
			__( 'Import Events', 'organizer' ),
			__( 'Import Events', 'organizer' ),
			'manage_options',
			'organizer-import',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Import Events', 'organizer' ); ?></h1>
			<p><?php esc_html_e( 'Upload a CSV file to create events in bulk.', 'organizer' ); ?></p>
			<p>
				<?php esc_html_e( 'CSV Columns: Title, Content, Start Date (Y-m-d), Start Time (H:i), Duration (min), Price, Capacity, Custom Fields (JSON)', 'organizer' ); ?>
			</p>
			
			<?php
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['imported'] ) ) {
				/* translators: %d: Number of events imported */
				echo '<div class="notice notice-success"><p>' . sprintf( esc_html__( 'Successfully imported %d events.', 'organizer' ), absint( $_GET['imported'] ) ) . '</p></div>';
			}
			if ( isset( $_GET['error'] ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Error importing file.', 'organizer' ) . '</p></div>';
			}
			// phpcs:enable
			?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="organizer_import_events">
				<?php wp_nonce_field( 'organizer_import_nonce', 'organizer_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="organizer_import_file"><?php esc_html_e( 'CSV File', 'organizer' ); ?></label></th>
						<td><input type="file" name="organizer_import_file" id="organizer_import_file" accept=".csv" required></td>
					</tr>
				</table>
				<?php submit_button( __( 'Upload and Import', 'organizer' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle import.
	 */
	public static function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'organizer' ) );
		}
		check_admin_referer( 'organizer_import_nonce', 'organizer_nonce' );

		if ( empty( $_FILES['organizer_import_file']['tmp_name'] ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-import&error=1' ) );
			exit;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$file   = fopen( $_FILES['organizer_import_file']['tmp_name'], 'r' );
		$header = fgetcsv( $file ); // Skip header.
		$count  = 0;

		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( ( $row = fgetcsv( $file ) ) !== false ) {
			// Map columns: Title, Content, Start Date, Start Time, Duration, Price, Capacity, Custom Fields.
			$title         = isset( $row[0] ) ? sanitize_text_field( $row[0] ) : '';
			$content       = isset( $row[1] ) ? wp_kses_post( $row[1] ) : '';
			$start_date    = isset( $row[2] ) ? sanitize_text_field( $row[2] ) : '';
			$start_time    = isset( $row[3] ) ? sanitize_text_field( $row[3] ) : '';
			$duration      = isset( $row[4] ) ? intval( $row[4] ) : 60;
			$price         = isset( $row[5] ) ? floatval( $row[5] ) : 0;
			$capacity      = isset( $row[6] ) ? intval( $row[6] ) : 0;
			$custom_fields = isset( $row[7] ) ? json_decode( $row[7], true ) : array();

			if ( empty( $title ) ) {
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => $content,
					'post_type'    => 'organizer_event',
					'post_status'  => 'publish',
				)
			);

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_organizer_event_price', $price );
				update_post_meta( $post_id, '_organizer_event_capacity', $capacity );

				if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
					// Sanitize custom fields structure.
					$clean_fields = array_map(
						function ( $field ) {
							return array_map( 'sanitize_text_field', $field );
						},
						$custom_fields
					);
					update_post_meta( $post_id, '_organizer_custom_fields', array_values( $clean_fields ) );
				}

				// Generate single session.
				$rules = array(
					'type'             => 'single',
					'start_date'       => $start_date,
					'start_time'       => $start_time,
					'duration_minutes' => $duration,
				);
				update_post_meta( $post_id, '_organizer_recurrence_rules', $rules );

				$generator = new SeriesGenerator();
				$generator->generate_sessions( $post_id, $rules );

				++$count;
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $file );

		wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-import&imported=' . $count ) );
		exit;
	}
}
