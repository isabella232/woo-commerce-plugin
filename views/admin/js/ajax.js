jQuery.noConflict();

jQuery(document).ready(function($) {

    $(document).on('click', '.cm-plugin-ad', function (e) {
        var method = $(this).attr('data-method');
        var dataToSend = {};
        dataToSend.action = 'dismiss_notice';
        dataToSend.method = method;

        $.ajax({
            type: "POST",
            url: ajax_request.ajax_url,
            data: dataToSend,
            dataType: "text json",
            success: function (data, textStatus, request) {
                console.log(data);
            },
            error: function (request, textStatus, errorThrown) {
                console.log(request);
            }
        });
    });

    $(document).on('click', '.save-settings', function (e) {
        e.preventDefault();

        var params = $("#lists option:selected").attr('data-url');

        var dataToSend = JSON.parse('{"' + decodeURI(params).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');

        dataToSend.action = 'set_client_list';
        var newList = $('#listName');

        if (newList.is(':visible') && newList.val() == ''){
            e.preventDefault();
            newList.css("border", "1px solid red");
            return false;
        }

        $('.campaign-monitor-woocommerce .progress-notice').slideDown();
        var subscribe = $('#autoNewsletter').is(':checked');
        var debug = $('#logToggle').is(':checked');
        var subscribeText = $('#subscriptionText').val();
        var subscriptionBox =  $('#subscriptionBox').is(':checked');

        var listToCreate = newList.val();
        var listType = $('#listType').val();
        
        dataToSend.debug = debug;
        dataToSend.subscribe = subscribe;
        dataToSend.subscribe_text = subscribeText;
        dataToSend.subscriptionBox = subscriptionBox;
        dataToSend.new_list_name = listToCreate;
        dataToSend.new_list_type = listType;


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
                    $('.campaign-monitor-woocommerce .progress-notice').slideUp();
                }

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

        var elementId = $(this).attr('id');

         if (dataToSend.action == 'create_client'){
             $('.new-client-creation').slideDown('slow');
             return;
         }


         if (dataToSend.action == 'create_list'){
             $('.new-list-creation').slideDown('slow');
             $('#subscriptionLegend').removeClass('hidden');
             $('#autoNewsletter').removeAttr('checked');
             $('#logToggle').removeAttr('checked');
             $("#subscriptionBox").attr('checked', 'checked');
             $('#subscriptionText').attr('value', '');
             return;
         }

        var selectedSettings = {};

        $('.new-client-creation').slideUp();
        $('.new-list-creation').slideUp();
        $('.campaign-monitor-woocommerce .progress-notice').slideDown();
        $.ajax({
            type: "POST",
            url: ajax_request.ajax_url,
            data: dataToSend,
            dataType: "text json",
            success: function (data, textStatus, request) {

                var container = $("#createList");

                if ( elementId == 'lists'){

                    if (!$.isEmptyObject(data)){
                        if(data.automatic_subscription){
                            $('#autoNewsletter').attr('checked', 'checked');
                        }else {
                            $('#autoNewsletter').removeAttr('checked');
                        }

                        if (data.debug){
                            $('#logToggle').attr('checked', 'checked');
                        } else {
                            $('#logToggle').removeAttr('checked');
                        }


                        $('#subscriptionText').attr('value', data.subscribe_text);
                        if (data.toggle_subscription_box){
                            $('#subscriptionLegend').removeClass('hidden');
                            $("#subscriptionBox").attr('checked', 'checked');
                        } else {
                            $('#subscriptionLegend').addClass('hidden');
                            $("#subscriptionBox").removeAttr('checked');
                        }
                    } else {


                        $('#subscriptionLegend').removeClass('hidden');
                        $('#autoNewsletter').removeAttr('checked');
                        $('#logToggle').removeAttr('checked');
                        $("#subscriptionBox").attr('checked', 'checked');
                        $('#subscriptionText').attr('value', '');
                    }


                }

                if (data.selected_list == true){
                    var $d = {};
                    $d.action = 'get_list_settings';
                    $d.ListID = data.selected_list_id;

                    $.ajax({
                        type: "POST",
                        url: ajax_request.ajax_url,
                        data: $d ,
                        dataType: "text json",
                        success: function (data, textStatus, request) {
                            if (!$.isEmptyObject(data)){
                                if(data.automatic_subscription){
                                    $('#autoNewsletter').attr('checked', 'checked');
                                }else {
                                    $('#autoNewsletter').removeAttr('checked');
                                }

                                if (data.debug){
                                    $('#logToggle').attr('checked', 'checked');
                                } else {
                                    $('#logToggle').removeAttr('checked');
                                }


                                $('#subscriptionText').attr('value', data.subscribe_text);
                                if (data.toggle_subscription_box){
                                    $('#subscriptionLegend').removeClass('hidden');
                                    $("#subscriptionBox").attr('checked', 'checked');
                                } else {
                                    $('#subscriptionLegend').addClass('hidden');
                                    $("#subscriptionBox").removeAttr('checked');
                                }
                            } else {

                                $('#subscriptionLegend').removeClass('hidden');
                                $('#autoNewsletter').removeAttr('checked');
                                $('#logToggle').removeAttr('checked');
                                $("#subscriptionBox").attr('checked', 'checked');
                                $('#subscriptionText').attr('value', '');
                            }
                        }
                    });

                } else {
                    if (dataToSend.action == 'view_client_list') {
                        $('#subscriptionLegend').removeClass('hidden');
                        $('#autoNewsletter').removeAttr('checked');
                        $('#logToggle').removeAttr('checked');
                        $("#subscriptionBox").attr('checked', 'checked');
                        $('#subscriptionText').attr('value', '');
                    }
                }

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