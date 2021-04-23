"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $colorpickers = $('.jsAlchemyColorpicker');
    const $document = $(document);

    if( $colorpickers[0] ) {
        $colorpickers.each((i, el) => {
            initialise_colorpicker(el);
        });
    }

    $document.on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $colorpickers = $repeatee.find('.jsAlchemyColorpicker');

        if( $colorpickers[0] ) {
            $colorpickers.each((i, el) => {
                initialise_colorpicker(el);
            });
        }
    });

    function initialise_colorpicker(colorpicker) {
        const $colorpicker = $(colorpicker);
        const $pickerInput = $colorpicker.find('input').on('click.alchemyColorpicker', e => { e.stopPropagation() });
        const $sampleBlock = $colorpicker.find('.jsAlchemyColorpickerSample');

        $pickerInput.iris( {
            palettes: true,
            change: (event, ui) => {
                $sampleBlock.css('backgroundColor', ui.color.toString());
            }
        } );

        $colorpicker.find( '.iris-picker' ).on('click.alchemyColorpicker', e => { e.stopPropagation() });

        $colorpicker.on('click.alchemyColorpickerDelete', '.jsAlchemyColorpickerClear', () => {
            $pickerInput.val("");
            $sampleBlock.css('backgroundColor', 'transparent');
        });

        $pickerInput.on('focus', function(){
            $pickerInput.iris('show');

            $document.one('click.alchemyColorpicker', () => {
                $pickerInput.iris('hide');
            });
        });
    }

    AO.get_colorpicker_value = id => {
        return Promise.resolve( {
            'type': 'colorpicker',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);