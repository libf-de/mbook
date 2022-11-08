function initList() {
    jQuery(".fe-standard-course").on("click", (event) => {
        event.preventDefault();
        var ferienRoot = jQuery(event.currentTarget).closest(".mb-listelem-outer");
        
        jQuery.ajax({
            url: WPURL.festandard,
            type: 'post',
            data: {
                id: ferienRoot.data("id"),
            },
            complete: function(data, txtStatus) {
                if(data.status != 200) {
                    alert("FATAL: Could not set default ferien: Request to REST API failed (" + data.status + "):\nstatusText: " + data.statusText + "\nresponseText: " + txtStatus);
                } else {
                    var oldStd = jQuery(".button-green").first();
                    var oldStdBtn = oldStd.children(".fa-heart-circle-check").first();
                    oldStdBtn.removeClass("fa-heart-circle-check"); oldStdBtn.addClass("fa-heart");
                    oldStd.removeClass("button-green"); oldStd.addClass("button-primary");
                    
                    var newStd = jQuery(event.currentTarget).first();
                    var newStdBtn = newStd.children(".fa-solid");
                    newStdBtn.removeClass("fa-heart"); newStdBtn.addClass("fa-heart-circle-check");
                    newStd.removeClass("button-primary"); newStd.addClass("button-green");
                }
            }
        });
    });

    jQuery(".fe-delete-course").on("click", (event) => {
        var courseRoot = jQuery(event.currentTarget).closest(".mb-listelem-outer");
        if (confirm(`Möchten Sie die Ferien \"${courseRoot.find('.title').text()}\" UND ZUGEHÖRIGE KURSE wirklich löschen?`)) {
            var form = jQuery('<form action="' + WPURL.fedelete + '" method="post">' +
                '<input type="hidden" name="id" value="' + courseRoot.data("id") + '" />' +
                '</form>');
            jQuery('body').append(form);
            form.submit();
        }
    });
}