"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    const $checkboxImages = $('.jsAlchemyCheckboxImage');

    if( $checkboxImages[0] ) {
        $checkboxImages.each((i, el) => {
            const $el = $(el);
            const $labels = $el.find('label');
            const isMultiple = $el.data('alchemy').multiple;

            $labels.filter((i, el) => {
                return ! $(el).hasClass('checkbox__label--disabled')
            }).attr('tabindex', 0);

            $el.find('input').attr('tabindex', -1);

            $el.on('click', '.jsAlchemyCheckboxImageLabel', function(e){
                e.preventDefault();

                const $label = $(this);

                if( $label.hasClass( 'checkbox__label--disabled' ) ) {
                    return;
                }

                if( ! isMultiple ) {
                    $labels.filter((i, el) => {
                        return ! $(el).is($label)
                    }).removeClass('checkbox__label--active').prev('input').prop('checked', false);
                }

                $label.toggleClass('checkbox__label--active').prev('input').prop('checked', $label.hasClass( 'checkbox__label--active' ));
            });

            $el.on('keypress', '.jsAlchemyCheckboxImageLabel', function(e){
                const $label = $(this);

                if( $label.hasClass( 'checkbox__label--disabled' ) ) {
                    return;
                }

                if( $.inArray( e.which, [13, 32] ) !== -1 ) {
                    e.preventDefault();

                    $label.toggleClass('checkbox__label--active').prev('input').prop('checked', $label.hasClass( 'checkbox__label--active' ));
                }
            });
        });
    }

    AO.get_checkbox_image_value = id => {
        const $el = $(`#${id}`);

        let value = '';

        if( $el.data('alchemy').multiple ) {
            value = $el.find(':checked').map((i, checkbox) => {
                return $(checkbox).data('value');
            }).get();
        } else {
            value = $el.find(':checked').data('value');
        }

        return Promise.resolve( {
            'type': 'checkbox_image',
            'id': id,
            'value': value
        } );
    };
})(window, jQuery);