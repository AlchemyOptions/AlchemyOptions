(function($){
    $(document).ready(function(){
        var $notification = $('.jsAlchemyOptionsNotification');

        if( $notification[0] ) {
            var $actionButtons = $('.jsButton', $notification);
            var adminURL = $notification.data('admin-url');

            $notification.on('click', '.jsButton', function(){
                var $button = $(this);
                var data = {
                    'action': 'alchemy_options_notification_dismiss',
                    'type': $button.data('type'),
                    'nonce': $notification.data('nonce')
                };

                $actionButtons.attr('disabled', true);

                $.ajax({
                    'type': 'post',
                    'url': adminURL,
                    'data': data,
                    'success': function(){
                        $notification.slideUp();
                    },
                    'error': function(data){
                        console.log(data);

                        $notification.removeClass('notice-info').addClass('notice-error').find('p').first().text(data);

                        $actionButtons.removeAttr('disabled');
                    }
                });
            });
        }
    });
})(jQuery);