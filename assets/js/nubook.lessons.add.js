let defaultDuration;

function weekday_dropdown(sel) {
    return `<option value="0" ${sel == 0 ? "selected" : ""}>Montags</option>`
        + `<option value="1" ${sel == 1 ? "selected" : ""}>Dienstags</option>`
        + `<option value="2" ${sel == 2 ? "selected" : ""}>Mittwochs</option>`
        + `<option value="3" ${sel == 3 ? "selected" : ""}>Donnerstags</option>`
        + `<option value="4" ${sel == 4 ? "selected" : ""}>Freitags</option>`
        + `<option value="5" ${sel == 5 ? "selected" : ""}>Samstags</option>`
        + `<option value="6" ${sel == 6 ? "selected" : ""}>Sonntags</option>`;
}

function initAddLesson() {
    jQuery(document).ready(function ($) {
        $('#lesson-add-append').click(() => {
            var lastObj = $('.selected-dates:last');
            var d = lastObj.data("num") + 1;
            var lastDay = lastObj.find("select.weekday").val();
            var lastStart = lastObj.find("input.startTime").val();
            var lastEnd = lastObj.find("input.endTime").val();
            lastObj.after(`<tr valign="top" data-num="${d}" id="date-${d}" id="dates-root" class="selected-dates dates-line"><th scope="row"></th><td><select required name="dates[${d}][weekday][]" multiple class="weekday lesson-add-weekday">${weekday_dropdown(parseInt(lastDay))}</select><span style="white-space: nowrap;"><input type="time" class="lesson-add-start startTime" required step="60" value="${lastStart}" min="00:00" max="23:59" name="dates[${d}][start]"> &mdash; <input type="time" class="lesson-add-end endTime" required step="60" value="${lastEnd}" min="00:00" max="23:59" name="dates[${d}][end]" id="dates-${d}-end"> Uhr <a class="button button-warn lesson-add-remove" ><i class="fa-solid fa-trash-can"></i></a></span></td></tr>`);
        });

        defaultDuration = jQuery("#template").find(":selected").data("duration");
        jQuery("#template").change(() => {
            defaultDuration = jQuery(this).find(":selected").data("duration");

            jQuery("input[name=max-participants]").val(jQuery(this).find(":selected").data("maxparts"));

            if(jQuery(".selected-dates").length > 1)
                if(!confirm("Möchten Sie die Endzeiten aller eingegebenen Stunden anpassen?")) return;

            for(const dateRow of jQuery(".selected-dates")) {
                var endElem = jQuery(dateRow).find("input.endTime");
                var startTimeArr = jQuery(dateRow).find("input.startTime").val().split(":");
                var durationHours = Math.floor(defaultDuration/60);
                var durationMins = defaultDuration % 60;
                console.log(`${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
                endElem.attr("data-normTime", `${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
                endElem.data("normTime", `${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
                endElem.attr("data-changed", false);
                endElem.data("changed", false);
                endElem.val(`${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
            }
        });

        $(document).on('click', 'a.lesson-add-remove', (e) => {
            jQuery(e.target).parents(".selected-dates").fadeOut(300, function(){ $(this).remove();});
        });

        $(document).on('change', 'input.startTime', (e) => {
            var endElem = jQuery(e.target).parent().find("input.endTime");
            var startTimeArr = jQuery(e.target).val().split(":");
            var durationHours = Math.floor(defaultDuration/60);
            var durationMins = defaultDuration % 60;
            console.log(`${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
            endElem.attr("data-normTime", `${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
            endElem.data("normTime", `${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
            if(endElem.data("changed") == false || endElem.data("changed") == null)
                endElem.val(`${String(parseInt(startTimeArr[0]) + durationHours).padStart(2, '0')}:${String(parseInt(startTimeArr[1]) + durationMins).padStart(2, '0')}`);
        });

        $(document).on('change', 'input.endTime', (e) => {
            var txtBox = jQuery(e.target);
            txtBox.attr("data-changed", txtBox.val() !== txtBox.data("normTime"));
            txtBox.data("changed", txtBox.val() !== txtBox.data("normTime"));
        });

        $('button[type=submit]').click(function(e) {
            for(const dateRow of jQuery(".selected-dates")) {
                if(jQuery(dateRow).find("input.startTime").val() > jQuery(dateRow).find("input.endTime").val()) {
                    jQuery(dateRow).css("background-color", "red");
                    jQuery(dateRow).find("th").text("Startzeit liegt nach Endzeit!");
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
}