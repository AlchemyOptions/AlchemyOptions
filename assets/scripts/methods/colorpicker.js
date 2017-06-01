export default function () {
    const $colorpickers = $('.jsAlchemyColorpicker');

    if( $colorpickers[0] ) {
        const $html = $('html');

        $colorpickers.each((i, el) => {
            const $colorpicker = $(el);
            const $pickerInput = $colorpicker.find('input').on('click.alchemyColorpicker', e => { e.stopPropagation() });
            const $sampleBlock = $colorpicker.find('.jsAlchemyColorpickerSample');

            $pickerInput.iris( {
                palettes: true,
                change: function(event, ui) {
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

                $html.one('click.alchemyColorpicker', () => {
                    $pickerInput.iris('hide');
                });
            });
        });
    }
}