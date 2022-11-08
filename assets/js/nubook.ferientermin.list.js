function initToggles() {
    jQuery(document).ready(( $ ) => {
        jQuery(document).on('change', '#ferien-select', function() {
            var ferien = jQuery(this).val();
            
            if(ferien == -1) {
                window.location.href = "?page=mb-options-menu&action=ferien-add";
            }

            var data = {
                'action': 'mb_get_kurse',
                'fe': ferien
            };
    
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, (response) => {
                jQuery("#fklist-body").replaceWith(response);
                jQuery("input[name='fe']").val(ferien);

                const url = new URL(window.location.href);
                url.searchParams.set('fe', ferien);
                window.history.replaceState(null, null, url);
                initButtons();
            });
        });
    });
    initButtons();
    //alert("inittoggles");
}

function initButtons() {
    jQuery(document).on('change', '.fk-list-parts', () => {
        let newValue;
        if(jQuery(this).attr("type") == "checkbox") {
            newValue = jQuery(this).prop('checked') ? jQuery(this).data('maxparts') : 0;
        } else if(!isNaN(jQuery(this).val()) && jQuery(this).val() != "") {
            newValue = jQuery(this).val();
        } else {
            jQuery(this).css('background-color', 'red');
            return;
        }
        jQuery(this).css('background-color', 'transparent');
        jQuery.ajax({
            url: wpApiSettings.root + "nubook/v1/set-parts",
            type: 'post',
            data: {
                id: jQuery(this).data('id'),
                val: newValue
            },
            beforeSend: ( xhr ) => {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            dataType: 'json',
            complete: (data, txtStatus) => {
                if(typeof data.responseJSON !== 'undefined') {
                    if(data.responseJSON.code != "ok")  {
                        jQuery(this).css('background-color', 'red');
                        alert("FATAL: Error from REST API (" + data.responseJSON.code + ")\nMessage: " + data.responseJSON.msg + "\nRaw: " + JSON.stringify(data.responseJSON));
                    } else {
                        alert("OK");
                    }
                } else {
                    jQuery(this).css('background-color', 'red');
                    alert("FATAL: Request to REST API failed (" + data.status + "):\nstatusText: " + data.statusText + "\nresponseText: " + txtStatus);
                }
            }
        });
    });
    jQuery(".fk-list-btns").click(function () {
        let inpEl = jQuery(this).parent().children('input[type=number]');
        if(inpEl.val() == "") inpEl.val(0);
        if(jQuery(this).val() == "+") {
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
    jQuery(".fk-list-edit").click(() => {
        let terminRoot = jQuery(this).closest('.mb-listelem-outer');
        jQuery('#edit-dialog-id').val(terminRoot.data('id'));
        jQuery('#edit-dialog-date').val(terminRoot.data('date'));
        jQuery('#edit-dialog-start').val(terminRoot.data('start'));
        jQuery('#edit-dialog-end').val(terminRoot.data('end'));
        jQuery('#edit-dialog-openend').prop('checked', terminRoot.data('openend') == "1");
        jQuery('#edit-dialog-cancelled').prop('checked', terminRoot.data('cancelled') == "1");
        jQuery('#edit-dialog-maxparts').val(parseInt(terminRoot.data('maxparts')));
        jQuery('#edit-dialog').dialog('option', 'title', terminRoot.find('.title').text() + " am " + terminRoot.data('date') + " bearbeiten");
        jQuery("#edit-dialog").dialog("open");
    });
    jQuery("#edit-dialog").dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: "auto",
        modal: true,
        buttons: {
          "Abbrechen": () => {
            jQuery( this ).dialog( "close" );
          },
          "Speichern": () => {
            jQuery( "#edit-form").submit();
          }
        }
      });
    jQuery(".fk-delete-course").on("click", (event) => {
        var courseRoot = jQuery(event.currentTarget).closest(".mb-listelem-outer");
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
                '<input type="hidden" name="fe" value="' + jQuery("#ferien-select").val() + '" />' +
                '</form>');
            jQuery('body').append(form);
            form.submit();
        }
    });
}