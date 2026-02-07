jQuery(document).ready(function($) {
    var container = $('#organizer-custom-fields-container');
    var template = $('#organizer-custom-field-template').html();
    var count = container.children().length;

    $('#organizer-add-custom-field').on('click', function() {
        var newRow = template.replace(/INDEX/g, count);
        container.append(newRow);
        count++;
    });

    container.on('click', '.organizer-remove-custom-field', function() {
        $(this).closest('.organizer-custom-field-row').remove();
    });

    // Toggle recurrence options
    // (Existing logic for recurrence type toggle could go here if needed)
});