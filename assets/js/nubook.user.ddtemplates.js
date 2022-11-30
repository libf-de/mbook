let btnTimeout;

function initDropdown() {
    jQuery(document).ready(function ($) {
        $(".user-template-titlebox").on('click', function () {
            $header = $(this);
            //getting the next element
            $content = $header.next();
            var contentVisible = $content.is(":visible");
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            if(!contentVisible) {
                $(this).addClass("user-template-titlebox-open");
            }
            
            var dropdownIcon = $($header).children(".user-template-dropdown");
            dropdownIcon.fadeOut(250, function() {
                if(!contentVisible) {
                    dropdownIcon.removeClass("fa-square-caret-down");
                    dropdownIcon.addClass("fa-square-caret-up");
                } else {
                    dropdownIcon.removeClass("fa-square-caret-up");
                    dropdownIcon.addClass("fa-square-caret-down");
                    
                }
            });
            dropdownIcon.fadeIn(250);

            $content.slideToggle(500, function() {
                if(contentVisible) {
                    $header.removeClass("user-template-titlebox-open");
                }
            });
        });

        $(".ws-fpr-bookbox").on("focus", preventFocus);
        $(".ws-fpr-bookbox").on("click", (ev) => { 
            clearTimeout(btnTimeout);
            var me = jQuery(ev.currentTarget);
            var code = "#" + me.closest('.ws-fpr-state').data("code");
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(code).select();
            document.execCommand("copy");
            $temp.remove();
            me.val("Kopiert");
            btnTimeout = setTimeout(() => {me.val(code);}, 1500); });

        if(typeof initBooking === "function") {
            initBooking();
        } else {
            console.log("WARN: Could not find initBooking(), booking buttons will *not* work!");
        }
    });
}

function preventFocus(event) {
    if (event.relatedTarget) {
      // Revert focus back to previous blurring element
      event.relatedTarget.focus();
    } else {
      // No previous focus target, blur instead
      this.blur();
      // Alternatively: event.currentTarget.blur();
    }
  }