"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $spacers = $('.jsAlchemySpacer');

    if( $spacers[0] ) {
        $spacers.each((i, el) => {
            initialise_spacer(el);
        });
    }

    $(document).on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $spacers = $repeatee.find('.jsAlchemySpacer');

        if( $spacers[0] ) {
            $spacers.each((i, el) => {
                initialise_spacer(el);
            });
        }
    });

    function initialise_spacer(spacer) {
        const $spacer = $(spacer);

        let interval;
        let increment = 1;
        let rate = 1;

        $spacer.on({
            'mousedown': function(){
                interval = setInterval(update_input($(this)), 150)
            },
            'mouseleave': stop_input_update,
            'click': function() {
                stop_input_update();
                update_input($(this))();
            },
            'keydown': function(e) {
                if( 13 === e.which || 32 === e.which ) {
                    e.preventDefault();

                    update_input($(this))();
                }
            },
            'keyup': stop_input_update
        }, '.jsSpacerButton');

        $spacer.on('blur', 'input', function() {
            const $input = $(this);

            if( ! $input.val() ) {
                $input.val(0);
            }
        });

        function stop_input_update() {
            increment = 1;
            rate = 1;

            clearInterval(interval);
        }

        function update_input($trigger) {
            const $input = $trigger.siblings('input');

            return function() {
                const currentValue = $input.val();

                let newVal;

                if( 'decr' === $trigger.data('type') ) {
                    newVal = parseInt( currentValue ) - increment;
                } else {
                    newVal = parseInt( currentValue ) + increment;
                }

                if( newVal <= 0 ) {
                    newVal = 0;
                }

                if( rate > 15 ) {
                    increment = 11;
                }

                if( rate > 25 ) {
                    increment = 21;
                }

                if( rate > 35 ) {
                    increment = 31;
                }

                $input.val(newVal);
                rate++;
            }
        }
    }

    AO.get_spacer_value = id => {
        const $spacer = $(`#${id}`);

        return Promise.resolve( {
            'type': 'spacer',
            'id': id,
            'value': {
                'top': $spacer.find(`#${id}_top`).val(),
                'right': $spacer.find(`#${id}_right`).val(),
                'bottom': $spacer.find(`#${id}_bottom`).val(),
                'left': $spacer.find(`#${id}_left`).val(),
            }
        } );
    };
})(window, document, jQuery);