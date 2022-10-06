"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $document = $(document);

    $document.ready(() => {
        const $ptsSelects = $('.jsAlchemyTaxonomySelect');

        if( $ptsSelects[0] ) {
            $ptsSelects.each((i, select) => {
                initialise_taxonomy_select(select);
            });
        }
    });

    $document.on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $ptsSelects = $repeatee.find('.jsAlchemyTaxonomySelect');

        if( $ptsSelects[0] ) {
            $ptsSelects.each((i, select) => {
                initialise_taxonomy_select(select);
            });
        }
    });

    function initialise_taxonomy_select(taxonomySelect) {
        const $select = $(taxonomySelect);
        const fieldData = $select.data('alchemy');
        const ptsData = AlchemyTaxonomySelectData['search'];
        const currentUrl = new URL(window.location.href);
        const pageID = currentUrl.searchParams.get('page') || currentUrl.searchParams.get('post');

        $select.select2({
            language: fieldData.locale,
            ajax: {
                url: ptsData.url,
                type: 'get',
                dataType: 'json',
                delay: 250,
                data: params => {
                    return {
                        'searchedFor': params.term,
                        '_wpnonce': ptsData.nonce,
                        'taxonomy': fieldData['taxonomy'],
                        'page-id': pageID
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
        }).on('select2:select', e => {
            const $target = $(e.target);
            const option = $target.children(`[value=${e.params.data.id}]`);

            option.detach();
            $target.append(option).change();
        });

        $select.siblings('.jsAlchemyTaxonomySelectClear').on('click', () => {
            $select.val("").change();
        });
    }

    AO.get_taxonomy_select_value = id => {
        return Promise.resolve( {
            'type': 'taxonomy_select',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);
