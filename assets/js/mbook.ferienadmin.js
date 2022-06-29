let selectedDates = [];
let terminDauerTage = 0;
let defaultStart = 0;
let defaultDuration = 0;

function addDaysToString(s,a) {
    return new Date(new Date(s).getTime() + 86400000*a);
}

function initAddFkTemplate() {
    const checkbox = document.getElementById('openEnd');

    checkbox.addEventListener('change', (event) => {
        var boxes = document.getElementsByClassName("duration-input"); 
        for (var i = 0; i < boxes.length; i++) { 
            boxes[i].disabled = event.currentTarget.checked;
            boxes[i].required = !event.currentTarget.checked;
        }
    });
}

function convertDate(d) {
    var p = d.split("-");
    return +(p[1] + p[2] + p[3]);
  } 

function initFerientermin() {
    jQuery(document).ready(function( $ ) {
        $('#template').change(function() {
            updatePicker();
        });

        updatePicker();
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
    console.log(endDate);
    endDate.setDate(endDate.getDate() + terminDauerTage);
    return endDate.getFullYear() + "-" + ('0' + (endDate.getMonth()+1)).slice(-2) + "-" + ('0' + endDate.getDate()).slice(-2);
}

function dateToHtmlIso(date) {
    return date.getFullYear() + "-" + ('0' + (date.getMonth()+1)).slice(-2) + "-" + ('0' + date.getDate()).slice(-2) + "T" + ('0' + date.getHours()).slice(-2) + ":" + ('0' + date.getMinutes()).slice(-2) + ":" + ('0' + date.getSeconds()).slice(-2);
}

function updatePicker() {
    jQuery(document).ready(function( $ ) {
        let today = new Date();
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);
        today.setMilliseconds(0);
        let selectedEntry = $('#template').find(':selected');
        terminDauerTage = selectedEntry.data('days');
        let defaultWeekday = selectedEntry.data('day');
        defaultStart = selectedEntry.data('start');
        defaultDuration = selectedEntry.data('duration');
        $('#dates').multiDatesPicker({
            dateFormat: 'yy-mm-dd',
            dayNamesMin: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ],
            beforeShowDay: function(d) {
                let dayISO = d.getDay()-1;
                console.log(d);
                dayISO = (dayISO >= 0 ? dayISO : dayISO + 7);
                return [true, (dayISO == $( "#template option:selected" ).data('day') ? "datepicker-highlight " : "")
                        + (d < today ? "datepicker-past " : "")];
            },
            onSelect: function(d,i) {
                var sdate = new Date(d);
                sdate.setHours(0);
                sdate.setMinutes(defaultStart);
                if($('#dates').multiDatesPicker('gotDate', d) === false) {
                    // If date was removed from selection, remove input field and possibly following disabled days
                    $('#date-' + d).remove();
                    if(terminDauerTage > 0) {
                        $('#dates').multiDatesPicker('removeDates', calculateDisabled(d), 'disabled');
                    }
                } else {
                    // If date was added to selection, add input fields...
                    let endDate = new Date(sdate.valueOf());
                    endDate.setMinutes(endDate.getMinutes() + defaultDuration);
                    //$('.selected-dates:last').after("<tr id=\"date-" + d + "\" class=\"selected-dates dates-line\" valign=\"top\"><th scope=\"row\">&nbsp;</th><td><input type=\"date\" size=\"6\" required name=\"dates-" + d + "-date\" class=\"datum dates-line-date\" value=\"" + d + "\" readonly><input type=\"time\" class=\"startTime dates-line-time\" required step=\"60\" value=\"" + ('0' + sdate.getHours()).slice(-2) + ":" + ('0' + sdate.getMinutes()).slice(-2) + "\" min=\"00:00\" max=\"23:59\" name=\"dates-" + d + "-start\"> Uhr bis  <input type=\"datetime-local\" size=\"6\" name=\"dates-" + d + "-end\" class=\"datum\" min=\"" + dateToHtmlIso(sdate) + "\" value=\"" + dateToHtmlIso(endDate) + "\" required> Uhr</td></tr>"); //TODO: Set min value properly;  <input type=\"date\" size=\"6\" name=\"dates-" + d + "-enddate\" class=\"datum dates-line-date\" min=\"" + d + "\" value=\"" + calculateEndDay(d) + "\" required><input type=\"time\" class=\"endTime dates-line-time\" required step=\"60\" min=\"00:00\" max=\"23:59\" name=\"dates-" + d + "-end\">
                    $('.selected-dates:last').after("<tr id=\"date-" + d + "\" class=\"selected-dates dates-line\" valign=\"top\"><th scope=\"row\">&nbsp;</th><td><input type=\"date\" size=\"6\" required name=\"dates[" + d + "][date]\" class=\"datum dates-line-date\" value=\"" + d + "\" readonly><input type=\"time\" class=\"startTime dates-line-time\" required step=\"60\" value=\"" + ('0' + sdate.getHours()).slice(-2) + ":" + ('0' + sdate.getMinutes()).slice(-2) + "\" min=\"00:00\" max=\"23:59\" name=\"dates[" + d + "][start]\"> Uhr bis  <input type=\"datetime-local\" size=\"6\" name=\"dates[" + d + "][end]\" class=\"datum\" min=\"" + dateToHtmlIso(sdate) + "\" value=\"" + dateToHtmlIso(endDate) + "\" required> Uhr</td></tr>");
                    // ...and disable following days
                    if(terminDauerTage > 0) {
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
            }
        });
    });
    
}