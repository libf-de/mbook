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
                        } else {
                            alert("OK");
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
        });
        $(".ft-list-edit").click(function() {
            let terminRoot = $(this).closest('.fktermine-outer');
            $('#edit-dialog-date').val(terminRoot.data('date'));
            $('#edit-dialog-start').val(terminRoot.data('start'));
            $('#edit-dialog-end').val(terminRoot.data('end'));
            $('#edit-dialog-openend').prop('checked', terminRoot.data('openend') == "1");
            $('#edit-dialog-cancelled').prop('checked', terminRoot.data('cancelled') == "1");
            $('#edit-dialog-maxparts').val(parseInt(terminRoot.data('maxparts')));
            $('#edit-dialog').dialog('option', 'title', terminRoot.find('.title').text() + " am " + terminRoot.data('date') + " bearbeiten");
            $("#edit-dialog").dialog("open");
        });
        $("#edit-dialog").dialog({
            autoOpen: false,
            resizable: false,
            height: "auto",
            width: "auto",
            modal: true,
            buttons: {
              "Abbrechen": function() {
                $( this ).dialog( "close" );
              },
              "Speichern": function() {
                $( this ).dialog( "close" );
              }
            }
          });
    });
    //alert("inittoggles");
}