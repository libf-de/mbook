function initToggles() {
    jQuery(document).ready(function( $ ) {
        $(document).on('change', '.ft-list-parts', function() {
            let newValue;
            if($(this).attr("type") == "checkbox") {
                newValue = $(this).prop('checked') ? 1 : 0;
            } else if(!isNaN($(this).val()) && $(this).val() != "") {
                newValue = $(this).val();
            } else {
                return;
            }
            $.post(window.location.href, { action: "api-set-ft-parts", id: $(this).data('id'), val: newValue }, function(rsp) {
                console.log(rsp);
            })
        });
        $(".ft-list-btns").click(function () {
            let inpEl = $(this).parent().children('input[type=number]');
            if(inpEl.val() == "") inpEl.val(0);
            if($(this).val() == "+") {
                let nxtVal = parseInt(inpEl.val()) + 1;
                if(nxtVal <= inpEl.attr("max")) {
                    inpEl.val(nxtVal);
                    inpEl.change();
                }
            } else {
                let nxtVal = parseInt(inpEl.val()) - 1;
                if(nxtVal >= inpEl.attr("min")) {
                    inpEl.val(nxtVal);
                    inpEl.change();
                }
            }
        })
    });
    //alert("inittoggles");
}