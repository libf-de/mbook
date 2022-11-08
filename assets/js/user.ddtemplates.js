function initDropdown() {
    jQuery(document).ready(function ($) {
        $(".user-template-titlebox").click(function () {
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
    });
}