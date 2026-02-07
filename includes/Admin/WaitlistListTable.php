<?php
/**
 * Waitlist List Table.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use Organizer\Model\Waitlist;

/**
 * Class WaitlistListTable
 */
class WaitlistListTable extends \WP_List_Table {

	/**
	 * Prepare items for the table.
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = 20;
		$current_page = $this->get_pagenum();

		// Filter by Event ID.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;

		global $wpdb;
		$table_name = Waitlist::get_table_name();
		$where      = '1=1';
		if ( $event_id > 0 ) {
			$where .= $wpdb->prepare( ' AND event_id = %d', $event_id );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE $where" );

		$offset = ( $current_page - 1 ) * $per_page;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$this->items = $wpdb->get_results( "SELECT * FROM $table_name WHERE $where ORDER BY priority DESC, created_at ASC LIMIT $per_page OFFSET $offset", ARRAY_A );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'id'         => __( 'ID', 'organizer' ),
			'event_id'   => __( 'Event', 'organizer' ),
			'name'       => __( 'Name', 'organizer' ),
			'email'      => __( 'Email', 'organizer' ),
			'priority'   => __( 'Priority', 'organizer' ),
			'created_at' => __( 'Date', 'organizer' ),
			'actions'    => __( 'Actions', 'organizer' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'priority'   => array( 'priority', true ), // Default sort.
			'created_at' => array( 'created_at', false ),
		);
	}

	/**
	 * Column default.
	 *
	 * @param array  $item        Item data.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'name':
			case 'email':
			case 'priority':
			case 'created_at':
				return esc_html( $item[ $column_name ] );
			default:
				return '';
		}
	}

	/**
	 * Column event_id.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_event_id( $item ) {
		$title = get_the_title( $item['event_id'] );
		return $title ? esc_html( $title ) : __( '(Unknown)', 'organizer' );
	}

	/**
	 * Column actions.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_actions( $item ) {
		$promote_url = wp_nonce_url( admin_url( 'admin-post.php?action=organizer_promote_waitlist&id=' . $item['id'] ), 'organizer_promote_waitlist_' . $item['id'] );
		$remove_url  = wp_nonce_url( admin_url( 'admin-post.php?action=organizer_remove_waitlist&id=' . $item['id'] ), 'organizer_remove_waitlist_' . $item['id'] );

		$actions  = '<a href="' . esc_url( $promote_url ) . '" class="button button-small">' . __( 'Promote', 'organizer' ) . '</a> ';
		$actions .= '<a href="' . esc_url( $remove_url ) . '" class="button button-small button-link-delete" onclick="return confirm(\'' . esc_attr__( 'Are you sure?', 'organizer' ) . '\');">' . __( 'Remove', 'organizer' ) . '</a>';

		return $actions;
	}
}
