export default function () {
    const $datalists = $('.jsAlchemyDatalist');

    if( $datalists[0] ) {
        $datalists.each((i, el) => {
            const $datalist = $(el);
            const cache = {};
            const source = $datalist.hasClass( 'jsAlchemyDatalistInplace' )
                ? $datalist.data('source')
                : ( req, res ) => {
                    const term = req.term;

                    if ( term in cache ) {
                        res( cache[ term ] );
                        return;
                    }

                    $.ajax({
                        'type': 'get',
                        'url': alchemyData.adminURL,
                        'data': {
                            'action': 'alchemy_save_options',
                            'nonce': alchemyData.nonce,
                            'fields': formData
                        },
                        'success': data => {
                            console.log('success', data);

                            cache[ term ] = data;
                            res( data );
                        },
                        'error': err => {
                            console.error('error', err);
                        }
                    });
                };

            $datalist.find('.jsAlchemyDatalistInput').autocomplete({
                minLength: 2,
                source
            });
        });
    }
}