export default function() {
    const $repeaterFields = $('.jsAlchemyRepeaterField');

    if( $repeaterFields[0] ) {
        $repeaterFields.each((i, el) => {
            const $repeater = $(el);
            const $dropIn = $('.jsAlchemyRepeaterSortable', $repeater);

            let clickIndex = $dropIn.children().length;

            $repeater.on('click', '.jsAlchemyRepeaterAdd', function() {
                const $btn = $(this);
                const nonce = $btn.data('nonce');
                const rID = $btn.data('repeater-id');
                const repeateeID = $btn.data('repeatee-id');

                $.ajax({
                    'type': 'get',
                    'url': alchemyData.adminURL,
                    'data': {
                        'action': 'alchemy_repeater_item_add',
                        'nonce': [nonce.id, nonce.value],
                        'repeater': [rID, repeateeID],
                        'index': clickIndex
                    },
                    'success': data => {
                        console.log('success');

                        const $data = $(data);
                        $data.find('.field--text').eq(0).find('input').addClass('jsAlchemyRepeateeTitle');

                        $dropIn.append($data);
                        $dropIn.sortable( "refresh" );
                    },
                    'error': err => {
                        console.error('error', err);
                    }
                });

                clickIndex++;
            });

            $repeater.on('input', '.jsAlchemyRepeateeTitle', function() {
                const $input = $(this);

                $input.closest('.repeatee').children('.jsAlchemyRepeateeToolbar').find('.jsAlchemyRepeateeTitle').text($input.val());
            });

            $repeater.on('click', '.jsAlchemyRepeateeToolbar', function(e) {
                const $toolbar = $(this);

                $toolbar.closest( '.repeatee' ).toggleClass('repeatee--expanded')
            });

            $repeater.on('click', '.jsAlchemyRepeateeHide', function(e) {
                const $toolbar = $(this);
                const $parent = $toolbar.closest( '.repeatee' );
                const $visibilityInput = $parent.find('.jsAlchemyRepeateeVisible');

                $toolbar.find('span').toggleClass('dashicons-hidden');
                $parent.removeAttr('style').toggleClass('repeatee--hidden');

                $visibilityInput.val( $visibilityInput.val() === 'true' ? 'false' : 'true' );
            });

            $repeater.on('click', '.repeatee__actions', e => {
                e.stopPropagation();
            });

            $dropIn.sortable({
                placeholder: "repeatee--placeholder",
                opacity: 0.8,
                start: function( event, ui ) {
                    $dropIn.find('.repeatee--placeholder').height( ui.helper.height() - 2 )
                }
            });

            $dropIn.disableSelection();
        });
    }
};
