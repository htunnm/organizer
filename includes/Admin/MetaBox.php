<?php
/**
 * Event Meta Box.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Services\SeriesGenerator;
use Organizer\Model\Session;

/**
 * Class MetaBox
 */
class MetaBox {

	/**
	 * Initialize the meta box.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_post' ) );
	}

	/**
	 * Add meta boxes.
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'organizer_event_schedule',
			__( 'Event Schedule', 'organizer' ),
			array( __CLASS__, 'render_meta_box' ),
			'organizer_event',
			'normal',
			'high'
		);
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public static function render_meta_box( $post ) {
		$rules = get_post_meta( $post->ID, '_organizer_recurrence_rules', true );
		if ( ! is_array( $rules ) ) {
			$rules = array();
		}

		wp_nonce_field( 'organizer_save_schedule', 'organizer_schedule_nonce' );

		$type       = isset( $rules['type'] ) ? $rules['type'] : 'single';
		$start_date = isset( $rules['start_date'] ) ? $rules['start_date'] : '';
		$start_time = isset( $rules['start_time'] ) ? $rules['start_time'] : '';
		$duration   = isset( $rules['duration_minutes'] ) ? $rules['duration_minutes'] : 60;
		$interval   = isset( $rules['interval'] ) ? $rules['interval'] : 1;
		$end_type   = isset( $rules['end_type'] ) ? $rules['end_type'] : 'count';
		$end_count  = isset( $rules['end_count'] ) ? $rules['end_count'] : 5;
		$end_date   = isset( $rules['end_date'] ) ? $rules['end_date'] : '';
		?>
		<p>
			<label for="organizer_recurrence_type"><?php esc_html_e( 'Recurrence Type:', 'organizer' ); ?></label>
			<select name="organizer_recurrence_rules[type]" id="organizer_recurrence_type">
				<option value="single" <?php selected( $type, 'single' ); ?>><?php esc_html_e( 'Single', 'organizer' ); ?></option>
				<option value="weekly" <?php selected( $type, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'organizer' ); ?></option>
			</select>
		</p>
		<p>
			<label for="organizer_start_date"><?php esc_html_e( 'Start Date:', 'organizer' ); ?></label>
			<input type="date" name="organizer_recurrence_rules[start_date]" id="organizer_start_date" value="<?php echo esc_attr( $start_date ); ?>">
			<label for="organizer_start_time"><?php esc_html_e( 'Start Time:', 'organizer' ); ?></label>
			<input type="time" name="organizer_recurrence_rules[start_time]" id="organizer_start_time" value="<?php echo esc_attr( $start_time ); ?>">
		</p>
		<p>
			<label for="organizer_duration"><?php esc_html_e( 'Duration (minutes):', 'organizer' ); ?></label>
			<input type="number" name="organizer_recurrence_rules[duration_minutes]" id="organizer_duration" value="<?php echo esc_attr( $duration ); ?>">
		</p>
		<div id="organizer_recurrence_options">
			<p>
				<label for="organizer_interval"><?php esc_html_e( 'Interval (weeks):', 'organizer' ); ?></label>
				<input type="number" name="organizer_recurrence_rules[interval]" id="organizer_interval" value="<?php echo esc_attr( $interval ); ?>" min="1">
			</p>
			<p>
				<label for="organizer_end_type"><?php esc_html_e( 'End Condition:', 'organizer' ); ?></label>
				<select name="organizer_recurrence_rules[end_type]" id="organizer_end_type">
					<option value="count" <?php selected( $end_type, 'count' ); ?>><?php esc_html_e( 'After N occurrences', 'organizer' ); ?></option>
					<option value="date" <?php selected( $end_type, 'date' ); ?>><?php esc_html_e( 'By Date', 'organizer' ); ?></option>
				</select>
			</p>
			<p>
				<label for="organizer_end_count"><?php esc_html_e( 'End Count:', 'organizer' ); ?></label>
				<input type="number" name="organizer_recurrence_rules[end_count]" id="organizer_end_count" value="<?php echo esc_attr( $end_count ); ?>">
			</p>
			<p>
				<label for="organizer_end_date"><?php esc_html_e( 'End Date:', 'organizer' ); ?></label>
				<input type="date" name="organizer_recurrence_rules[end_date]" id="organizer_end_date" value="<?php echo esc_attr( $end_date ); ?>">
			</p>
		</div>
		<?php
	}

	/**
	 * Save post meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_post( $post_id ) {
		if ( ! isset( $_POST['organizer_schedule_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['organizer_schedule_nonce'] ) ), 'organizer_save_schedule' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['organizer_recurrence_rules'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$rules = array_map( 'sanitize_text_field', wp_unslash( $_POST['organizer_recurrence_rules'] ) );
			update_post_meta( $post_id, '_organizer_recurrence_rules', $rules );

			// Regenerate sessions.
			// Note: This deletes existing sessions for this event.
			Session::delete_by_event( $post_id );
			$generator = new SeriesGenerator();
			$generator->generate_sessions( $post_id, $rules );
		}
	}
}
