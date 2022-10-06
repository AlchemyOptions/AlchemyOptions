"use strict";

(function(window, document, $){
    window.AO = window.AO || {};

    $(() => {
        const $repeaters = $('.jsAlchemyRepeaterField');

        if( ! $repeaters[0] ) {
            return;
        }

        const $document = $(document);
        const addRepeateeData = AlchemyRepeatersData['add-repeatee'];
        const cloneRepeateeData = AlchemyRepeatersData['clone-repeatee'];
        const miscRepeaterData = AlchemyRepeatersData['misc'];
        const currentUrl = new URL(window.location.href);
        const pageID = currentUrl.searchParams.get('page') || currentUrl.searchParams.get('post');

        $repeaters.each((i, repeater) => {
            const $repeater = $(repeater);
            const $repeateesContainer = $repeater.children('.field__content').children('.jsAlchemyRepeaterItems');

            let repeateesCount = $repeateesContainer.children('.jsAlchemyRepeatee').length;

            $repeater.on('click', '.jsAlchemyAddRepeatee', function(e){
                e.stopPropagation();

                const $trigger = $(this);
                const $repeater = $trigger.closest('.jsAlchemyRepeaterField');
                const alchemy = $repeater.data('alchemy');
                const repeaterData = $trigger.data('repeater');
                const $repeateesContainer = $repeater.children('.field__content').children('.jsAlchemyRepeaterItems');
                const data = new FormData();

                $trigger.attr('disabled', true).next('.jsAlchemyRepeaterLoader').removeClass('alchemy__spinner--hidden');

                data.append('_wpnonce', addRepeateeData.nonce);
                data.append('page-id', pageID);
                data.append('repeatees-number', ++repeateesCount);
                data.append('id', alchemy['id']);
                data.append('repeater-id', alchemy['repeater-id']);

                if( repeaterData && repeaterData['type'] ) {
                    data.append('type-id', repeaterData['type'].id);
                    data.append('type-title', repeaterData['type'].title);
                }

                $.ajax({
                    method: "POST",
                    url: addRepeateeData.url,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    data: data,
                    success: response => {
                        if( response.success && response.data ) {
                            const $response = $(response.data);

                            $repeateesContainer.append($response);

                            $document.trigger('alch_repeatee_added', {
                                repeater: $repeater,
                                repeatee: $response,
                            });
                        }
                    },
                    complete: () => {
                        $document.click();
                        $trigger.removeAttr('disabled').next('.jsAlchemyRepeaterLoader').addClass('alchemy__spinner--hidden');
                    }
                });
            });

            $repeater.on('click', '.jsAlchemyRepeateeToolbar', function(e) {
                e.stopPropagation();

                $document.click();

                const $toolbar = $(this);
                const $repeatee = $toolbar.closest( '.repeatee' );

                if( $repeatee.hasClass('repeatee--expanded') ) {
                    $repeatee.removeClass('repeatee--expanded');

                    $document.trigger('alch_repeatee_closed', {
                        repeater: $repeater,
                        repeatee: $repeatee,
                    });
                } else {
                    $repeatee.addClass('repeatee--expanded');

                    $document.trigger('alch_repeatee_opened', {
                        repeater: $repeater,
                        repeatee: $repeatee,
                    });
                }
            });

            $repeater.on('click', '.jsAlchemyRepeateeRemove', function() {
                const $toolbar = $(this);
                const $parent = $toolbar.closest( '.repeatee' );

                $parent.fadeOut(() => {
                    $parent.remove();
                });
            });

            $repeater.on('click', '.jsAlchemyRepeateeAction', function(e) {
                e.stopPropagation();

                const $trigger = $(this);
                const $repeatee = $trigger.closest( '.repeatee' );

                switch( $trigger.data('action') ) {
                    case 'hide' :
                        handle_hide_repeatee( $trigger, $repeatee );
                    break;
                    case 'clone' :
                        (() => {
                            e.stopPropagation();

                            const $trigger = $(this);
                            const $repeater = $trigger.closest('.jsAlchemyRepeaterField');
                            const alchemy = $repeater.data('alchemy');
                            const data = new FormData();

                            $trigger.attr('disabled', true).next('.jsAlchemyRepeaterLoader').removeClass('alchemy__spinner--hidden');

                            data.append('_wpnonce', cloneRepeateeData.nonce);
                            data.append('page-id', pageID);
                            data.append('repeatees-number', ++repeateesCount);
                            data.append('id', alchemy['id']);
                            data.append('repeater-id', alchemy['repeater-id']);

                            const repeateeFields = $repeatee.children('.repeatee__fields').children('.jsAlchemyField').map((k, field) => {
                                const alchemy = $(field).data('alchemy');

                                return new Function( `return AO['get_${alchemy.type}_value']('${alchemy.id}');` )(alchemy.id)
                            }).get()

                            Promise.all(repeateeFields).then(values => {
                                const meta = $.extend({}, $repeatee.data('meta'));

                                meta.label = meta.label ? `${meta.label} copy` : '';

                                if( meta['title'] && meta['id'] ) {
                                    data.append('type-id', meta['id']);
                                    data.append('type-title', meta['title']);
                                }

                                values = correct_ids( alchemy, [{
                                    'meta': meta,
                                    'values': values
                                }] )

                                data.append('values', JSON.stringify(values));

                                $.ajax({
                                    method: "POST",
                                    url: cloneRepeateeData.url,
                                    dataType: 'json',
                                    processData: false,
                                    contentType: false,
                                    data: data,
                                    success: response => {
                                        if( response.success && response.data ) {
                                            const $response = $(response.data);

                                            $repeatee.after($response);

                                            $document.trigger('alch_repeatee_added', {
                                                repeater: $repeater,
                                                repeatee: $response,
                                            });
                                        }
                                    },
                                    complete: () => {
                                        $document.click();
                                        $trigger.removeAttr('disabled').next('.jsAlchemyRepeaterLoader').addClass('alchemy__spinner--hidden');
                                    }
                                });
                            });
                        })();
                    break;
                    case 'delete' :
                        handle_delete_repeatee( $repeatee );
                    break;
                }
            });

            $repeater.on('click', '.jsAlchemyRepeaterTypeTrigger', function(e){
                const $trigger = $(this);
                const $parent = $trigger.closest('.repeater__add-from-type');
                const $list = $trigger.siblings('.jsAlchemyRepeaterFieldTypes');

                e.stopPropagation();

                $parent.addClass('repeater__add-new--active');
                $list.addClass('repeater__field-types--visible');

                $document.one('click', function() {
                    $parent.removeClass('repeater__add-new--active');
                    $list.removeClass('repeater__field-types--visible');
                });
            });

            $repeater.on('click', '.jsAlchemyRepeaterFieldTypes', function(e){
                e.stopPropagation();
            });

            $repeater.on({
                'input': function() {
                    const $input = $(this);
                    const $repeatee = $input.closest( '.repeatee' );
                    const metaData = $repeatee.data('meta');
                    const value = $input.val();

                    metaData.label = value;
                    $input.prev('.jsRepeateeTitleText').text(value);
                },
                'click': e => {
                    e.stopPropagation();
                }
            }, '.jsRepeateeTitle');

            $repeateesContainer.sortable({
                placeholder: "repeatee--placeholder",
                opacity: 0.8,
                axis: "y",
                handle: ".jsRepeateeDndHandle",
                start: (event, ui) => {
                    $repeateesContainer.find('.repeatee--placeholder').height(ui.helper.height() - 2);

                    AO.tinymce.save_editors( $('.jsAlchemyEditor', $repeater) );
                },
                stop: () => {
                    AO.tinymce.restore_editors( $('.jsAlchemyEditor', $repeater) );
                }
            });

            $repeateesContainer.disableSelection();

            const $select = $repeater.find('.jsAlchRepeaterSelect').children('select');
            const placeholder = miscRepeaterData['add-from-type'] ? miscRepeaterData['add-from-type'] : 'Add from type';

			$select.select2({ placeholder }).on('select2:select', e => {
				$document.click();

				const $trigger = $(e.target);
				const option = $trigger.children(`[value="${e.params.data.id}"]`);
				const $repeater = $trigger.closest('.jsAlchemyRepeaterField');
				const alchemy = $repeater.data('alchemy');
				const repeaterData = option.data('repeater');
				const $repeateesContainer = $repeater.children('.field__content').children('.jsAlchemyRepeaterItems');
				const data = new FormData();

				$trigger.siblings('.jsAlchemyRepeaterLoader').removeClass('alchemy__spinner--hidden');

				data.append('_wpnonce', addRepeateeData.nonce);
				data.append('page-id', pageID);
				data.append('repeatees-number', ++repeateesCount);
				data.append('id', alchemy['id']);
				data.append('repeater-id', alchemy['repeater-id']);

				if( repeaterData && repeaterData['type'] ) {
					data.append('type-id', repeaterData['type'].id);
					data.append('type-title', repeaterData['type'].title);
				}

				$.ajax({
					method: "POST",
					url: addRepeateeData.url,
					dataType: 'json',
					processData: false,
					contentType: false,
					data: data,
					success: response => {
						if( response.success && response.data ) {
							const $response = $(response.data);

							$repeateesContainer.append($response);

                            $document.trigger('alch_repeatee_added', {
                                repeater: $repeater,
                                repeatee: $response,
                            });
						}
					},
					complete: () => {
						$trigger.removeAttr('disabled').val("").change().blur().siblings('.jsAlchemyRepeaterLoader').addClass('alchemy__spinner--hidden');
					}
				});
			});
        });
    });

    function handle_hide_repeatee( $trigger, $repeatee ) {
        const metaData = $repeatee.data('meta');

        $trigger.toggleClass('repeatee__btn--active');
        $trigger.find('span').toggleClass('dashicons-visibility').toggleClass('dashicons-hidden');

        $repeatee.removeAttr('style').toggleClass('repeatee--hidden');

        metaData.visible = ( 'undefined' === typeof metaData.visible ) ? false : ! metaData.visible;
    }

    function handle_delete_repeatee( $repeatee ) {
        $repeatee.fadeOut(() => {
            $repeatee.remove();
        });
    }

    function correct_ids( alchemy, values ) {
        const hasTypes = typeof( alchemy['fields-data']['field-types'] ) !== 'undefined';

        let neededTypeFields = alchemy['fields-data']['fields'];

        $.each(values, (i, repeatee) => {
            if( ! hasTypes ) {
                $.each(repeatee.values, (k, value) => {
                    value['id'] = neededTypeFields[k]['id'];
                });
            } else {
                $.each(alchemy['fields-data']['field-types'], (l, item) => {
                    if( item.type === repeatee.meta.id ) {
                        neededTypeFields = item.fields
                    }
                });

                $.each(repeatee.values, (k, value) => {
                    value['id'] = neededTypeFields[k]['id'];
                });
            }
        });

        return values;
    }

    AO.get_repeater_value = id => {
        const $repeaters = $('.jsAlchemyRepeaterField');
        const $parent = $repeaters.filter((i, repeater) => {
            return id === $(repeater).data('alchemy')['id'];
        });

        const $repeatees = $parent.children('.field__content').children('.jsAlchemyRepeaterItems').children('.jsAlchemyRepeatee');

        const values = $repeatees.map((i, repeatee) => {
            const $repeatee = $(repeatee);
            const repeateeMeta = $repeatee.data('meta');
            const repeateeFields = $repeatee.children('.repeatee__fields').children('.jsAlchemyField').map((k, field) => {
                const alchemy = $(field).data('alchemy');

                return new Function( `return AO['get_${alchemy.type}_value']('${alchemy.id}');` )(alchemy.id)
            }).get()

            return Promise.all(repeateeFields).then(values => {
                return Promise.resolve( {
                    meta: repeateeMeta,
                    values: values
                } );
            });
        }).get();

        return Promise.all(values).then(valuesData => {
            return Promise.resolve( {
                'type': `repeater:${$parent.data('alchemy')['repeater-id']}`,
                'id': id,
                'value': correct_ids( $parent.data('alchemy'), valuesData )
            } );
        });
    };
})(window, document, jQuery);
