<?php
/**
 * Calendar Block.
 *
 * @package Organizer\Blocks
 */

namespace Organizer\Blocks;

use Organizer\Frontend\Shortcodes;

/**
 * Class CalendarBlock
 */
class CalendarBlock {

	/**
	 * Initialize the block.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register the block.
	 */
	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'organizer/calendar',
			array(
				'attributes'      => array(
					'limit'      => array(
						'type'    => 'number',
						'default' => 10,
					),
					'showSearch' => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'category'   => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'render_callback' => array( __CLASS__, 'render_callback' ),
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 */
	public static function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'organizer-calendar-block',
			ORGANIZER_URL . 'assets/js/calendar-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			ORGANIZER_VERSION,
			true
		);
	}

	/**
	 * Render callback.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public static function render_callback( $attributes ) {
		$atts = array(
			'limit'       => isset( $attributes['limit'] ) ? $attributes['limit'] : 10,
			'show_search' => isset( $attributes['showSearch'] ) && $attributes['showSearch'] ? 'yes' : 'no',
			'category'    => isset( $attributes['category'] ) ? $attributes['category'] : '',
		);

		return Shortcodes::render_calendar( $atts );
	}
}
