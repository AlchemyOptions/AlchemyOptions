"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $document = $(document);

    $document.ready(() => {
        const $selectBoxes = $('.jsAlchemyTokens');

        if( $selectBoxes[0] ) {
            $selectBoxes.each((i, select) => {
                initialise_token_field(select);
            });
        }
    });

    $document.on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $selectBoxes = $repeatee.find('.jsAlchemyTokens');

        if( $selectBoxes[0] ) {
            $selectBoxes.each((i, select) => {
                initialise_token_field(select);
            });
        }
    });

    function initialise_token_field(select) {
        const $select = $(select);

        $select.select2({
            tags: true,
            dropdownParent: $('.jsAlchemyTempEditor')
        });

        $select.siblings('.jsAlchemyTokensClear').on('click', () => {
            $select.val("").change();
        });
    }

    AO.get_tokens_value = id => {
        return Promise.resolve( {
            'type': 'tokens',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);