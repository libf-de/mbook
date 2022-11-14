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