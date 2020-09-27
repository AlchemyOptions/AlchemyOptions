"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    $(document).ready(() => {
        const $selectBoxes = $('.jsAlchemyDatalist');

        if( $selectBoxes[0] ) {
            $selectBoxes.each((i, select) => {
                const $select = $(select);

                $select.select2();

                $select.siblings('.jsAlchemyDatalistClear').on('click', () => {
                    $select.val("").change();
                });
            });
        }
    });

    AO.get_datalist_value = id => {
        return Promise.resolve( {
            'type': 'datalist',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);