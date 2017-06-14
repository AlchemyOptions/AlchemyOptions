export default function( scope = document ) {
    const $selectBoxes = $('.jsAlchemyTaxonomySelectBlock', scope);

    if( $selectBoxes[0] ) {
        $selectBoxes.each((i, el) => {
            const $el = $(el);
            const $select = $('.jsAlchemyTaxonomySelect', $el);

            $select.select2();

            $el.on('click', '.jsAlchemyTaxonomySelectClear', () => {
                $select.val("").change();
            });
        });
    }
}