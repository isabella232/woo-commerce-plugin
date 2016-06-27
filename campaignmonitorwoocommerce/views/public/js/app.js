jQuery.noConflict();
jQuery(document).ready(function($) {

/*    $(document).on('change', '#subscriptionBox', function (e) {
        var dataToSend = {};
        dataToSend.action = 'ajax_handler_nopriv';
        e.preventDefault();

        var subscribe = $(this).is(':checked');
        var nonce = $('#subscriptionNonce').attr('value');
        dataToSend.subscribe = subscribe;
        dataToSend.nonce = nonce;

        console.log(dataToSend);
        $.ajax({
            type: "POST",
            url: ajax_request.ajax_url,
            data: dataToSend,
            dataType: "text json",
            success: function (data, textStatus, request) {
                console.log(data);
            },
            error: function (request, textStatus, errorThrown) {
                console.log(errorThrown);
            }
        });
    });*/
});