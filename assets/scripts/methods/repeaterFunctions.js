export default function() {
    const $repeaterFields = $('.jsAlchemyRepeaterField');

    if( $repeaterFields[0] ) {
        $repeaterFields.each((i, el) => {
            const $repeater = $(el);
            const $dropIn = $('.jsAlchemyRepeaterSortable', $repeater);

            let clickIndex = 0;

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
                        $dropIn.append(data);
                    },
                    'error': err => {
                        console.error('error', err);
                    }
                });

                clickIndex++;
            });
        });
    }
};
