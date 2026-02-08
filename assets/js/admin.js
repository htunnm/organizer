jQuery(document).ready(function($) {
    // Recurrence Type Toggle
    var recurrenceType = $('#organizer_recurrence_type');
    var recurrenceOptions = $('#organizer_recurrence_options');

    function toggleRecurrenceOptions() {
        if (recurrenceType.val() === 'single') {
            recurrenceOptions.hide();
        } else {
            recurrenceOptions.show();
        }
    }

    if (recurrenceType.length) {
        toggleRecurrenceOptions();
        recurrenceType.on('change', toggleRecurrenceOptions);
    }

    // Custom Fields Logic
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
});