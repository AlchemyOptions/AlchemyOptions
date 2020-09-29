'use strict';

((window, document, $) => {
    window.AO = window.AO || {};

    $(document).on('tinymce-editor-init', () => {
        AO.tinymce = AO.tinymce || {};
        AO.tinymce.initialSettings = tinyMCEPreInit.mceInit['alchemy-temp-editor'];
    });

    $(() => {
        const $alchemy = $('.jsAlchemy');
        const $metaboxes = $('.jsAlchemyMetaBox');

        if( ! $alchemy ) {
            return;
        }

        const $alchOptions = $('.jsAlchemyOptions');
        const currentUrl = new URL(window.location.href);
        const pageID = currentUrl.searchParams.get('page');
        const data = new FormData();
        const popperInstances = {};

        const $fields = $alchOptions.children('.jsAlchemyField');
        const constrFields = [];

        $fields.each((i, field) => {
            const $field = $(field);

            if( 'sections' === $field.data('alchemy').type ) {
                const $children = $field.find('.jsAlchemySectionsTab').children('.jsAlchemyField');

                if( $children[0] ) {
                    $children.each((i, child) => {
                        constrFields.push($(child));
                    });
                }
            } else {
                constrFields.push($field);
            }
        });

        $alchemy.on('click', '.jsAlchemySaveOptions', function() {
            const $button = $(this);
            const fieldsPromises = $(constrFields).map((i, $field) => {
                const alchemy = $field.data('alchemy');

                $field.removeClass('alchemy__field--invalid').children('.jsAlchemyValidationTooltip').find('.jsAlchemyTooltipText').text('');

                return new Function( `return AO['get_${alchemy.type}_value']('${alchemy.id}');` )(alchemy.id)
            }).get();

            $.each(popperInstances, (i, instance) => {
                instance.destroy();
            });

            $button.attr('disabled', true).next('.jsAlchemyLoader').removeClass('alchemy__spinner--hidden');

            let saveOptionsData = AlchemyData['save-options'];

            if( $button.data() && 'network' === $button.data('type') ) {
                saveOptionsData = AlchemyData['save-network-options'];
            }

            Promise.all(fieldsPromises).then(values => {
                data.append('_wpnonce', saveOptionsData.nonce);
                data.append('page-id', pageID);
                data.append('values', JSON.stringify(values));

                $.ajax({
                    method: "POST",
                    url: saveOptionsData.url,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    data: data,
                    success: () => {
                        window.location.reload();
                    },
                    error: error => {
                        if( error.responseJSON.data && 'alch-save-validation-errors' === error.responseJSON.code ) {
                            const messsages = error.responseJSON.data['invalid-fields'];

                            let topMostPosition = 0;

                            $(constrFields).each((i, $field) => {
                                const alchemy = $field.data('alchemy');

                                if( messsages[alchemy.id] ) {
                                    const $tooltip = $field.children('.jsAlchemyValidationTooltip');
                                    const blockTop = $field.offset().top;

                                    topMostPosition = -100 + ( blockTop > topMostPosition ? blockTop : topMostPosition );

                                    if( $tooltip[0] ) {
                                        $tooltip.attr('data-show', true).find('.jsAlchemyTooltipText').text(messsages[alchemy.id]);

                                        $field.addClass('alchemy__field--invalid').parents('.repeatee').addClass('repeatee--expanded');

                                        popperInstances[alchemy.id] = Popper.createPopper( $field[0], $tooltip[0], {
                                            placement: 'top',
                                            modifiers: [
                                                {
                                                    name: 'flip',
                                                    options: {
                                                        fallbackPlacements: ['top-start'],
                                                    },
                                                }
                                            ],
                                        } );

                                        $field.one('hover focus', () => {
                                            if( $field.hasClass('alchemy__field--invalid') ) {
                                                $field.removeClass('alchemy__field--invalid');

                                                if( popperInstances[alchemy.id] ) {
                                                    popperInstances[alchemy.id].destroy();

                                                    $tooltip.removeAttr('data-show');
                                                }
                                            }
                                        });

                                        if( topMostPosition ) {
                                            $('html').add('body').animate({ scrollTop: topMostPosition }, 500);
                                        }
                                    }
                                }
                            });
                        }

                        $button.next('.jsAlchemyLoader').addClass('alchemy__spinner--hidden');
                    },
                    complete: () => {
                        $button.removeAttr('disabled');
                    }
                });
            });
        });

        if( $metaboxes[0] ) {
            const $postForm = $('#post');
            const $postBox = $metaboxes.closest('.postbox');

            $postBox.find('.hndle').removeClass('hndle'); // this removes dnd for metaboxes

            // this removes metaboxes sorting buttons (WP 5.5). It is needed not to break WYSIWYGs
            $postBox.find('.handle-order-higher').remove();
            $postBox.find('.handle-order-lower').remove();

            if( $postForm[0] ) {
                $('#publish').on('click', function(e) {
                    $(this).attr('disabled', true);

                    save_metadata($postForm);

                    e.preventDefault();
                });
            } else if( wp.data && wp.data.subscribe ) {
                let saved = false; // helps against multiple save calls

                wp.data.subscribe(() => {
                    const editor = wp.data.select('core/editor');

                    if ( editor.isSavingPost() && ! editor.isAutosavingPost() && ! saved ) {
                        save_metadata();

                        saved = true;
                    }
                });
            }
        }

        function save_metadata($postForm) {
            const $fields = $metaboxes.children('.metabox__fields').children('.jsAlchemyField');
            const constrFields = [];

            $fields.each((i, field) => {
                const $field = $(field);

                if( 'sections' === $field.data('alchemy').type ) {
                    const $children = $field.find('.jsAlchemySectionsTab').children('.jsAlchemyField');

                    if( $children[0] ) {
                        $children.each((i, child) => {
                            constrFields.push($(child));
                        });
                    }
                } else {
                    constrFields.push($field);
                }
            });

            const fieldsPromises = $(constrFields).map((i, field) => {
                const alchemy = $(field).data('alchemy');

                return new Function( `return AO['get_${alchemy.type}_value']('${alchemy.id}');` )(alchemy.id)
            }).get();

            const saveMetaboxesData = AlchemyData['save-metaboxes'];

            Promise.all(fieldsPromises).then(values => {
                data.append('_wpnonce', saveMetaboxesData.nonce);
                data.append('post-id', saveMetaboxesData.postID);
                data.append('values', JSON.stringify(values));

                $.ajax({
                    method: "POST",
                    url: saveMetaboxesData.url,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    data: data,
                    success: () => {
                        if( $postForm && $postForm[0] ) {
                            $postForm.submit();
                        }
                    }
                });
            });
        }
    });
})(window, document, jQuery);
