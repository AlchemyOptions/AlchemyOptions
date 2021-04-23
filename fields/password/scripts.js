"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $passwordToggles = $('.jsAlchemyTogglePassword');

    if( $passwordToggles[0] ) {
        $passwordToggles.each((i, el) => {
            initialise_password_toggle($(el));
        });
    }

    $(document).on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $passwordToggles = $repeatee.find('.jsAlchemyTogglePassword');

        if( $passwordToggles[0] ) {
            $passwordToggles.each((i, el) => {
                initialise_password_toggle($(el));
            });
        }
    });

    function initialise_password_toggle($passwordToggle) {
        $passwordToggle.on('click', function() {
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
})(window, document, jQuery);