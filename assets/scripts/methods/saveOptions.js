import getFieldValue from './getFieldValue';

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


}