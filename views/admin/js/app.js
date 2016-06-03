jQuery.noConflict();
jQuery(document).ready(function($) {


    $(document).on('change', '.mapped-fields', function (e) {
        var value = $(this).val();
        var currentElement = $(this);

        $('.mapped-fields').each(function (index, element) {



            if (currentElement.attr('name') == $(element).attr('name') || $(element).val() == ''){
               return;
            }

            if (value == $(element).val()){
                currentElement.val('');
               $(element).css('border', '1px solid red');
               alert('This field is already mapped');
                return false;
            }

        });
    });

    $(document).on('click', '.save-settings', function (e) {
        $("#clientList").slideDown();
        var container = $("#selector .content #variable");
        container.html('');
    });

    $(document).on('click', '#btnCreateList', function (e) {
        var name = $('#listName').val();
        var id = $('#clientSelection').val();
        var optIn = $('#listType').val();


        if ( name == ''){
            $('#listName').css('border', '1px solid #FF0000');
            $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            e.preventDefault();
        } else {
            $('.campaign-monitor-woocommerce .progress-notice').slideDown();
            $('#listNameData').val(name);
            $('#clientIdData').val(id);
            $('#optInData').val(optIn);
        }
    });

    $(document).on('click', '#btnCreateNewClient', function (e) {
        var name = $('#clientName').val();

        if ( name == ''){
            $('.campaign-monitor-woocommerce .progress-notice').slideUp();
            $('#clientName').css('border', '1px solid #FF0000');
            e.preventDefault();
        } else {
            $('.campaign-monitor-woocommerce .progress-notice').slideDown();
            $('#clientNameData').val(name);
        }
    });

    $(document).on('click', '#btnToClientLists', function (e) {
        $("#clientList").slideDown();
        var container = $("#selector .content #variable");
        container.html('');
    });

    $(document).on('click', '#btnMapCustomFields', function (e) {
        $('#fieldMappper').slideDown();
    });
    $(document).on('click', '.switch-list', function (e) {

        var isConfirm = confirm('Are you sure you want to switch lists? \nThis will reset your previous mapping.');

        if (!isConfirm){
            e.preventDefault();
        }
    });

    $(document).on('click', '.modal .btn-close', function (e) {
        $(".modal").hide();
    });

    $(document).on('click', '#btnCreateClientList', function (e) {
        var isEmpty = $('#newListName').val();
        var url = $(this).attr('data-url');

        console.log(url);
        if ($(this).hasClass('ajax-call')) return;

        if (isEmpty == ''){
            $('#newListName').css("border", "1px solid red");
            e.preventDefault();
        }else {
            url += '&list_name=' +  $('#newListName').val();
            $(this).attr('data-url', url);
            $(this).addClass('ajax-call');
            $(this).trigger('click');
        }
    });

});