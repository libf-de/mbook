function initToggles() {
    jQuery(document).ready(( $ ) => {
        jQuery(document).on('change', '#ferien-select', function() {
            alert("Trig'd");
            var ferien = jQuery(this).val();
            
            if(ferien == -1) {
                window.location.href = "?page=nb-options-menu&action=ferien-modify";
            }

            var data = {
                'action': 'nb_get_kurse',
                'fe': ferien
            };
    
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, (response) => {
                jQuery("#fklist-body").replaceWith(response);
                jQuery("input[name='fe']").val(ferien);

                const url = new URL(window.location.href);
                url.searchParams.set('fe', ferien);
                window.history.replaceState(null, null, url);

                jQuery("#nb-fklist-add").attr("href", "?page=nb-options-menu&action=fkurs-add&fe=" + ferien);
                
                initButtons();
            });
        });
    });
    initButtons();
    //alert("inittoggles");
}

function initButtons() {
    jQuery(document).on('change', '.fk-list-parts', (e) => {
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
            url: wpApiSettings.root + "nubook/v1/set-parts",
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
                        jQuery(e.target).css('background-color', 'red');
                        inputs.prop('disabled', false);
                        alert("FATAL: Error from REST API (" + data.responseJSON.code + ")\nMessage: " + data.responseJSON.msg + "\nRaw: " + JSON.stringify(data.responseJSON));
                    } else {
                        inputs.prop('disabled', false);
                        console.log("Participants updated!");
                    }
                } else {
                    jQuery(e.target).css('background-color', 'red');
                    inputs.prop('disabled', false);
                    alert("FATAL: Request to REST API failed (" + data.status + "):\nstatusText: " + data.statusText + "\nresponseText: " + txtStatus);
                }
            }
        });
    });
    jQuery(".fk-list-btns").on('click', (e) => {
        let inpEl = jQuery(e.target).parent().children('input[type=number]');
        if(inpEl.val() == "") inpEl.val(0);
        if(jQuery(e.target).val() == "+") {
            let nxtVal = parseInt(inpEl.val()) + 1;
            if(nxtVal <= inpEl.attr("max")) {
                inpEl.val(nxtVal);
                inpEl.trigger('change');
                //inpEl.change();
            }
        } else {
            let nxtVal = parseInt(inpEl.val()) - 1;
            if(nxtVal >= inpEl.attr("min")) {
                inpEl.val(nxtVal);
                inpEl.trigger('change');
                //inpEl.change();
            }
        }
    });
    jQuery(".fk-list-edit").on('click', (e) => {
        let terminRoot = jQuery(e.target).closest('.nb-listelem-outer');
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
          "Abbrechen": (e) => {
            jQuery( "#edit-dialog" ).dialog( "close" );
          },
          "Speichern": () => {
            jQuery( "#edit-form").submit();
          }
        }
      });
    jQuery(".fk-delete-course").on("click", (event) => {
        var courseRoot = jQuery(event.currentTarget).closest(".nb-listelem-outer");
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