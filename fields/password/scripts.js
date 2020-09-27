"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    const $passwordToggles = $('.jsAlchemyTogglePassword');

    if( $passwordToggles[0] ) {
        $passwordToggles.on('click', function() {
            const $toggle = $(this);
            const $target = $toggle.prev('input');

            $toggle.find('span').toggleClass('dashicons-lock').toggleClass('dashicons-unlock');
            $target.attr('type', $target.attr('type') === 'text' ? 'password' : 'text');
        });
    }

    AO.get_password_value = id => {
        return Promise.resolve( {
            'type': 'password',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);