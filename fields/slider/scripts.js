"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $sliders = $('.jsAlchemySlider');

    if( $sliders[0] ) {
        $sliders.each((i, el) => {
            initialise_slider(el);
        });
    }

    $(document).on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $sliders = $repeatee.find('.jsAlchemySlider');

        if( $sliders[0] ) {
            $sliders.each((i, el) => {
                initialise_slider(el);
            });
        }
    });

    function initialise_slider(slider) {
        const $slider = $(slider);
        const data = $slider.data('values');

        if( ! data ) {
            return;
        }

        const $input = $slider.closest('.jsAlchemyField').find('.jsAlchemySliderInput');
        const min = data.min ? data.min : 0;
        const max = data.max ? data.max : 100;
        const step = data.step ? data.step : 1;

        $slider.slider({
            min: min,
            max: max,
            range: "min",
            step: step,
            value: $input.val(),
            slide: function( event, ui ) {
                $input.val(ui.value);
            }
        });
    }

    AO.get_slider_value = id => {
        return Promise.resolve( {
            'type': 'slider',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);