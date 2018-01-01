export default function() {
    const $variationSelects = $('.jsAlchemyVariationsSelect');

    if( $variationSelects[0] ) {
        $variationSelects.each((i, el) => {
            const $select = $(el);
            const fieldID = $select.data('field-id');

            $select.on('change', () => {
                console.log(fieldID);
            });
        });
    }
}
