function initBooking() {
    console.log("initBooking()");
    jQuery(".ws-fpr-bookbtn").on('click', (event) => {
        if(typeof WPDATA == "undefined") { alert('Der Webseitenbetreiber hat noch keine WhatsApp-Buchung eingerichtet!'); return; }
        if(typeof WPDATA.phone == "undefined") { alert('Der Webseitenbetreiber hat noch keine WhatsApp-Buchung eingerichtet!'); return; }
        if(WPDATA.phone == "0" || WPDATA.phone == "") { alert('Der Webseitenbetreiber hat noch keine WhatsApp-Buchung eingerichtet!'); return; }
        window.open("https://wa.me/" + WPDATA.phone + "?text=%23" + jQuery(event.currentTarget).closest(".ws-fpr-book").data("code"));
        //console.log("beep");
        //console.log(jQuery(event.currentTarget).parent().data("code"));
        /*if( != null) {
            const disId = document.getElementById(jQuery(event.currentTarget).data("disables-id"));
            disId.disabled = event.currentTarget.checked;
            disId.required = !event.currentTarget.checked;
        } else if(jQuery(event.currentTarget).data("disables-class") != null) {
            var boxes = document.getElementsByClassName(jQuery(event.currentTarget).data("disables-class")); 
            for (var i = 0; i < boxes.length; i++) { 
                boxes[i].disabled = event.currentTarget.checked;
                boxes[i].required = !event.currentTarget.checked;
            }
        }*/
      });
}