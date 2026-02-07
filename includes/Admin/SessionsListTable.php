<?php
/**
 * Sessions List Table.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use Organizer\Model\Session;

/**
 * Class SessionsListTable
 */
class SessionsListTable extends \WP_List_Table {

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
		$total_items  = Session::count_all();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'start_datetime';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC';

		$this->items = Session::get_all( $per_page, ( $current_page - 1 ) * $per_page, $orderby, $order );

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
			'id'             => __( 'ID', 'organizer' ),
			'event_id'       => __( 'Event', 'organizer' ),
			'start_datetime' => __( 'Start', 'organizer' ),
			'end_datetime'   => __( 'End', 'organizer' ),
			'status'         => __( 'Status', 'organizer' ),
			'capacity'       => __( 'Capacity', 'organizer' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'start_datetime' => array( 'start_datetime', false ),
			'end_datetime'   => array( 'end_datetime', false ),
			'status'         => array( 'status', false ),
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
			case 'start_datetime':
			case 'end_datetime':
			case 'status':
			case 'capacity':
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
}
