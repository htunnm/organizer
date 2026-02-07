<?php
/**
 * PHPUnit bootstrap file.
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// If we are running in a full WP environment (e.g. wp-env), load WP.
// For basic unit tests in this scaffold, we might mock WP functions if needed.
// This is a placeholder for the WP test suite loader.

if ( getenv( 'WP_TESTS_DIR' ) ) {
	require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';
	require_once getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
} else {
	// Helper class for mocking WP functions
	class WPMocks {
		public static $actions           = array();
		public static $options_pages     = array();
		public static $post_types        = array();
		public static $db_delta          = array();
		public static $rest_routes       = array();
		public static $sent_emails       = array();
		public static $post_meta         = array();
		public static $dashboard_widgets = array();
		public static $shortcodes        = array();
		public static $styles            = array();
		public static $taxonomies        = array();
		public static $transients        = array();
		public static $blocks            = array();
		public static $widgets           = array();

		public static function reset() {
			self::$actions           = array();
			self::$options_pages     = array();
			self::$post_types        = array();
			self::$db_delta          = array();
			self::$rest_routes       = array();
			self::$sent_emails       = array();
			self::$post_meta         = array();
			self::$dashboard_widgets = array();
			self::$shortcodes        = array();
			self::$styles            = array();
			self::$taxonomies        = array();
			self::$transients        = array();
			self::$blocks            = array();
			self::$widgets           = array();
		}
	}

	// Mock WP functions for basic unit testing without WP
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', dirname( __DIR__ ) . '/' );
	}
	if ( ! defined( 'OBJECT' ) ) {
		define( 'OBJECT', 'OBJECT' );
	}
	if ( ! defined( 'OBJECT_K' ) ) {
		define( 'OBJECT_K', 'OBJECT_K' );
	}
	if ( ! defined( 'ARRAY_A' ) ) {
		define( 'ARRAY_A', 'ARRAY_A' );
	}
	if ( ! defined( 'ARRAY_N' ) ) {
		define( 'ARRAY_N', 'ARRAY_N' );
	}
	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
			WPMocks::$actions[] = array(
				'tag'           => $tag,
				'callback'      => $callback,
				'priority'      => $priority,
				'accepted_args' => $accepted_args,
			);
		}
	}
	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
			return true;
		}
	}
	if ( ! function_exists( 'remove_filter' ) ) {
		function remove_filter( $tag, $callback, $priority = 10 ) {
			return true;
		}
	}
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook() {}
	}
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook() {}
	}
	if ( ! function_exists( 'plugin_dir_path' ) ) {
		function plugin_dir_path( $file ) {
			return dirname( $file ) . '/'; }
	}
	if ( ! function_exists( 'plugin_dir_url' ) ) {
		function plugin_dir_url() {
			return ''; }
	}

	// Mocks for Settings and Admin
	if ( ! function_exists( 'add_options_page' ) ) {
		function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
			WPMocks::$options_pages[] = array(
				'page_title' => $page_title,
				'menu_title' => $menu_title,
				'capability' => $capability,
				'menu_slug'  => $menu_slug,
				'function'   => $function,
			);
		}
	}

	if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
		function wp_add_dashboard_widget( $widget_id, $widget_name, $callback, $control_callback = null, $callback_args = null ) {
			WPMocks::$dashboard_widgets[] = array(
				'widget_id'   => $widget_id,
				'widget_name' => $widget_name,
				'callback'    => $callback,
			);
		}
	}

	if ( ! function_exists( 'add_shortcode' ) ) {
		function add_shortcode( $tag, $callback ) {
			WPMocks::$shortcodes[ $tag ] = $callback;
		}
	}

	if ( ! function_exists( 'shortcode_atts' ) ) {
		function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
			return array_merge( $pairs, (array) $atts );
		}
	}

	if ( ! function_exists( 'wp_register_style' ) ) {
		function wp_register_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
			WPMocks::$styles[ $handle ] = $src;
		}
	}

	if ( ! function_exists( 'wp_enqueue_style' ) ) {
		function wp_enqueue_style( $handle ) {}
	}

	if ( ! function_exists( 'wp_enqueue_script' ) ) {
		function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {}
	}

	if ( ! function_exists( 'date_i18n' ) ) {
		function date_i18n( $format, $timestamp_with_offset = false, $gmt = false ) {
			return date( $format, $timestamp_with_offset );
		}
	}

	if ( ! function_exists( 'get_the_title' ) ) {
		function get_the_title( $post = 0 ) {
			return 'Event Title ' . $post;
		}
	}

	if ( ! function_exists( 'get_permalink' ) ) {
		function get_permalink( $post = 0 ) {
			return 'http://example.com/post/' . $post;
		}
	}

	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = 'default' ) {
			return $text;
		}
	}

	if ( ! function_exists( '_x' ) ) {
		function _x( $text, $context, $domain = 'default' ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_html_e' ) ) {
		function esc_html_e( $text ) {
			echo $text;
		}
	}

	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $text ) {
			return $text;
		}
	}

	if ( ! function_exists( 'esc_attr_e' ) ) {
		function esc_attr_e( $text, $domain = 'default' ) {
			echo $text;
		}
	}

	if ( ! function_exists( 'esc_attr__' ) ) {
		function esc_attr__( $text, $domain = 'default' ) {
			return $text;
		}
	}

	if ( ! function_exists( 'register_setting' ) ) {
		function register_setting() {}
	}
	if ( ! function_exists( 'add_settings_section' ) ) {
		function add_settings_section() {}
	}
	if ( ! function_exists( 'add_settings_field' ) ) {
		function add_settings_field() {}
	}
	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $option, $default = false ) {
			if ( 'date_format' === $option ) {
				return 'Y-m-d';
			}
			if ( 'time_format' === $option ) {
				return 'H:i';
			}
			if ( 'organizer_options' === $option && false === $default ) {
				return array();
			}
			return $default;
		}
	}
	if ( ! function_exists( 'wp_enqueue_scripts' ) ) {
		function wp_enqueue_scripts() {}
	}
	if ( ! function_exists( 'admin_enqueue_scripts' ) ) {
		function admin_enqueue_scripts() {}
	}

	if ( ! function_exists( 'current_user_can' ) ) {
		function current_user_can() {
			return true; }
	}
	if ( ! function_exists( 'register_post_type' ) ) {
		function register_post_type( $post_type, $args = array() ) {
			WPMocks::$post_types[ $post_type ] = $args;
		}
	}

	if ( ! function_exists( 'register_block_type' ) ) {
		function register_block_type( $name, $args = array() ) {
			WPMocks::$blocks[ $name ] = $args;
		}
	}

	if ( ! function_exists( 'register_taxonomy' ) ) {
		function register_taxonomy( $taxonomy, $object_type, $args = array() ) {
			WPMocks::$taxonomies[ $taxonomy ] = array(
				'object_type' => $object_type,
				'args'        => $args,
			);
		}
	}

	if ( ! function_exists( 'register_widget' ) ) {
		function register_widget( $widget_class ) {
			WPMocks::$widgets[] = $widget_class;
		}
	}

	if ( ! function_exists( 'wp_tag_cloud' ) ) {
		function wp_tag_cloud( $args = array() ) {
			echo '<div class="tag-cloud">Tag Cloud HTML</div>';
		}
	}

	if ( ! function_exists( 'get_transient' ) ) {
		function get_transient( $transient ) {
			return isset( WPMocks::$transients[ $transient ] ) ? WPMocks::$transients[ $transient ] : false;
		}
	}

	if ( ! function_exists( 'set_transient' ) ) {
		function set_transient( $transient, $value, $expiration = 0 ) {
			WPMocks::$transients[ $transient ] = $value;
			return true;
		}
	}

	if ( ! function_exists( 'dbDelta' ) ) {
		function dbDelta( $sql ) {
			WPMocks::$db_delta[] = $sql;
		}
	}

	if ( ! function_exists( 'get_post_meta' ) ) {
		function get_post_meta( $post_id, $key = '', $single = false ) {
			if ( isset( WPMocks::$post_meta[ $post_id ][ $key ] ) ) {
				return WPMocks::$post_meta[ $post_id ][ $key ];
			}
			return '';
		}
	}

	if ( ! function_exists( 'update_post_meta' ) ) {
		function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
			WPMocks::$post_meta[ $post_id ][ $meta_key ] = $meta_value;
			return true;
		}
	}

	if ( ! function_exists( 'register_rest_route' ) ) {
		function register_rest_route( $namespace, $route, $args = array(), $override = false ) {
			WPMocks::$rest_routes[] = array( $namespace, $route, $args );
		}
	}

	if ( ! function_exists( 'rest_ensure_response' ) ) {
		function rest_ensure_response( $response ) {
			if ( $response instanceof WP_REST_Response ) {
				return $response;
			}
			if ( is_wp_error( $response ) ) {
				return $response;
			}
			return new WP_REST_Response( $response );
		}
	}

	if ( ! function_exists( 'is_wp_error' ) ) {
		function is_wp_error( $thing ) {
			return ( $thing instanceof WP_Error );
		}
	}

	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $str ) {
			return trim( (string) $str );
		}
	}

	if ( ! function_exists( 'sanitize_email' ) ) {
		function sanitize_email( $email ) {
			return filter_var( $email, FILTER_SANITIZE_EMAIL );
		}
	}

	if ( ! function_exists( 'is_email' ) ) {
		function is_email( $email ) {
			return filter_var( $email, FILTER_VALIDATE_EMAIL );
		}
	}

	if ( ! function_exists( 'absint' ) ) {
		function absint( $maybeint ) {
			return abs( intval( $maybeint ) );
		}
	}

	if ( ! function_exists( 'wp_mail' ) ) {
		function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
			WPMocks::$sent_emails[] = array(
				'to'          => $to,
				'subject'     => $subject,
				'message'     => $message,
				'headers'     => $headers,
				'attachments' => $attachments,
			);
			return true;
		}
	}

	if ( ! function_exists( 'wp_unslash' ) ) {
		function wp_unslash( $value ) {
			return $value;
		}
	}

	if ( ! function_exists( 'esc_url' ) ) {
		function esc_url( $url, $protocols = null, $_context = null ) {
			return $url;
		}
	}

	if ( ! function_exists( 'wp_upload_dir' ) ) {
		function wp_upload_dir( $time = null, $create_dir = true, $refresh_cache = false ) {
			return array( 'basedir' => sys_get_temp_dir() );
		}
	}

	if ( ! function_exists( 'get_term_by' ) ) {
		function get_term_by( $field, $value, $taxonomy, $output = OBJECT, $filter = 'raw' ) {
			return false; // Mock return false by default.
		}
	}

	if ( ! function_exists( 'admin_url' ) ) {
		function admin_url( $path = '', $scheme = 'admin' ) {
			return 'http://example.com/wp-admin/' . $path;
		}
	}

	if ( ! function_exists( 'home_url' ) ) {
		function home_url( $path = '', $scheme = null ) {
			return 'http://example.com/' . $path;
		}
	}

	if ( ! function_exists( 'rest_url' ) ) {
		function rest_url( $path = '' ) {
			return 'http://example.com/wp-json/' . $path;
		}
	}

	if ( ! function_exists( 'wp_nonce_field' ) ) {
		function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
			if ( $echo ) {
				echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="mock_nonce">';
			}
			return 'mock_nonce';
		}
	}

	if ( ! function_exists( 'wp_verify_nonce' ) ) {
		function wp_verify_nonce( $nonce, $action = -1 ) {
			return true;
		}
	}

	if ( ! function_exists( 'wp_create_nonce' ) ) {
		function wp_create_nonce( $action = -1 ) {
			return 'mock_nonce';
		}
	}

	if ( ! function_exists( 'check_admin_referer' ) ) {
		function check_admin_referer( $action = -1, $query_arg = '_wpnonce' ) {
			return true;
		}
	}

	if ( ! function_exists( 'wp_nonce_url' ) ) {
		function wp_nonce_url( $actionurl, $action = -1, $name = '_wpnonce' ) {
			return add_query_arg( $name, 'mock_nonce', $actionurl );
		}
	}

	if ( ! function_exists( 'wp_die' ) ) {
		function wp_die( $message = '', $title = '', $args = array() ) {
			throw new Exception( 'wp_die: ' . $message );
		}
	}

	if ( ! function_exists( 'wp_redirect' ) ) {
		function wp_redirect( $location, $status = 302 ) {}
	}

	if ( ! function_exists( 'wp_safe_redirect' ) ) {
		function wp_safe_redirect( $location, $status = 302 ) {}
	}

	if ( ! function_exists( 'wp_safe_redirect' ) ) {
		function wp_safe_redirect( $location, $status = 302 ) {}
	}

	if ( ! function_exists( 'add_query_arg' ) ) {
		function add_query_arg( $key, $value, $url = '' ) {
			return $url . '?' . $key . '=' . $value;
		}
	}

	if ( ! function_exists( 'wp_get_referer' ) ) {
		function wp_get_referer() {
			return 'http://example.com/referer';
		}
	}

	if ( ! function_exists( 'is_user_logged_in' ) ) {
		function is_user_logged_in() {
			return true;
		}
	}

	if ( ! function_exists( 'wp_get_current_user' ) ) {
		function wp_get_current_user() {
			$user             = new stdClass();
			$user->ID         = 1;
			$user->user_email = 'test@example.com';
			$user->first_name = 'Test';
			$user->last_name  = 'User';
			return $user;
		}
	}

	if ( ! function_exists( 'wp_update_user' ) ) {
		function wp_update_user( $userdata ) {
			if ( empty( $userdata['ID'] ) ) {
				return new WP_Error( 'invalid_user_id', 'Invalid user ID.' );
			}
			return $userdata['ID'];
		}
	}

	if ( ! function_exists( 'wp_generate_password' ) ) {
		function wp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
			return 'mock_password_' . $length;
		}
	}

	if ( ! function_exists( 'wp_send_json_success' ) ) {
		function wp_send_json_success( $data = null ) {
			echo json_encode(
				array(
					'success' => true,
					'data'    => $data,
				)
			);
			return true;
		}
	}

	if ( ! function_exists( 'wp_send_json_error' ) ) {
		function wp_send_json_error( $data = null ) {
			echo json_encode(
				array(
					'success' => false,
					'data'    => $data,
				)
			);
			return true;
		}
	}

	if ( ! function_exists( 'wp_next_scheduled' ) ) {
		function wp_next_scheduled( $hook, $args = array() ) {
			return false;
		}
	}

	if ( ! function_exists( 'wp_schedule_event' ) ) {
		function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {}
	}

	if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
		function wp_clear_scheduled_hook( $hook, $args = array() ) {}
	}

	if ( ! function_exists( 'wp_handle_upload' ) ) {
		function wp_handle_upload( $file, $overrides ) {
			return array(
				'file' => $file['tmp_name'],
				'url'  => 'http://example.com/uploads/' . $file['name'],
				'type' => $file['type'],
			);
		}
	}

	if ( ! function_exists( 'wp_insert_attachment' ) ) {
		function wp_insert_attachment( $attachment, $file, $parent_post_id = 0 ) {
			return 101; // Mock attachment ID.
		}
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		function wp_generate_attachment_metadata( $attachment_id, $file ) {
			return array();
		}
	}

	if ( ! function_exists( 'wp_update_attachment_metadata' ) ) {
		function wp_update_attachment_metadata( $attachment_id, $data ) {
			return true;
		}
	}

	if ( ! function_exists( 'sanitize_file_name' ) ) {
		function sanitize_file_name( $filename ) {
			return preg_replace( '/[^a-zA-Z0-9._-]/', '', $filename );
		}
	}

	if ( ! function_exists( 'wp_get_attachment_image' ) ) {
		function wp_get_attachment_image( $attachment_id, $size = 'thumbnail', $icon = false, $attr = '' ) {
			return '<img src="mock_image.jpg">';
		}
	}

	if ( ! function_exists( 'current_time' ) ) {
		function current_time( $type, $gmt = 0 ) {
			return date( 'Y-m-d H:i:s' );
		}
	}

	// Mock WP Classes
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public $code;
			public $message;
			public $data;
			public function __construct( $code = '', $message = '', $data = '' ) {
				$this->code    = $code;
				$this->message = $message;
				$this->data    = $data;
			}
			public function get_error_code() {
				return $this->code; }
		}
	}

	if ( ! class_exists( 'WP_REST_Request' ) ) {
		class WP_REST_Request {
			private $params = array();
			public function get_params() {
				return $this->params; }
			public function get_param( $key ) {
				return isset( $this->params[ $key ] ) ? $this->params[ $key ] : null;
			}
			public function set_param( $key, $value ) {
				$this->params[ $key ] = $value; }
			public function set_params( $params ) {
				$this->params = $params; }
		}
	}

	if ( ! class_exists( 'WP_REST_Response' ) ) {
		class WP_REST_Response {
			public $data;
			public $status;
			public function __construct( $data = null, $status = 200 ) {
				$this->data   = $data;
				$this->status = $status;
			}
		}
	}

	if ( ! class_exists( 'WP_REST_Controller' ) ) {
		class WP_REST_Controller {
			public $namespace;
			public $rest_base;
			public function register_routes() {}
		}
	}

	if ( ! class_exists( 'WP_CLI' ) ) {
		class WP_CLI {
			public static $logs = array();
			public static function log( $message ) {
				self::$logs[] = $message;
			}
			public static function success( $message ) {
				self::$logs[] = 'SUCCESS: ' . $message;
			}
			public static function error( $message ) {
				self::$logs[] = 'ERROR: ' . $message;
			}
			public static function add_command( $name, $class ) {}
		}
	}

	if ( ! class_exists( 'WP_List_Table' ) ) {
		class WP_List_Table {
			public $items = array();
			protected $_column_headers;
			protected $_pagination_args = array();
			public function __construct( $args = array() ) {}
			public function get_pagenum() {
				return 1; }
			public function set_pagination_args( $args ) {
				$this->_pagination_args = $args; }
			public function get_pagination_args() {
				return $this->_pagination_args; }
			public function display() {}
		}
	}

	if ( ! class_exists( 'WP_Widget' ) ) {
		class WP_Widget {
			public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {}
			public function widget( $args, $instance ) {}
			public function form( $instance ) {}
			public function update( $new_instance, $old_instance ) {
				return $new_instance; }
			public function get_field_id( $field_name ) {
				return 'widget-' . $field_name; }
			public function get_field_name( $field_name ) {
				return 'widget-' . $field_name; }
		}
	}

	if ( ! isset( $GLOBALS['wpdb'] ) ) {
		$GLOBALS['wpdb'] = new class() {
			public $prefix              = 'wp_';
			public $insert_id           = 0;
			public $insert_return_value = false;
			public $get_var_return      = 0;
			public $get_results_return  = array();
			public $get_row_return      = null;
			public $delete_return_value = false;
			public $update_return_value = false;

			public function get_charset_collate() {
				return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
			}
			public function insert( $table, $data, $format = null ) {
				return $this->insert_return_value;
			}
			public function prepare( $query, ...$args ) {
				return $query;
			}
			public function esc_like( $text ) {
				return addcslashes( $text, '_%\\' );
			}
			public function get_var( $query = null, $x = 0, $y = 0 ) {
				return $this->get_var_return;
			}
			public function get_results( $query = null, $output = OBJECT ) {
				return $this->get_results_return;
			}
			public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
				return $this->get_row_return;
			}
			public function delete( $table, $where, $where_format = null ) {
				return $this->delete_return_value;
			}
			public function update( $table, $data, $where, $format = null, $where_format = null ) {
				return $this->update_return_value;
			}
			public function query( $query ) {
				return true;
			}
		};
	}

	if ( ! function_exists( 'get_current_user_id' ) ) {
		function get_current_user_id() {
			return 1;
		}
	}

	if ( ! function_exists( 'get_the_ID' ) ) {
		function get_the_ID() {
			return 1;
		}
	}

	if ( ! function_exists( 'update_user_meta' ) ) {
		function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
			WPMocks::$post_meta[ $user_id ][ $meta_key ] = $meta_value; // Reuse post_meta mock for user meta for simplicity.
			return true;
		}
	}

	if ( ! function_exists( 'get_user_meta' ) ) {
		function get_user_meta( $user_id, $key = '', $single = false ) {
			return isset( WPMocks::$post_meta[ $user_id ][ $key ] ) ? WPMocks::$post_meta[ $user_id ][ $key ] : '';
		}
	}

	if ( ! class_exists( 'WP_Query' ) ) {
		class WP_Query {
			public $posts = array();
			public $post;
			public function __construct( $args = array() ) {}
			public function have_posts() {
				return false; }
			public function the_post() {}
		}
	}

	if ( ! function_exists( 'wp_reset_postdata' ) ) {
		function wp_reset_postdata() {}
	}

	if ( ! function_exists( 'get_post' ) ) {
		function get_post( $post = null, $output = OBJECT, $filter = 'raw' ) {
			if ( is_object( $post ) ) {
				return $post;
			}
			return (object) array(
				'ID'                    => 1,
				'post_author'           => 1,
				'post_date'             => '2023-01-01 12:00:00',
				'post_content'          => 'Content',
				'post_title'            => 'Original Title',
				'post_excerpt'          => '',
				'post_status'           => 'publish',
				'comment_status'        => 'closed',
				'ping_status'           => 'closed',
				'post_password'         => '',
				'post_name'             => 'original-title',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2023-01-01 12:00:00',
				'post_modified_gmt'     => '2023-01-01 12:00:00',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => '',
				'menu_order'            => 0,
				'post_type'             => 'organizer_event',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			);
		}
	}

	if ( ! function_exists( 'wp_insert_post' ) ) {
		function wp_insert_post( $postarr, $wp_error = false ) {
			return 101; // New post ID.
		}
	}

	if ( ! function_exists( 'delete_post_meta' ) ) {
		function delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {
			unset( WPMocks::$post_meta[ $post_id ][ $meta_key ] );
			return true;
		}
	}

	// Mock upgrade.php required by Registration model
	$upgrade_dir = ABSPATH . 'wp-admin/includes';
	if ( ! is_dir( $upgrade_dir ) ) {
		mkdir( $upgrade_dir, 0777, true );
	}
	if ( ! file_exists( $upgrade_dir . '/upgrade.php' ) ) {
		file_put_contents( $upgrade_dir . '/upgrade.php', '<?php' );
	}

	require_once dirname( __DIR__ ) . '/organizer.php';
}
