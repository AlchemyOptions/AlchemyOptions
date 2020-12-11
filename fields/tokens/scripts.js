"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    $(document).ready(() => {
        const $selectBoxes = $('.jsAlchemyTokens');

        if( $selectBoxes[0] ) {
            $selectBoxes.each((i, select) => {
                const $select = $(select);

                $select.select2({
                    tags: true,
                    dropdownParent: $('.jsAlchemyTempEditor')
                });

                $select.siblings('.jsAlchemyTokensClear').on('click', () => {
                    $select.val("").change();
                });
            });
        }
    });

    AO.get_tokens_value = id => {
        return Promise.resolve( {
            'type': 'tokens',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);