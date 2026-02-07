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
		function get_option() {
			return 'Y-m-d'; // Default date format for tests.
		}
	}
	if ( ! function_exists( 'wp_enqueue_scripts' ) ) {
		function wp_enqueue_scripts() {}
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

	if ( ! function_exists( 'register_taxonomy' ) ) {
		function register_taxonomy( $taxonomy, $object_type, $args = array() ) {
			WPMocks::$taxonomies[ $taxonomy ] = array(
				'object_type' => $object_type,
				'args'        => $args,
			);
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
		};
	}

	if ( ! function_exists( 'get_current_user_id' ) ) {
		function get_current_user_id() {
			return 1;
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
