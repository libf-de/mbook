const weekdays = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];

function initButtons() {
    jQuery(document).on('change', '.ls-list-parts', (e) => {
        let inputs = jQuery(e.target).closest('.nb-listelem-inner-parts').find("input");
        inputs.prop('disabled', true);
        let newValue;
        if(jQuery(e.target).attr("type") == "checkbox") {
            newValue = jQuery(e.target).prop('checked') ? jQuery(e.target).data('maxparts') : 0;
        } else if(!isNaN(jQuery(e.target).val()) && jQuery(e.target).val() != "") {
            newValue = jQuery(e.target).val();
        } else {
            inputs.prop('disabled', false);
            jQuery(e.target).css('background-color', 'red');
            return;
        }
        jQuery(e.target).css('background-color', 'transparent');
        jQuery.ajax({
            url: wpApiSettings.root + "nubook/v1/set-parts-lesson",
            type: 'post',
            data: {
                id: jQuery(e.target).data('id'),
                val: newValue
            },
            beforeSend: ( xhr ) => {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            dataType: 'json',
            complete: (data, txtStatus) => {
                if(typeof data.responseJSON !== 'undefined') {
                    if(data.responseJSON.code != "ok")  {
                        inputs.prop('disabled', false);
                        jQuery(e.target).css('background-color', 'red');
                        alert("FATAL: Error from REST API (" + data.responseJSON.code + ")\nMessage: " + data.responseJSON.msg + "\nRaw: " + JSON.stringify(data.responseJSON));
                    } else {
                        console.log("Participants updated!");
                    }
                } else {
                    inputs.prop('disabled', false);
                    jQuery(e.target).css('background-color', 'red');
                    alert("FATAL: Request to REST API failed (" + data.status + "):\nstatusText: " + data.statusText + "\nresponseText: " + txtStatus);
                }
            }
        });
    });
    jQuery(".ls-list-edit").on('click', (e) => {
        let terminRoot = jQuery(e.target).closest('.nb-listelem-outer');
        jQuery('#edit-dialog-id').val(terminRoot.data('id'));
        jQuery('#edit-dialog-weekday').val(terminRoot.data('weekday'));
        jQuery('#edit-dialog-start').val(terminRoot.data('start').slice(0,-3));
        jQuery('#edit-dialog-end').val(terminRoot.data('end').slice(0,-3));
        jQuery('#edit-dialog-cancelled').prop('checked', terminRoot.data('cancelled') == "1");
        jQuery('#edit-dialog-maxparts').val(parseInt(terminRoot.data('maxparts')));
        jQuery('#edit-dialog').dialog('option', 'title', terminRoot.find('.title').text() + " am " + weekdays[terminRoot.data('weekday')] + " bearbeiten");
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
            jQuery("#edit-dialog").dialog( "close" );
          },
          "Speichern": () => {
            jQuery( "#edit-form").submit();
          }
        }
      });
    jQuery(".ls-delete-course").on("click", (event) => {
        var courseRoot = jQuery(event.currentTarget).closest(".nb-listelem-outer");
        var dateStr = `${weekdays[courseRoot.data("weekday")]}s von ${courseRoot.data("start").slice(0,-3)} bis ${courseRoot.data("end").slice(0,-3)} Uhr`;
        
        if (confirm(`Möchten Sie die ${courseRoot.find('.title').text().replace(/\s+/g,' ').trim()} ${dateStr} wirklich löschen?`)) {
            var form = jQuery('<form action="' + WPURL.lsdelete + '" method="post">' +
                '<input type="hidden" name="id" value="' + courseRoot.data("id") + '" />' +
                '</form>');
            jQuery('body').append(form);
            form.submit();
        }
    });
}