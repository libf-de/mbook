let selectedDates = [];
let terminDauerTage = 0;
let defaultStart = 0;
let defaultDuration = 0;
let occupiedDates = [];
let dspYear = new Date().getFullYear();
let dspMonth =new Date().getMonth()+1;

function addDaysToString(s, a) {
    return new Date(new Date(s).getTime() + 86400000 * a);
}

function initOpenEnd() {
    console.log("initOpenEnd()");
    jQuery(".openEnd").on("change", (event) => {
        if (jQuery(event.currentTarget).data("disables-id") != null) {
            const disId = document.getElementById(jQuery(event.currentTarget).data("disables-id"));
            disId.disabled = event.currentTarget.checked;
            disId.required = !event.currentTarget.checked;
        } else if (jQuery(event.currentTarget).data("disables-class") != null) {
            var boxes = document.getElementsByClassName(jQuery(event.currentTarget).data("disables-class"));
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].disabled = event.currentTarget.checked;
                boxes[i].required = !event.currentTarget.checked;
            }
        }
    });
}

function initListFTemplate() {
    jQuery(".ft-delete-course").on("click", (event) => {
        if (confirm("Möchten Sie die \"" + jQuery(event.currentTarget).data("title") + "\"-Vorlage wirklich löschen?\nALLE ZUGEHÖRIGEN FERIENKURSE WERDEN EBENFALLS GELÖSCHT!")) {
            var form = jQuery('<form action="' + WPURL.ftdelete + '" method="post">' +
                '<input type="hidden" name="id" value="' + jQuery(event.currentTarget).data("id") + '" />' +
                '</form>');
            jQuery('body').append(form);
            form.submit();
        }
    });
}

function initAddFTemplate() {
    initOpenEnd();

    jQuery('input[name="title"]').on("input", (event) => {
        let titleVal = event.currentTarget.value;
        let shortVal = "";
        titleVal.split(/[\s-]+/).forEach((singleWord) => {
            shortVal += singleWord.charAt(0).replace(/[^a-z0-9]/gi, '').toUpperCase();
            if (singleWord.toLowerCase().includes('reitkurs') && singleWord.toLowerCase() != "reitkurs") {
                shortVal += "RK";
            } else if (singleWord.toLowerCase().includes('kurs') && singleWord.toLowerCase() != "kurs") {
                shortVal += "K";
            } else if (singleWord.toLowerCase().includes('ritt') && singleWord.toLowerCase() != "ritt") {
                shortVal += "R";
            }
        });

        jQuery('input[name="shorthand"]').val(shortVal);
    });

    /*const checkbox = document.getElementById('openEnd');

    checkbox.addEventListener('change', (event) => {
        var boxes = document.getElementsByClassName("duration-input"); 
        for (var i = 0; i < boxes.length; i++) { 
            boxes[i].disabled = event.currentTarget.checked;
            boxes[i].required = !event.currentTarget.checked;
        }
    });*/
}

function convertDate(d) {
    var p = d.split("-");
    return +(p[1] + p[2] + p[3]);
}

function ferienkursAddInit() {
    jQuery(document).ready(function ($) {
        initOpenEnd();
        
        $('#template').change(function () {
            loadOccupied();
            ferienkursAddUpdatePicker();
        });

        $('button[type=submit]').click(function(e) {
            if($(".selected-dates").length < 2) {
                alert("Es wurden keine Daten ausgewählt!");
                e.preventDefault();
                return false;
            }
        });

        $('#clear-dates').click(function() {
            $("#dates").multiDatesPicker('resetDates');
            $(".selected-dates").not(":first").remove();
            ferienkursAddUpdatePicker();
        })

        var prev_val;

        $('#ferien-select').focus(function () {
            prev_val = $(this).val();
        }).change(function () {
            if($(".selected-dates").length > 1) {
                $(this).blur() // Firefox fix as suggested by AgDude
                var success = confirm('Vorsicht: Das Ändern der Ferien nach Auswahl von Terminen kann zu Problemen führen, da Termine möglicherweise außerhalb der Ferien landen. Ferien wirklich ändern?');
                if (!success) {
                    $(this).val(prev_val);
                    return false;
                }

                var selDates = $('#dates').multiDatesPicker('getDates');
                $("#dates").multiDatesPicker('destroy');
                ferienkursAddUpdatePicker();
                $("#dates").multiDatesPicker('addDates', selDates);
            } else {
                $("#dates").multiDatesPicker('destroy');
                ferienkursAddUpdatePicker();
            }
        });

        ferienkursAddUpdatePicker();
    });
}

function calculateDisabled(firstDate) {
    let followDates = [];
    for (let i = 1; i <= terminDauerTage; i++) {
        followDates.push(addDaysToString(firstDate, i));
    }
    return followDates;
}

function calculateEndDay(inputDate) {
    var endDate = new Date(inputDate);
    endDate.setDate(endDate.getDate() + terminDauerTage);
    return endDate.getFullYear() + "-" + ('0' + (endDate.getMonth() + 1)).slice(-2) + "-" + ('0' + endDate.getDate()).slice(-2);
}

function dateToHtmlIso(date) {
    return date.getFullYear() + "-" + ('0' + (date.getMonth() + 1)).slice(-2) + "-" + ('0' + date.getDate()).slice(-2) + "T" + ('0' + date.getHours()).slice(-2) + ":" + ('0' + date.getMinutes()).slice(-2) + ":" + ('0' + date.getSeconds()).slice(-2);
}

function loadOccupied() {
    jQuery.ajax({
        url: WPURL.queryurl,
        type: 'post',
        data: {
            t: jQuery("#template").val(),
            m: dspMonth,
            y: dspYear
        },
        beforeSend: function ( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce ); },
        dataType: 'json',
        complete: function(data, txtStatus) {
            if(data.status == 200) {
                data.responseJSON.forEach(element => {
                    let dateArr = element.split("-");
                    jQuery(`td[data-month=${parseInt(dateArr[1])-1}][data-year=${parseInt(dateArr[0])}]:has([data-date=${parseInt(element.split("-")[2])}])`)
                        .addClass("datepicker-occupied");
                });
            }
        },
    });
}

function ferienkursAddUpdatePicker() {
    jQuery(document).ready(function ($) {
        let today = new Date();
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);
        today.setMilliseconds(0);
        let selectedFerien = $('#ferien-select').find(':selected');
        let selectedEntry = $('#template').find(':selected');
        terminDauerTage = selectedEntry.data('days');
        let defaultWeekday = selectedEntry.data('day');
        defaultStart = selectedEntry.data('start');
        defaultDuration = selectedEntry.data('duration');
        $('input[name="max-participants"]').val(parseInt(selectedEntry.data('maxparts')));
        dspMonth = today.getMonth() + 1;
        dspYear = today.getFullYear();
        loadOccupied();
        $('#dates').multiDatesPicker({
            minDate: selectedFerien.data("dstart"),
            maxDate: selectedFerien.data("dend"),
            dateFormat: 'yy-mm-dd',
            dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            onChangeMonthYear: function(y, m, instance) {
                dspMonth = m;
                dspYear = y;
                loadOccupied();
            },
            beforeShowDay: function (d) {
                let dayISO = d.getDay() - 1;
                dayISO = (dayISO >= 0 ? dayISO : dayISO + 7);

                return [true, ( 
                    (dayISO == $("#template option:selected").data('day')) ? "datepicker-stdday " : "") +
                    (d < today ? "datepicker-past " : "")
                ];
            },
            onSelect: function (d, i) {
                var sdate = new Date(d);
                sdate.setHours(0);
                sdate.setMinutes(defaultStart);
                if ($('#dates').multiDatesPicker('gotDate', d) === false) {
                    // If date was removed from selection, remove input field and possibly following disabled days
                    $('#date-' + d).remove();
                    if (terminDauerTage > 0) {
                        $('#dates').multiDatesPicker('removeDates', calculateDisabled(d), 'disabled');
                    }
                } else {
                    // If date was added to selection, add input fields...
                    let endDate = new Date(sdate.valueOf());
                    if (defaultDuration > 0) endDate.setMinutes(endDate.getMinutes() + defaultDuration);
                    //$('.selected-dates:last').after("<tr id=\"date-" + d + "\" class=\"selected-dates dates-line\" valign=\"top\"><th scope=\"row\">&nbsp;</th><td><input type=\"date\" size=\"6\" required name=\"dates-" + d + "-date\" class=\"datum dates-line-date\" value=\"" + d + "\" readonly><input type=\"time\" class=\"startTime dates-line-time\" required step=\"60\" value=\"" + ('0' + sdate.getHours()).slice(-2) + ":" + ('0' + sdate.getMinutes()).slice(-2) + "\" min=\"00:00\" max=\"23:59\" name=\"dates-" + d + "-start\"> Uhr bis  <input type=\"datetime-local\" size=\"6\" name=\"dates-" + d + "-end\" class=\"datum\" min=\"" + dateToHtmlIso(sdate) + "\" value=\"" + dateToHtmlIso(endDate) + "\" required> Uhr</td></tr>"); //TODO: Set min value properly;  <input type=\"date\" size=\"6\" name=\"dates-" + d + "-enddate\" class=\"datum dates-line-date\" min=\"" + d + "\" value=\"" + calculateEndDay(d) + "\" required><input type=\"time\" class=\"endTime dates-line-time\" required step=\"60\" min=\"00:00\" max=\"23:59\" name=\"dates-" + d + "-end\">
                    $('.selected-dates:last').after("<tr id=\"date-" + d + "\" class=\"selected-dates dates-line\" valign=\"top\"><th scope=\"row\" class=\"dates-line-heading\">" + sdate.toLocaleDateString("de-DE", { month: "2-digit", day: "2-digit", year: "numeric" }) + "</th><td><input type=\"date\" size=\"6\" required name=\"dates[" + d + "][date]\" class=\"datum dates-line-date\" value=\"" + d + "\" readonly><input type=\"time\" class=\"startTime dates-line-time\" required step=\"60\" value=\"" + ('0' + sdate.getHours()).slice(-2) + ":" + ('0' + sdate.getMinutes()).slice(-2) + "\" min=\"00:00\" max=\"23:59\" name=\"dates[" + d + "][start]\"> &mdash; <nobr><input type=\"datetime-local\" size=\"6\" name=\"dates[" + d + "][end]\" id=\"dates-" + d + "-end\" class=\"datum\" min=\"" + dateToHtmlIso(sdate) + "\" value=\"" + dateToHtmlIso(endDate) + "\" " + (defaultDuration == -1 ? "disabled" : "required") + "> Uhr</nobr> <div class=\"dates-line-break\">oder <input type=\"checkbox\" data-disables-id=\"dates-" + d + "-end\" name=\"dates[" + d + "][openEnd]\" class=\"openEnd\" " + (defaultDuration == -1 ? "checked" : "") + "> offenes Ende</div></td></tr>");
                    // ...and disable following days
                    if (terminDauerTage > 0) {
                        $('#dates').multiDatesPicker('addDates', calculateDisabled(d), 'disabled');
                    }
                }

                // and re-sort input fields by start date
                var datesRoot = $('#dates-root');
                var dateLines = [].slice.call($('.dates-line'));
                dateLines.sort(function (a, b) {
                    return (convertDate(b.id) - convertDate(a.id));
                });
                dateLines.forEach(function (v) {
                    v.remove();
                    datesRoot.after(v);
                });
                initOpenEnd();
            }
        });
    });

}