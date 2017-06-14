export default function( scope = document ) {
    const $selectBoxes = $('.jsAlchemyDatalistBlock', scope);

    if( $selectBoxes[0] ) {
        $selectBoxes.each((i, el) => {
            const $el = $(el);
            const $select = $('.jsAlchemyDatalistSelect', $el);

            $select.select2();

            $el.on('click', '.jsAlchemyDatalistClear', () => {
                $select.val("").change();
            });
        });
    }
}