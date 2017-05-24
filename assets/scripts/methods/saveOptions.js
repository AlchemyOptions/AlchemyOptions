export default function() {
    const $form = $("#jsAlchemyForm");

    if( $form[0] ) {

        $form.on('submit', e => {
            console.log($form);
            console.log($form.serializeArray());

            e.preventDefault();

            $.ajax({
                'type': 'post',
                'url': alchemyData.adminURL,
                'data': {
                    'action': 'alchemy_save_options',
                    'nonce': alchemyData.nonce,
                    'fields': $form.serializeArray() //todo: find inputs and construct the fields data by hand, sending fieldsType, Name, Value
                },
                'success': data => {
                    console.log('success', data);
                },
                'error': err => {
                    console.error('error', err);
                }
            });
        });
    }
}