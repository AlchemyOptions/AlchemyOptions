"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    const $btnGroups = $('.jsAlchemyButtonGroup');

    if( $btnGroups[0] ) {
        $btnGroups.each((i, el) => {
            const $btnGroup = $(el);
            const $choices = $('.jsAlchemyButtonGroupChoice', $btnGroup);
            const isMultiple = $btnGroup.data('alchemy').multiple;

            $btnGroup.on('click', '.jsAlchemyButtonGroupChoice', function() {
                const $btn = $(this);

                if( ! isMultiple ) {
                    $choices.filter((i, el) => {
                        return ! $(el).is($btn)
                    }).removeClass('button-primary');
                }

                $btn.toggleClass('button-primary');
            });
        });
    }

    AO.get_button_group_value = id => {
        const $el = $(`#${id}`);

        let value = '';

        if( $el.data('alchemy').multiple ) {
            value = $el.find('.button-primary').map((i, checkbox) => {
                return $(checkbox).data('value');
            }).get();
        } else {
            value = $el.find('.button-primary').data('value');
        }

        return Promise.resolve( {
            'type': 'button_group',
            'id': id,
            'value': value
        } );
    };
})(window, jQuery);