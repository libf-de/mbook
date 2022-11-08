function initListLTemplate() {
    jQuery(".lt-delete-lesson").on("click", (event) => {
        if (confirm("Möchten Sie die \"" + jQuery(event.currentTarget).data("title") + "\"-Vorlage wirklich löschen?\nALLE ZUGEHÖRIGEN UNTERRICHTSSTUNDEN WERDEN EBENFALLS GELÖSCHT!")) {
            var form = jQuery('<form action="' + WPURL.ltdelete + '" method="post">' +
                '<input type="hidden" name="id" value="' + jQuery(event.currentTarget).data("id") + '" />' +
                '</form>');
            jQuery('body').append(form);
            form.submit();
        }
    });
}