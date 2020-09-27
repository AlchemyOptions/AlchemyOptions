"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    const $datepickers = $('.jsAlchemyDatepickerInput');

    if( $datepickers[0] ) {
        $datepickers.each((i, el) => {
            const $datepicker = $(el);
            const defaultSettings = {
                dateFormat: 'yy-mm-dd',
            }

            let datepickerSettings = {};

            if( AlchemyDatepickersData && AlchemyDatepickersData[$datepicker.attr('id')] ) {
                datepickerSettings = AlchemyDatepickersData[$datepicker.attr('id')];
            }

            const settings = $.extend({}, defaultSettings, datepickerSettings);

            $datepicker.datepicker(settings);
        });
    }

    AO.get_datepicker_value = id => {
        return Promise.resolve( {
            'type': 'datepicker',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);