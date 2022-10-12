function initToggles() {
    jQuery(document).ready(function( $ ) {
        $(document).on('change', '#ferien-select', function() {
            console.log($(this).val());
        });
        $(document).on('change', '.fk-list-parts', function() {
            let newValue;
            if($(this).attr("type") == "checkbox") {
                newValue = $(this).prop('checked') ? $(this).data('maxparts') : 0;
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
        $(".fk-list-btns").click(function () {
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
        $(".fk-list-edit").click(function() {
            let terminRoot = $(this).closest('.fktermine-outer');
            $('#edit-dialog-id').val(terminRoot.data('id'));
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
                $( "#edit-form").submit();
              }
            }
          });
        jQuery(".fk-delete-course").on("click", (event) => {
            var courseRoot = jQuery(event.currentTarget).closest(".fktermine-outer");
            var dateStr;
            if(courseRoot.data("openend") == "1")
                dateStr =  "ab " + courseRoot.data("date") + ", " + courseRoot.data("start") + " Uhr";
            else {
                var tsEnd = courseRoot.data("end").split(/[-T:]/);
                dateStr = `von ${courseRoot.data("date")}, ${courseRoot.data("start")} Uhr bis ${tsEnd[2]}.${tsEnd[1]}.${tsEnd[0]}, ${tsEnd[3]}:${tsEnd[4]} Uhr`;
            }
            if (confirm(`Möchten Sie den Ferienkurs \"${courseRoot.find('.title').text()}\" ${dateStr} wirklich löschen?`)) {
                var form = jQuery('<form action="' + WPURL.fkdelete + '" method="post">' +
                    '<input type="hidden" name="id" value="' + courseRoot.data("id") + '" />' +
                    '</form>');
                jQuery('body').append(form);
                form.submit();
            }
        });
    });
    //alert("inittoggles");
}
