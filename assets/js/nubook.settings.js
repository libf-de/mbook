function testGCProtection() {
    jQuery.ajax({
        url: WPURL.jsonURL,
        type: 'get',
        complete: function (data, txtStatus) {
            if(data.status == 200) {
                jQuery("#gcsec").text("VORSICHT: Die Anmeldedaten-JSON ist von außen zugreifbar. Stellen Sie sicher, dass die .htaccess-Datei '" + WPURL.jsonDIR + ".htaccess' funktioniert! DIES IST EIN SICHERHEITSRISIKO!");
                jQuery("#gcsec").addClass("fail")
                alert("VORSICHT: Die Anmeldedaten-JSON ist von außen zugreifbar. Stellen Sie sicher, dass die .htaccess-Datei '" + WPURL.jsonDIR + ".htaccess' funktioniert! DIES IST EIN SICHERHEITSRISIKO!");
            } else {
                jQuery("#gcsec").text("Die Anmeldedaten-JSON scheint von außen nicht zugänglich zu sein, das ist gut.");
                jQuery("#gcsec").addClass("ok");
            }
        },
    });
}

function initGCUpload() {
    console.log("initGCUpload");
    jQuery("#upload").on('click', (e) => {
        e.preventDefault();
        jQuery("#gcauth").trigger("click");
        return false;
    });

    jQuery("#gcauth").on("change", (e) => {
        jQuery("#google-form").submit();
    })
}