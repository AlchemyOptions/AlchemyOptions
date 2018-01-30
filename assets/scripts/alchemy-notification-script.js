(function(document, $){
    $(document).ready(function(){
        var $notification = $('.jsAlchemyOptionsNotification');
        var maxAge;

        if( $notification[0] ) {
            var $actionButtons = $('.jsButton', $notification);

            $notification.on('click', '.jsButton', function(){
                var buttonType = $(this).data('type');

                $actionButtons.attr('disabled', true);

                if( 'hide' === buttonType ) {
                    maxAge = 31536000; // one year in seconds - 60 * 60 * 24 * 365
                }

                if( 'dismiss' === buttonType ) {
                    maxAge = 86400; // one day in seconds - 60 * 60 * 24
                }

                document.cookie = "alchemy-options-notice-dismissed=1;max-age=" + maxAge;

                $notification.slideUp();
            });
        }
    });
})(document, jQuery);