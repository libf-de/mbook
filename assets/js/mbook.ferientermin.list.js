function initToggles() {
    jQuery(document).ready(function( $ ) {
        $(document).on('change', '.ft-list-parts', function() {
            let newValue;
            if($(this).attr("type") == "checkbox") {
                newValue = $(this).prop('checked') ? 1 : 0;
            } else if(!isNaN($(this).val()) && $(this).val() != "") {
                newValue = $(this).val();
            } else {
                $(this).css('background-color', 'red');
                return;
            }
            $(this).css('background-color', 'transparent');
            $.ajax({
                url: wpApiSettings.root + "mbook/v1/set-parts",
                type: 'post',
                data: {
                    id: $(this).data('id'),
                    val: newValue
                },
                beforeSend: function ( xhr ) {
                    xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                },
                dataType: 'json',
                complete: function(data, txtStatus) {
                    if(typeof data.responseJSON !== 'undefined') {
                        if(data.responseJSON.code != "ok")  {
                            $(this).css('background-color', 'red');
                            alert("FATAL: Error from REST API (" + data.responseJSON.code + ")\nMessage: " + data.responseJSON.message + "\nData: " + JSON.stringify(data.responseJSON.data));
                        }
                    } else {
                        $(this).css('background-color', 'red');
                        alert("FATAL: Request to REST API failed (" + data.status + "):\nstatusText: " + data.statusText + "\nresponseText: " + txtStatus);
                    }
                }
            })
        });
        $(".ft-list-btns").click(function () {
            let inpEl = $(this).parent().children('input[type=number]');
            if(inpEl.val() == "") inpEl.val(0);
            if($(this).val() == "+") {
                let nxtVal = parseInt(inpEl.val()) + 1;
                if(nxtVal <= inpEl.attr("max")) {
                    inpEl.val(nxtVal);
                    inpEl.change();
                }
            } else {
                let nxtVal = parseInt(inpEl.val()) - 1;
                if(nxtVal >= inpEl.attr("min")) {
                    inpEl.val(nxtVal);
                    inpEl.change();
                }
            }
        })
    });
    //alert("inittoggles");
}