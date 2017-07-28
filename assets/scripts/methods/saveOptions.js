export default function() {
    const $form = $("#jsAlchemyForm");

    if( $form[0] ) {
        const isNetworkForm = $form.data('is-network');
        const formData = {};
        const $formFields = $form.find( '.alchemy__fields' ).children('.alchemy__field');
        const $modal = $('.jsAlchemyModal');
        const $successModal = $('.alchemy__modal--success', $modal).css('opacity', 1).hide();
        const $errorModal = $('.alchemy__modal--error', $modal).css('opacity', 1).hide();
        const $submitBtns = $('.alchemy__btn--submit', $form);
        const $submitSpinners = $submitBtns.next();

        $formFields.each((i, field) => {
            const $field = $(field);
            const data = $field.data('alchemy');

            if( data ) {
                if( 'sections' === data.type ) {
                    $field.children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab').children('.alchemy__field').each((i, item) => {
                        const data = $(item).data('alchemy');

                        if( data ) {
                            formData[data.id] = {
                                type: data.type
                            };
                        }
                    });
                } else {
                    formData[data.id] = {
                        type: data.type
                    };
                }
            }
        });

        $form.on('submit', e => {
            e.preventDefault();

            $submitBtns.attr('disabled', true);
            $submitSpinners.css('opacity', 1);

            $.each(formData, name => {
                formData[name]['value'] = getFieldValue($(`#field--${name}`));
            });

            const data = {
                'action': 'alchemy_options_save_options',
                'nonce': alchemyData.nonce,
                'fields': formData
            };

            if( isNetworkForm ) {
                data.network = true;
            }

            $.ajax({
                'type': 'post',
                'url': alchemyData.adminURL,
                'data': data,
                'success': (data) => {
                    if( data.success ) {
                        $successModal.css('opacity', 1).show().addClass('fadeInDown');
                        setTimeout(() => {
                            $successModal.removeClass('fadeInDown').addClass('fadeOutUp').css('opacity', 0);
                        }, 2500);
                    } else {
                        $errorModal.css('opacity', 1).show().addClass('fadeInDown');

                        setTimeout(() => {
                            $errorModal.removeClass('fadeInDown').addClass('fadeOutUp').css('opacity', 0);
                        }, 2500);
                    }
                },
                'error': () => {
                    $errorModal.css('opacity', 1).show().addClass('fadeInDown');
                    setTimeout(() => {
                        $errorModal.removeClass('fadeInDown').addClass('fadeOutUp').css('opacity', 0);
                    }, 2000);
                },
                'complete': () => {
                    $submitBtns.removeAttr('disabled');
                    $submitSpinners.css('opacity', 0);
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
            case 'sections' :
                value = [];

                const $childFields = alchemyField.children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab').children('.alchemy__field');

                console.log($childFields);

                if( $childFields[0] ) {
                    $childFields.each((i, el) => {
                        const $el = $(el);
                        const data = $el.data('alchemy');

                        if( data ) {
                            value.push({
                                'type': data.type,
                                'value': getFieldValue($el)
                            })
                        }
                    });
                }
            break;
            case 'repeater' :
                value = [];

                const fields = alchemyField.children('fieldset').children('.field__content').children('.jsAlchemyRepeaterSortable').children('.repeatee');

                if( fields[0] ) {
                    fields.each((i, el) => {
                        const $repeatee = $(el);
                        const repeateeData = $repeatee.data('alchemy');
                        const $childFields = $repeatee.children('.repeatee__content').children('.alchemy__field');
                        const valueToStore = {
                            isVisible: $repeatee.children('.jsAlchemyRepeateeVisible').val(),
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
            case 'post-type-select' :
                const selectVal = alchemyField.find('select').val();

                value = {
                    'type': data['post-type'],
                    'ids': typeof selectVal === 'string' ? [selectVal]: selectVal
                };
            break;
            case 'taxonomy-select' :
                const taxSelectVal = alchemyField.find('select').val();

                value = {
                    'taxonomy': data.taxonomy,
                    'ids': typeof taxSelectVal === 'string' ? [taxSelectVal]: taxSelectVal
                };
            break;
            case 'datalist' :
                const datalistSelectVal = alchemyField.find('select').val();

                value = typeof datalistSelectVal === 'string' ? [datalistSelectVal]: datalistSelectVal;
            break;
            case 'field-group' :
                value = {};

                const $groupFields = alchemyField.children('fieldset').children('.jsAlchemyFiledGroupWrapper');

                if( $groupFields[0] ) {
                    $groupFields.each((i, el) => {
                        const $group = $(el);
                        const groupData = $group.data('fields');
                        const $childFields = $group.children('.alchemy__field');

                        if( groupData ) {
                            $.each(groupData, (ind, field) => {
                                value[field.id] = {
                                    'type': field.type,
                                    'value': getFieldValue( $childFields.eq(ind) )
                                };
                            });

                        }
                    });
                }
            break;
            default : break;
        }

        return value;
    }
}