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
