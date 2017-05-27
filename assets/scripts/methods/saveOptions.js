export default function() {
    const $form = $("#jsAlchemyForm");

    if( $form[0] ) {
        const formData = {};
        const $formFields = $form.find( '.field' );

        $formFields.each((i, field) => {
            const data = $(field).data('alchemy');

            formData[data.id] = {
                type: data.type
            };
        });

        $form.on('submit', e => {
            $.each(formData, (name, fieldObj) => {
                switch (fieldObj.type) {
                    case 'text' :
                    case 'url' :
                    case 'password' :
                    case 'email' :
                    case 'select' :
                    case 'textarea' :
                        formData[name]['value'] = $(`#${name}`).val();
                    break;
                    case 'checkbox':
                    case 'radio':
                        formData[name]['value'] = [];

                        $(`#field--${name}`).find(':checked').each((i, el) => {
                            formData[name]['value'].push($(el).data('value'));
                        });
                    default : break;
                }
            });

            console.log(formData);

            e.preventDefault();

            $.ajax({
                'type': 'post',
                'url': alchemyData.adminURL,
                'data': {
                    'action': 'alchemy_save_options',
                    'nonce': alchemyData.nonce,
                    'fields': formData
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