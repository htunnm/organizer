jQuery(document).ready(function($) {
    $('#organizer_apply_discount').on('click', function() {
        var code = $('#organizer_discount_code').val();
        var price = $('#organizer_event_price_display').data('price'); // Assuming we add this data attr
        var message = $('#organizer_discount_message');

        if (!code) return;

        $.post(organizer_ajax.ajaxurl, {
            action: 'organizer_validate_discount',
            code: code,
            price: price
        }, function(response) {
            if (response.success) {
                message.css('color', 'green').text(response.data.message);
                // Update displayed price if element exists
                if ($('#organizer_event_price_display').length) {
                    $('#organizer_event_price_display').text(response.data.new_price);
                }
            } else {
                message.css('color', 'red').text(response.data.message);
            }
        });
    });
});