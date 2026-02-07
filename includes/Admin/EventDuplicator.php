<?php
/**
 * Event Duplicator.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

/**
 * Class EventDuplicator
 */
class EventDuplicator {

	/**
	 * Initialize the duplicator.
	 */
	public static function init() {
		add_filter( 'post_row_actions', array( __CLASS__, 'add_duplicate_link' ), 10, 2 );
		add_action( 'admin_post_organizer_duplicate_event', array( __CLASS__, 'handle_duplication' ) );
	}

	/**
	 * Add duplicate link to row actions.
	 *
	 * @param array    $actions Row actions.
	 * @param \WP_Post $post    Post object.
	 * @return array Modified actions.
	 */
	public static function add_duplicate_link( $actions, $post ) {
		if ( 'organizer_event' !== $post->post_type ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=organizer_duplicate_event&post_id=' . $post->ID ),
			'organizer_duplicate_event_' . $post->ID
		);

		$actions['duplicate'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr__( 'Duplicate this event', 'organizer' ) . '">' . esc_html__( 'Duplicate', 'organizer' ) . '</a>';

		return $actions;
	}

	/**
	 * Handle event duplication.
	 */
	public static function handle_duplication() {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

		if ( empty( $post_id ) ) {
			wp_die( esc_html__( 'No post to duplicate has been supplied!', 'organizer' ) );
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'organizer_duplicate_event_' . $post_id ) ) {
			wp_die( esc_html__( 'No naughty business please!', 'organizer' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'organizer' ) );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			/* translators: %d: Post ID */
			wp_die( esc_html( sprintf( __( 'Post creation failed, could not find original post: %d', 'organizer' ), $post_id ) ) );
		}

		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft', // As requested.
			'post_title'     => 'Copy of ' . $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order,
		);

		$new_post_id = wp_insert_post( $args );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html( $new_post_id->get_error_message() ) );
		}

		// Copy post meta.
		$meta_keys = array(
			'_organizer_recurrence_rules',
			'_organizer_event_price',
			'_organizer_event_venue',
			'_organizer_custom_fields',
			'_organizer_event_capacity',
		);

		foreach ( $meta_keys as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( $value ) {
				update_post_meta( $new_post_id, $key, $value );
			}
		}

		// Redirect to the edit screen for the new draft.
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	}
}
