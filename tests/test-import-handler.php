<?php
/**
 * Class ImportHandlerTest
 *
 * @package Organizer
 */

use Organizer\Admin\ImportPage;

/**
 * Test the ImportPage class.
 */
class ImportHandlerTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		$_FILES = array();
	}

	/**
	 * Test handle_import processes CSV.
	 */
	public function test_handle_import_success() {
		// Create a temporary CSV file.
		$csv_content  = "Title,Content,2023-10-01,10:00,60,10.00,20,\"[{ \"\"label\"\": \"\"Diet\"\", \"\"type\"\": \"\"text\"\" }]\"\n";
		$csv_content .= 'Event 1,Desc 1,2023-10-01,10:00,60,10.00,20,"[{ ""label"": ""Diet"", ""type"": ""text"" }]"';
		$tmp_file     = tempnam( sys_get_temp_dir(), 'test_import' );
		file_put_contents( $tmp_file, $csv_content );

		$_FILES['organizer_import_file'] = array(
			'tmp_name' => $tmp_file,
		);

		// Mock wp_insert_post.
		if ( ! function_exists( 'wp_insert_post' ) ) {
			function wp_insert_post( $args ) {
				return 101;
			}
		}

		// Mock SeriesGenerator (since we can't easily mock `new` in this setup without DI, we rely on the fact that it calls Session::create which uses $wpdb).
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$wpdb->insert_return_value = 1;
		}

		try {
			ImportPage::handle_import();
		} catch ( Exception $e ) {
			// wp_safe_redirect might throw or exit.
		}

		// Verify post meta was updated (mocked in bootstrap).
		$this->assertEquals( 10.00, WPMocks::$post_meta[101]['_organizer_event_price'] );
		$this->assertNotEmpty( WPMocks::$post_meta[101]['_organizer_custom_fields'] );

		unlink( $tmp_file );
	}
}
