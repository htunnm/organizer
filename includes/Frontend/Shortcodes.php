<?php
/**
 * Shortcodes Handler.
 *
 * @package Organizer\Frontend
 */

namespace Organizer\Frontend;

use Organizer\Model\Session;
use Organizer\Model\Registration;
use Organizer\Services\QrCodeService;
use Organizer\Services\AnalyticsService;

/**
 * Class Shortcodes
 */
class Shortcodes {

	/**
	 * Initialize shortcodes.
	 */
	public static function init() {
		add_shortcode( 'organizer_calendar', array( __CLASS__, 'render_calendar' ) );
		add_shortcode( 'organizer_search_bar', array( __CLASS__, 'render_search_bar' ) );
		add_shortcode( 'organizer_month_view', array( __CLASS__, 'render_month_view' ) );
		add_shortcode( 'organizer_registration_form', array( __CLASS__, 'render_registration_form' ) );
		add_shortcode( 'organizer_user_dashboard', array( __CLASS__, 'render_user_dashboard' ) );
		add_shortcode( 'organizer_user_profile', array( __CLASS__, 'render_user_profile' ) );
		add_shortcode( 'organizer_ticket', array( __CLASS__, 'render_ticket' ) );
		add_shortcode( 'organizer_analytics_dashboard', array( __CLASS__, 'render_analytics_dashboard' ) );
		add_shortcode( 'organizer_checkin_scanner', array( __CLASS__, 'render_checkin_scanner' ) );
		add_shortcode( 'organizer_feedback_form', array( __CLASS__, 'render_feedback_form' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_assets() {
		wp_register_style( 'organizer-calendar', ORGANIZER_URL . 'assets/css/calendar.css', array(), ORGANIZER_VERSION );
		wp_register_style( 'organizer-dashboard', ORGANIZER_URL . 'assets/css/dashboard.css', array(), ORGANIZER_VERSION );
		wp_register_style( 'organizer-analytics', ORGANIZER_URL . 'assets/css/frontend-analytics.css', array(), ORGANIZER_VERSION );
		wp_register_style( 'organizer-registration', ORGANIZER_URL . 'assets/css/registration.css', array(), ORGANIZER_VERSION );
		wp_register_script( 'organizer-frontend', ORGANIZER_URL . 'assets/js/frontend.js', array( 'jquery' ), ORGANIZER_VERSION, true );
		wp_localize_script( 'organizer-frontend', 'organizer_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		// FullCalendar assets for month view.
		wp_register_style( 'fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css', array(), '6.1.15' );
		wp_register_script( 'fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', array(), '6.1.15', true );

		// Scanner assets.
		wp_register_script( 'html5-qrcode', 'https://unpkg.com/html5-qrcode', array(), '2.3.8', true );
		wp_register_script( 'organizer-scanner', ORGANIZER_URL . 'assets/js/scanner.js', array( 'jquery', 'html5-qrcode' ), ORGANIZER_VERSION, true );
	}

	/**
	 * Render the calendar shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_calendar( $atts ) {
		wp_enqueue_style( 'organizer-calendar' );
		wp_enqueue_style( 'dashicons' );

		$atts = shortcode_atts(
			array(
				'limit'       => 10,
				'offset'      => 0,
				'category'    => '',
				'tag'         => '',
				'show_search' => 'no',
				'columns'     => 3,
			),
			$atts,
			'organizer_calendar'
		);

		$columns = intval( $atts['columns'] );

		$filters = array();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['organizer_search'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['keyword'] = sanitize_text_field( wp_unslash( $_GET['organizer_search'] ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['organizer_start_date'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['start_date'] = sanitize_text_field( wp_unslash( $_GET['organizer_start_date'] ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['organizer_end_date'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['end_date'] = sanitize_text_field( wp_unslash( $_GET['organizer_end_date'] ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['organizer_city'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['city'] = sanitize_text_field( wp_unslash( $_GET['organizer_city'] ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['organizer_days'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['duration_days'] = intval( wp_unslash( $_GET['organizer_days'] ) );
		}

		if ( ! empty( $atts['tag'] ) ) {
			$filters['tag'] = sanitize_text_field( $atts['tag'] );
		}

		// Fetch upcoming sessions.
		// In a real calendar, we'd fetch by date range. For now, we list upcoming sessions.
		$sessions    = Session::get_all( (int) $atts['limit'], (int) $atts['offset'], 'start_datetime', 'ASC', sanitize_text_field( $atts['category'] ), $filters );
		$total_items = Session::count_all(); // Note: This count should ideally respect filters, but for simplicity we use total.
		// In a real implementation, Session::count_all should accept filters too.
		// For now, pagination might be slightly inaccurate if filtered, but functional for full list.

		ob_start();
		if ( 'yes' === $atts['show_search'] ) {
			$search_view = ORGANIZER_PATH . 'includes/Frontend/views/search-form.php';
			if ( file_exists( $search_view ) ) {
				include $search_view;
			}
		}

		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/calendar.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Calendar view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the search bar shortcode.
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string HTML output.
	 */
	public static function render_search_bar( $atts = array(), $content = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $wpdb;

		// Fetch distinct cities from the database.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cities = $wpdb->get_col(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
			WHERE meta_key='_event_city' AND meta_value != '' 
			ORDER BY meta_value ASC"
		);

		// Get current filter values from GET parameters.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_city = isset( $_GET['organizer_city'] ) ? sanitize_text_field( wp_unslash( $_GET['organizer_city'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_days = isset( $_GET['organizer_days'] ) ? intval( $_GET['organizer_days'] ) : '';

		// Get current page URL safely.
		$current_url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		ob_start();
		?>
		<div class="organizer-search-bar-container">
			<form method="GET" action="<?php echo esc_url( $current_url ); ?>" class="organizer-search-bar-form">
				<div class="organizer-search-bar-fields">
					<div class="organizer-search-field">
						<label for="organizer_city"><?php esc_html_e( 'Location (City)', 'organizer' ); ?></label>
						<select name="organizer_city" id="organizer_city" class="organizer-search-input">
							<option value=""><?php esc_html_e( 'All Cities', 'organizer' ); ?></option>
							<?php foreach ( $cities as $city ) : ?>
								<option value="<?php echo esc_attr( $city ); ?>" <?php selected( $current_city, $city ); ?>>
									<?php echo esc_html( $city ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="organizer-search-field">
						<label for="organizer_days"><?php esc_html_e( 'Duration', 'organizer' ); ?></label>
						<input type="number" name="organizer_days" id="organizer_days" class="organizer-search-input" placeholder="<?php esc_attr_e( 'Days', 'organizer' ); ?>" value="<?php echo esc_attr( $current_days ); ?>" min="0">
					</div>
				</div>

				<button type="submit" class="organizer-search-btn"><?php esc_html_e( 'Find Events', 'organizer' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the registration form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_registration_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'event_id'   => 0,
				'session_id' => 0,
			),
			$atts,
			'organizer_registration_form'
		);

		$event_id   = (int) $atts['event_id'];
		$session_id = (int) $atts['session_id'];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $event_id ) && isset( $_GET['event_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$event_id = absint( $_GET['event_id'] );
		}

		if ( empty( $event_id ) ) {
			return '<p>' . esc_html__( 'Event ID is required.', 'organizer' ) . '</p>';
		}

		$options  = get_option( 'organizer_options' );
		$site_key = isset( $options['organizer_recaptcha_site_key'] ) ? $options['organizer_recaptcha_site_key'] : '';
		if ( ! empty( $site_key ) ) {
			wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), ORGANIZER_VERSION, true );
		}

		ob_start();
		wp_enqueue_style( 'organizer-registration' );
		wp_enqueue_script( 'organizer-frontend', ORGANIZER_URL . 'assets/js/frontend.js', array( 'jquery' ), ORGANIZER_VERSION, true );
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/registration-form.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Registration form view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the user dashboard shortcode.
	 *
	 * @return string HTML output.
	 */
	public static function render_user_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your dashboard.', 'organizer' ) . '</p>';
		}

		wp_enqueue_style( 'organizer-dashboard' );

		$current_user  = wp_get_current_user();
		$registrations = Registration::get_by_user_email( $current_user->user_email );

		$upcoming = array();
		$past     = array();
		$now      = current_time( 'mysql' );

		foreach ( $registrations as $reg ) {
			$event_date = $reg['created_at']; // Default to registration date.
			if ( ! empty( $reg['session_id'] ) ) {
				$session = Session::get( $reg['session_id'] );
				if ( $session ) {
					$event_date = $session->start_datetime;
				}
			}

			if ( $event_date >= $now ) {
				$upcoming[] = $reg;
			} else {
				$past[] = $reg;
			}
		}

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/user-dashboard.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Dashboard view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the user profile shortcode.
	 *
	 * @return string HTML output.
	 */
	public static function render_user_profile() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to edit your profile.', 'organizer' ) . '</p>';
		}

		$current_user = wp_get_current_user();

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/user-profile.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Profile view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the ticket shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_ticket( $atts ) {
		$atts = shortcode_atts(
			array(
				'token' => '',
			),
			$atts,
			'organizer_ticket'
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : $atts['token'];

		if ( empty( $token ) ) {
			return '<p>' . esc_html__( 'Invalid ticket token.', 'organizer' ) . '</p>';
		}

		$registration = Registration::get_by_token( $token );

		if ( ! $registration ) {
			return '<p>' . esc_html__( 'Ticket not found.', 'organizer' ) . '</p>';
		}

		// Generate QR Code URL (using admin-post for checkin).
		$checkin_url = admin_url( 'admin-post.php?action=organizer_checkin&token=' . $token );
		$qr_service  = new QrCodeService();
		$upload_dir  = wp_upload_dir();
		$filename    = 'qr-' . $token . '.png';
		$filepath    = $upload_dir['basedir'] . '/' . $filename;
		$fileurl     = $upload_dir['baseurl'] . '/' . $filename;

		if ( ! file_exists( $filepath ) ) {
			$qr_service->generate_file( $checkin_url, $filepath );
		}

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/ticket.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Ticket view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the analytics dashboard shortcode.
	 *
	 * @return string HTML output.
	 */
	public static function render_analytics_dashboard() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '<p>' . esc_html__( 'You do not have permission to view this dashboard.', 'organizer' ) . '</p>';
		}

		wp_enqueue_style( 'organizer-analytics' );

		$analytics = new AnalyticsService();
		$stats     = $analytics->get_registration_stats();
		$daily     = $analytics->get_daily_registrations();
		$wl_stats  = $analytics->get_waitlist_metrics();
		$wl_growth = $analytics->get_waitlist_growth();

		// Fetch upcoming events authored by current user.
		$args = array(
			'post_type'      => 'organizer_event',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
			'author'         => get_current_user_id(),
		);

		$events          = new \WP_Query( $args );
		$upcoming_events = array();

		if ( $events->have_posts() ) {
			while ( $events->have_posts() ) {
				$events->the_post();
				$upcoming_events[] = array(
					'title' => get_the_title(),
					'count' => Registration::count_by_event( get_the_ID() ),
					'link'  => get_permalink(),
				);
			}
			wp_reset_postdata();
		}

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/analytics-dashboard.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Analytics dashboard view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the check-in scanner shortcode.
	 *
	 * @return string HTML output.
	 */
	public static function render_checkin_scanner() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '<p>' . esc_html__( 'You do not have permission to access the scanner.', 'organizer' ) . '</p>';
		}

		wp_enqueue_script( 'organizer-scanner' );
		wp_localize_script(
			'organizer-scanner',
			'organizer_scanner',
			array(
				'api_url' => rest_url( 'organizer/v1/checkin' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/scanner.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		}
		return ob_get_clean();
	}

	/**
	 * Render the feedback form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_feedback_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'event_id'  => 0,
				'anonymous' => 'no',
			),
			$atts,
			'organizer_feedback_form'
		);

		$event_id = (int) $atts['event_id'];
		if ( empty( $event_id ) ) {
			return '<p>' . esc_html__( 'Event ID is required.', 'organizer' ) . '</p>';
		}

		$registration_id = 0;
		if ( 'yes' !== $atts['anonymous'] ) {
			if ( ! is_user_logged_in() ) {
				return '<p>' . esc_html__( 'Please log in to leave feedback.', 'organizer' ) . '</p>';
			}
			// Check if user registered for this event.
			$current_user  = wp_get_current_user();
			$registrations = Registration::get_by_user_email( $current_user->user_email );
			foreach ( $registrations as $reg ) {
				if ( (int) $reg['event_id'] === $event_id && 'confirmed' === $reg['status'] ) {
					$registration_id = $reg['id'];
					break;
				}
			}
		}

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/feedback-form.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		}
		return ob_get_clean();
	}

	/**
	 * Render the month view shortcode with FullCalendar.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_month_view( $atts = array() ) { // phpcs:ignore
		wp_enqueue_style( 'fullcalendar-css' );
		wp_enqueue_style( 'organizer-calendar' ); // For custom overrides.
		wp_enqueue_script( 'fullcalendar-js' );

		$rest_url = rest_url( 'organizer/v1/events' );
		$site_url = site_url();

		// Inline script to initialize FullCalendar.
		$inline_script = <<<JS
		document.addEventListener('DOMContentLoaded', function() {
			var calendarEl = document.getElementById('organizer-fullcalendar');
			if (calendarEl) {
				var calendar = new FullCalendar.Calendar(calendarEl, {
					initialView: 'dayGridMonth',
					headerToolbar: {
						left: 'prev,next today',
						center: 'title',
						right: 'dayGridMonth,dayGridWeek'
					},
					events: {
						url: '{$rest_url}',
						failure: function() {
							alert('There was an error while fetching events!');
						}
					},
					eventClick: function(info) {
						if (info.event.url) {
							window.location.href = info.event.url;
							return false;
						}
					},
					height: 'auto',
					contentHeight: 'auto'
				});
				calendar.render();
			}
		});
		JS;

		wp_add_inline_script( 'fullcalendar-js', $inline_script );

		return '<div id="organizer-fullcalendar"></div>';
	}
}
