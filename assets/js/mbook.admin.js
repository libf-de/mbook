function initAddFkTemplate() {
    const checkbox = document.getElementById('openEnd');

    checkbox.addEventListener('change', (event) => {
        var boxes = document.getElementsByClassName("duration-input"); 
        for (var i = 0; i < boxes.length; i++) { 
            boxes[i].disabled = event.currentTarget.checked;
        }
    });
}

function pad(num, size) {
    num = num.toString();
    while (num.length < size) num = "0" + num;
    return num;
}

function addDateField() {
    var numCountInp = document.getElementById("datesCount");
    var dateInp = document.querySelectorAll('.datum');
    var numCount = dateInp.length;
    numCount++;
    numCountInp.value = numCount;
    var nxtDate = new Date(new Date().setDate(new Date(dateInp[dateInp.length-1].value).getDate() + 7));
    var nxtDateStr = nxtDate.getFullYear() + "-" + pad(nxtDate.getMonth() + 1, 2) + "-" + nxtDate.getDate();
    var sttInp = document.querySelectorAll('.startTime');
    var endInp = document.querySelectorAll('.endTime');
    var newrow = "<tr valign=\"top\"><th scope=\"row\">&nbsp;</th><td><input type=\"date\" name=\"dates[" + numCount + "][date]\" class=\"datum\" value=\"" + nxtDateStr + "\">, &nbsp;&nbsp;<input type=\"time\" class=\"startTime\" required min=\"00:00\" max=\"23:59\" name=\"dates[" + numCount + "][start]\" value=\"" + sttInp[sttInp.length-1].value + "\"> bis <input type=\"time\" class=\"endTime\" required min=\"00:00\" max=\"23:59\" name=\"dates[" + numCount + "][end]\" value=\"" + endInp[endInp.length-1].value + "\"><input type=\"checkbox\" name=\"dates[" + numCount + "][use]\" value=\"true\" checked></td></tr>";
    document.getElementById("addDateRow").insertAdjacentHTML('beforebegin', newrow);
}