<?php
/**
 * Scanner View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="organizer-scanner-wrapper">
	<h2><?php esc_html_e( 'Check-in Scanner', 'organizer' ); ?></h2>
	<div id="organizer-qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
	<div id="organizer-scan-result" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; display: none;"></div>
	
	<audio id="organizer-beep-sound" style="display:none;">
		<source src="<?php echo esc_url( ORGANIZER_URL . 'assets/audio/beep.mp3' ); ?>" type="audio/mpeg">
	</audio>
</div>
