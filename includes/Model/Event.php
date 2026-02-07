<?php
/**
 * Event Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class Event
 */
class Event {

	/**
	 * Register the Custom Post Type.
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'Events', 'Post Type General Name', 'organizer' ),
			'singular_name'         => _x( 'Event', 'Post Type Singular Name', 'organizer' ),
			'menu_name'             => __( 'Events', 'organizer' ),
			'name_admin_bar'        => __( 'Event', 'organizer' ),
			'archives'              => __( 'Event Archives', 'organizer' ),
			'attributes'            => __( 'Event Attributes', 'organizer' ),
			'parent_item_colon'     => __( 'Parent Event:', 'organizer' ),
			'all_items'             => __( 'All Events', 'organizer' ),
			'add_new_item'          => __( 'Add New Event', 'organizer' ),
			'add_new'               => __( 'Add New', 'organizer' ),
			'new_item'              => __( 'New Event', 'organizer' ),
			'edit_item'             => __( 'Edit Event', 'organizer' ),
			'update_item'           => __( 'Update Event', 'organizer' ),
			'view_item'             => __( 'View Event', 'organizer' ),
			'view_items'            => __( 'View Events', 'organizer' ),
			'search_items'          => __( 'Search Event', 'organizer' ),
			'not_found'             => __( 'Not found', 'organizer' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'organizer' ),
			'featured_image'        => __( 'Featured Image', 'organizer' ),
			'set_featured_image'    => __( 'Set featured image', 'organizer' ),
			'remove_featured_image' => __( 'Remove featured image', 'organizer' ),
			'use_featured_image'    => __( 'Use as featured image', 'organizer' ),
			'insert_into_item'      => __( 'Insert into event', 'organizer' ),
			'uploaded_to_this_item' => __( 'Uploaded to this event', 'organizer' ),
			'items_list'            => __( 'Events list', 'organizer' ),
			'items_list_navigation' => __( 'Events list navigation', 'organizer' ),
			'filter_items_list'     => __( 'Filter events list', 'organizer' ),
		);

		$args = array(
			'label'               => __( 'Event', 'organizer' ),
			'description'         => __( 'Organizer Events', 'organizer' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		);

		register_post_type( 'organizer_event', $args );

		// Register Taxonomy.
		$tax_labels = array(
			'name'              => _x( 'Categories', 'taxonomy general name', 'organizer' ),
			'singular_name'     => _x( 'Category', 'taxonomy singular name', 'organizer' ),
			'search_items'      => __( 'Search Categories', 'organizer' ),
			'all_items'         => __( 'All Categories', 'organizer' ),
			'parent_item'       => __( 'Parent Category', 'organizer' ),
			'parent_item_colon' => __( 'Parent Category:', 'organizer' ),
			'edit_item'         => __( 'Edit Category', 'organizer' ),
			'update_item'       => __( 'Update Category', 'organizer' ),
			'add_new_item'      => __( 'Add New Category', 'organizer' ),
			'new_item_name'     => __( 'New Category Name', 'organizer' ),
			'menu_name'         => __( 'Categories', 'organizer' ),
		);

		register_taxonomy(
			'organizer_category',
			array( 'organizer_event' ),
			array(
				'hierarchical' => true,
				'labels'       => $tax_labels,
				'show_ui'      => true,
				'show_in_rest' => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => 'organizer-category' ),
			)
		);
	}

	/**
	 * Check if event is full.
	 *
	 * @param int $event_id Event ID.
	 * @return bool True if full.
	 */
	public static function is_full( $event_id ) {
		$capacity = (int) get_post_meta( $event_id, '_organizer_event_capacity', true );
		if ( $capacity <= 0 ) {
			return false; // No limit.
		}

		$count = Registration::count_by_event( $event_id );
		return $count >= $capacity;
	}
}
