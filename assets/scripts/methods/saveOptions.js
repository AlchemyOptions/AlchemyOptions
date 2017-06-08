export default function() {
    const $form = $("#jsAlchemyForm");

    if( $form[0] ) {
        const formData = {};
        const $formFields = $form.find( '.alchemy__fields' ).children('.alchemy__field');

        $formFields.each((i, field) => {
            const data = $(field).data('alchemy');

            if( data ) {
                formData[data.id] = {
                    type: data.type
                };
            }
        });

        $form.on('submit', e => {
            e.preventDefault();

            $.each(formData, name => {
                formData[name]['value'] = getFieldValue($(`#field--${name}`));
            });

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

    function getFieldValue( alchemyField ) {
        const data = alchemyField.data('alchemy');

        if( ! data ) {
            return;
        }

        let value;

        switch (data.type) {
            case 'text' :
            case 'url' :
            case 'password' :
            case 'email' :
            case 'tel' :
            case 'select' :
            case 'textarea' :
            case 'colorpicker' :
            case 'datepicker' :
            case 'button-group' :
            case 'upload' :
            case 'slider' :
                value = alchemyField.find('input,select,textarea').val();
            break;
            case 'checkbox':
            case 'radio':
            case 'image-radio':
                value = [];

                alchemyField.find(':checked').each((i, el) => {
                    value.push($(el).data('value'));
                });
            break;
            case 'editor' :
                const $area = $('.jsAlchemyEditorTextarea ', alchemyField);

                if( $area.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
                    value = tinymce.get($area.attr('id')).getContent()
                } else {
                    value = $area.val();
                }
            break;
            case 'repeater' :
                value = [];

                const fields = alchemyField.find('.repeatee');

                if( fields[0] ) {
                    fields.each((i, el) => {
                        const $repeatee = $(el);
                        const repeateeData = $repeatee.data('alchemy');
                        const $childFields = $repeatee.children('.repeatee__content').children('.alchemy__field');
                        const valueToStore = {
                            isVisible: $repeatee.children('.jsAlchemyRepeateeVisible').val(),
                            type: repeateeData.repeatee_id,
                            title: repeateeData.repeatee_title,
                            fields: {}
                        };

                        if( repeateeData.fieldIDs ) {

                            $.each(repeateeData.fieldIDs, (ind, field) => {
                                valueToStore.fields[field.id] = {
                                    'type': field.type,
                                    'value': getFieldValue( $childFields.eq(ind) )
                                };
                            });

                        }

                        value.push(valueToStore);
                    });
                }
            break;
            default : break;
        }

        return value;
    }
}