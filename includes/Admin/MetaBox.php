<?php
/**
 * Event Meta Box.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Services\SeriesGenerator;
use Organizer\Model\Session;
use Organizer\Model\Feedback;

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
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
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
	 * Enqueue scripts.
	 *
	 * @param string $hook Admin page hook.
	 */
	public static function enqueue_scripts( $hook ) {
		if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
			wp_enqueue_script( 'organizer-admin', ORGANIZER_URL . 'assets/js/admin.js', array( 'jquery' ), ORGANIZER_VERSION, true );
		}
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

		$custom_fields = get_post_meta( $post->ID, '_organizer_custom_fields', true );
		if ( ! is_array( $custom_fields ) ) {
			$custom_fields = array();
		}
		$price = get_post_meta( $post->ID, '_organizer_event_price', true );
		$venue = get_post_meta( $post->ID, '_organizer_event_venue', true );
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
		<p>
			<label for="organizer_event_price"><?php esc_html_e( 'Price:', 'organizer' ); ?></label>
			<input type="number" step="0.01" name="organizer_event_price" id="organizer_event_price" value="<?php echo esc_attr( $price ); ?>">
		</p>
		<p>
			<label for="organizer_event_venue"><?php esc_html_e( 'Venue:', 'organizer' ); ?></label>
			<input type="text" name="organizer_event_venue" id="organizer_event_venue" value="<?php echo esc_attr( $venue ); ?>" class="regular-text">
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
		// Custom Fields Section.
		?>
		<hr>
		<h4><?php esc_html_e( 'Custom Fields', 'organizer' ); ?></h4>
		<div id="organizer-custom-fields-container">
			<?php
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $index => $field ) {
					self::render_custom_field_row( $index, $field );
				}
			}
			?>
		</div>
		<p>
			<button type="button" class="button" id="organizer-add-custom-field"><?php esc_html_e( 'Add Field', 'organizer' ); ?></button>
		</p>
		<script type="text/template" id="organizer-custom-field-template">
			<?php self::render_custom_field_row( 'INDEX', array() ); ?>
		</script>
		<?php
		// Email Notifications Section.
		?>
		<hr>
		<details>
			<summary style="cursor: pointer; font-weight: bold; padding: 5px 0;"><?php esc_html_e( 'Email Notifications (Override Defaults)', 'organizer' ); ?></summary>
			<div style="padding: 10px; border: 1px solid #ddd; margin-top: 5px;">
				<?php
				$email_types = array(
					'registration_confirmation' => __( 'Registration Confirmation', 'organizer' ),
					'waitlist_confirmation'     => __( 'Waitlist Confirmation', 'organizer' ),
					'waitlist_promotion'        => __( 'Waitlist Promotion', 'organizer' ),
					'event_reminder'            => __( 'Event Reminder', 'organizer' ),
				);

				foreach ( $email_types as $key => $label ) {
					$subject = get_post_meta( $post->ID, "_organizer_email_{$key}_subject", true );
					$message = get_post_meta( $post->ID, "_organizer_email_{$key}_message", true );
					?>
					<div style="margin-bottom: 15px;">
						<strong><?php echo esc_html( $label ); ?></strong>
						<p>
							<label for="organizer_email_<?php echo esc_attr( $key ); ?>_subject"><?php esc_html_e( 'Subject:', 'organizer' ); ?></label><br>
							<input type="text" name="organizer_email[<?php echo esc_attr( $key ); ?>][subject]" id="organizer_email_<?php echo esc_attr( $key ); ?>_subject" value="<?php echo esc_attr( $subject ); ?>" class="widefat">
						</p>
						<p>
							<label for="organizer_email_<?php echo esc_attr( $key ); ?>_message"><?php esc_html_e( 'Message:', 'organizer' ); ?></label><br>
							<textarea name="organizer_email[<?php echo esc_attr( $key ); ?>][message]" id="organizer_email_<?php echo esc_attr( $key ); ?>_message" rows="3" class="widefat"><?php echo esc_textarea( $message ); ?></textarea>
						</p>
					</div>
					<?php
				}
				?>
			</div>
		</details>

		<?php
		// Feedback Section.
		$avg_rating = Feedback::get_average_rating( $post->ID );
		$feedbacks  = Feedback::get_by_event( $post->ID );
		?>
		<hr>
		<h4><?php esc_html_e( 'Feedback', 'organizer' ); ?></h4>
		<p><strong><?php esc_html_e( 'Average Rating:', 'organizer' ); ?></strong> <?php echo esc_html( number_format( $avg_rating, 1 ) ); ?> / 5</p>
		<?php if ( ! empty( $feedbacks ) ) : ?>
			<ul>
				<?php foreach ( $feedbacks as $feedback ) : ?>
					<li>
						<strong><?php echo esc_html( $feedback['rating'] ); ?>/5</strong> - 
						<?php echo esc_html( $feedback['comment'] ); ?>
						<small>(<?php echo esc_html( $feedback['created_at'] ); ?>)</small>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a custom field row.
	 *
	 * @param int|string $index Index.
	 * @param array      $field Field data.
	 */
	private static function render_custom_field_row( $index, $field ) {
		$label    = isset( $field['label'] ) ? $field['label'] : '';
		$type     = isset( $field['type'] ) ? $field['type'] : 'text';
		$required = isset( $field['required'] ) ? $field['required'] : 'no';
		?>
		<div class="organizer-custom-field-row" style="margin-bottom: 10px; border: 1px solid #ddd; padding: 10px;">
			<label><?php esc_html_e( 'Label:', 'organizer' ); ?> <input type="text" name="organizer_custom_fields[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>"></label>
			<label><?php esc_html_e( 'Type:', 'organizer' ); ?> <select name="organizer_custom_fields[<?php echo esc_attr( $index ); ?>][type]"><option value="text" <?php selected( $type, 'text' ); ?>>Text</option><option value="checkbox" <?php selected( $type, 'checkbox' ); ?>>Checkbox</option></select></label>
			<label><?php esc_html_e( 'Required:', 'organizer' ); ?> <select name="organizer_custom_fields[<?php echo esc_attr( $index ); ?>][required]"><option value="no" <?php selected( $required, 'no' ); ?>>No</option><option value="yes" <?php selected( $required, 'yes' ); ?>>Yes</option></select></label>
			<button type="button" class="button organizer-remove-custom-field"><?php esc_html_e( 'Remove', 'organizer' ); ?></button>
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

		if ( isset( $_POST['organizer_event_price'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			update_post_meta( $post_id, '_organizer_event_price', sanitize_text_field( wp_unslash( $_POST['organizer_event_price'] ) ) );
		}

		if ( isset( $_POST['organizer_event_venue'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			update_post_meta( $post_id, '_organizer_event_venue', sanitize_text_field( wp_unslash( $_POST['organizer_event_venue'] ) ) );
		}

		if ( isset( $_POST['organizer_custom_fields'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw_custom_fields = wp_unslash( $_POST['organizer_custom_fields'] );

			$custom_fields = array_map(
				function ( $field ) {
					return array_map( 'sanitize_text_field', $field );
				},
				$raw_custom_fields
			);
			update_post_meta( $post_id, '_organizer_custom_fields', array_values( $custom_fields ) );
		} else {
			delete_post_meta( $post_id, '_organizer_custom_fields' );
		}

		if ( isset( $_POST['organizer_email'] ) && is_array( $_POST['organizer_email'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$organizer_emails = wp_unslash( $_POST['organizer_email'] );
			foreach ( $organizer_emails as $key => $data ) {
				$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : '';
				$message = isset( $data['message'] ) ? sanitize_textarea_field( $data['message'] ) : '';
				update_post_meta( $post_id, "_organizer_email_{$key}_subject", $subject );
				update_post_meta( $post_id, "_organizer_email_{$key}_message", $message );
			}
		}
	}
}
