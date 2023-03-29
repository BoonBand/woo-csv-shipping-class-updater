jQuery(function ($) {
    $('#csv-form').on('submit', function (event) {
        event.preventDefault();

        let formData = new FormData(this);

        formData.append('action', 'process_csv');
        formData.append('security', ajax_object.ajax_nonce);

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#progress').html('Data processing...');
            },
            success: function (response) {
                if (response.success) {
                    $('#progress').html(response.data.message);
                } else {
                    $('#progress').html('Error: ' + response.data.message);
                }
            },
            error: function () {
                $('#progress').html('Error.');
            }
        });
    });
});
