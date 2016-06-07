jQuery.noConflict();

jQuery(document).ready(function($) {

    $(document).on('click', '.save-settings', function (e) {
        e.preventDefault();
        $('.campaign-monitor-woocommerce .progress-notice').slideDown();
        var params = $("#lists option:selected").attr('data-url');
        var dataToSend = JSON.parse('{"' + decodeURI(params).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');

        dataToSend.action = 'set_client_list';

        var subscribe = $('#autoNewsletter').is(':checked');
        var debug = $('#logToggle').is(':checked');

        dataToSend.debug = debug;
        dataToSend.subscribe = subscribe;
        
        $.ajax({
            type: "POST",
            url: ajax_request.ajax_url,
            data: dataToSend,
            dataType: "text json",
            success: function (data, textStatus, request) {
                $("#clientList").slideUp();
                var container = $(".content #variable");
                if (data.modal != ''){
                    container.append(data.content);
                    container.append(data.modal);
                } else {
                    container.html(data.content);
                }

                if (typeof data.error == 'undefined' || data.error == 'false'){
                    $('#poststuff').slideUp();
                }

                $('.campaign-monitor-woocommerce .progress-notice').slideUp();
                $("#selector .content").slideDown();
            },
            error: function (request, textStatus, errorThrown) {
                $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            }
        });
    });

    $(document).on('click', '.post-ajax', function (e) {
        e.preventDefault();
        // this is was created on app.js
        if (abortAjax) return;

        $('.campaign-monitor-woocommerce .progress-notice').slideDown();
        var params = $(this).attr('data-url');
        if (params == ''){
            params = $(this).attr('href');
        }
        var dataToSend = JSON.parse('{"' + decodeURI(params).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');

        $.ajax({
            type: "POST",
            url: ajax_request.ajax_url,
            data: dataToSend,
            dataType: "text json",
            success: function (data, textStatus, request) {
                $("#clientList").slideUp();
                var container = $(".content #variable");
                if (data.modal != ''){
                    container.append(data.content);
                    container.append(data.modal);
                } else {
                    container.html(data.content);
                }

                if (typeof data.error == 'undefined' || data.error == 'false'){
                    $('#poststuff').slideUp();
                }

                $('.campaign-monitor-woocommerce .progress-notice').slideUp();
                $("#selector .content").slideDown();

            },
            error: function (request, textStatus, errorThrown) {
                $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            }
        });
    });
    $(document).on('change', '.ajax-call', function (e) {
        e.preventDefault();

        var  params = $("option:selected", this).attr('data-url');

        if (params == '' || typeof params == 'undefined') {
            $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            return;
        }
        var dataToSend = JSON.parse('{"' + decodeURI(params).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');


         if (dataToSend.action == 'create_client'){
             $('.new-client-creation').slideDown('slow');
             return;
         }

         if (dataToSend.action == 'create_list'){
             $('.new-list-creation').slideDown('slow');
             return;
         }

        $('.new-client-creation').slideUp();
        $('.new-list-creation').slideUp();
        $('.campaign-monitor-woocommerce .progress-notice').slideDown();
        $.ajax({
            type: "POST",
            url: ajax_request.ajax_url,
            data: dataToSend,
            dataType: "text json",
            success: function (data, textStatus, request) {

               // $("#clientList").slideUp();
                var container = $("#createList");

                if (data.modal != ''){
                    container.html(data.content);
                    container.append(data.modal);
                } else {
                    container.html(data.content);
                }

                if (data.show){
                    $(data.show).show();
                }

                if (data.hide){
                    $(data.hide).hide();
                }


                $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            },
            error: function (request, textStatus, errorThrown) {
                $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            }
        });
    });

});