export default function( scope = document ) {
    const $selectBoxes = $('.jsAlchemyPostTypeSelectBlock', scope);

    if( $selectBoxes[0] ) {
        $selectBoxes.each((i, el) => {
            const $el = $(el);
            const fieldData = $el.data('alchemy');
            const $select = $('.jsAlchemyPostTypeSelect', $el);
            const nonce = $select.data('nonce');

            $select.select2({
                ajax: {
                    url: alchemyData.adminURL,
                    type: 'get',
                    dataType: 'json',
                    delay: 250,
                    data: params => {
                        return {
                            'searchedFor': params.term,
                            'action': 'alchemy_post_type_selection',
                            'nonce': [nonce.id, nonce.value],
                            'post-type': fieldData['post-type']
                        };
                    },
                    processResults: data => {
                        return {
                            results: data.data,
                        };
                    },
                    cache: false
                },
                minimumInputLength: 2
            });

            $el.on('click', '.jsAlchemyPostTypeSelectClear', () => {
                $select.val("").change();
            });
        });
    }
}