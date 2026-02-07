jQuery(document).ready(function($) {
    if (!$('#organizer-qr-reader').length) {
        return;
    }

    var html5QrcodeScanner = new Html5QrcodeScanner(
        "organizer-qr-reader", { fps: 10, qrbox: 250 }
    );

    var isProcessing = false;

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        
        // Extract token from URL (e.g., ...?action=organizer_checkin&token=XYZ)
        var urlParams = new URLSearchParams(decodedText.split('?')[1]);
        var token = urlParams.get('token');

        if (!token) {
            // Try direct token if QR is just the token string
            token = decodedText;
        }

        if (token) {
            isProcessing = true;
            $('#organizer-scan-result').show().html('Processing...').removeClass('success error');

            $.ajax({
                url: organizer_scanner.api_url,
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', organizer_scanner.nonce);
                },
                data: {
                    token: token
                },
                success: function(response) {
                    $('#organizer-scan-result').addClass('success').html('<p style="color:green; font-weight:bold;">' + response.message + '</p><p>Attendee: ' + response.registration.name + '</p>');
                    playBeep();
                    setTimeout(function() { isProcessing = false; }, 2000);
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing scan.';
                    $('#organizer-scan-result').addClass('error').html('<p style="color:red;">' + msg + '</p>');
                    setTimeout(function() { isProcessing = false; }, 2000);
                }
            });
        }
    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
        // console.warn(`Code scan error = ${error}`);
    }

    function playBeep() {
        var audio = document.getElementById('organizer-beep-sound');
        if (audio) {
            audio.play().catch(function(e) { console.log('Audio play failed', e); });
        }
    }

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
});