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
            e.preventDefault();

            $.each(formData, (name, fieldObj) => {
                switch (fieldObj.type) {
                    case 'text' :
                    case 'url' :
                    case 'password' :
                    case 'email' :
                    case 'tel' :
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
                    break;
                    case 'datalist' :
                        const $activeEl = $(`#field--${name}`).find(':selected, :checked');

                        if( $activeEl[0] ) {
                            if( 'INPUT' === $activeEl.get(0).nodeName ) {
                                formData[name]['value'] = $activeEl.data('value');
                            } else if( 'OPTION' === $activeEl.get(0).nodeName ) {
                                formData[name]['value'] = $activeEl.val();
                            }
                        } else {
                            formData[name]['value'] = $(`#field--${name}`).find('input').val();
                        }
                    break;
                    default : break;
                }
            });

            console.log(formData);

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