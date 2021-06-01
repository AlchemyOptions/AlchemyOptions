"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $datepickers = $('.jsAlchemyDatepickerInput');

    if( $datepickers[0] ) {
        $datepickers.each((i, el) => {
            initialise_datepicker(el);
        });
    }

    $(document).on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $datepickers = $repeatee.find('.jsAlchemyDatepickerInput');

        if( $datepickers[0] ) {
            $datepickers.each((i, el) => {
                initialise_datepicker(el);
            });
        }
    });

    function initialise_datepicker(datepicker) {
        const $datepicker = $(datepicker);
        const defaultSettings = {
            dateFormat: 'yy-mm-dd',
        }

        let datepickerSettings = {};

        if( 'undefined' !== typeof ( window.AlchemyDatepickersData ) && window.AlchemyDatepickersData[$datepicker.attr('id')] ) {
            datepickerSettings = window.AlchemyDatepickersData[$datepicker.attr('id')];
        }

        const settings = $.extend({}, defaultSettings, datepickerSettings);

        $datepicker.datepicker(settings);
    }

    AO.get_datepicker_value = id => {
        return Promise.resolve( {
            'type': 'datepicker',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);