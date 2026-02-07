<?php
/**
 * QR Code Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Class QrCodeService
 */
class QrCodeService {

	/**
	 * Generate QR Code and save to file.
	 *
	 * @param string $content  QR Code content.
	 * @param string $filepath File path to save.
	 * @return bool True on success.
	 */
	public function generate_file( $content, $filepath ) {
		if ( ! class_exists( 'Endroid\QrCode\Builder\Builder' ) ) {
			return false;
		}

		try {
			$result = Builder::create()
				->writer( new PngWriter() )
				->writerOptions( array() )
				->data( $content )
				->encoding( new Encoding( 'UTF-8' ) )
				->errorCorrectionLevel( new ErrorCorrectionLevelHigh() )
				->size( 300 )
				->margin( 10 )
				->build();

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			$result->saveToFile( $filepath );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
