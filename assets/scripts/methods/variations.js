export default function() {
    const $variationSelects = $('.jsAlchemyVariationsSelect');

    if( $variationSelects[0] ) {

        $variationSelects.each((i, el) => {
            const $select = $(el);
            const fieldID = $select.data('field-id');
            const $contentVariations = $(`#field--${fieldID}`).children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation ');

            $select.on('change', () => {
                $contentVariations.hide().filter((i, el) => {
                    return $select.val() === $(el).data('variation-id');
                }).show();
            });
        });
    }
}
