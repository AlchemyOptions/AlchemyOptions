export default function( scope = document ) {
    const $selectBoxes = $('.jsAlchemyDatalistBlock', scope);

    if( $selectBoxes[0] ) {
        $selectBoxes.each((i, el) => {
            const $el = $(el);
            const $select = $el.children('.field__content').children('.jsAlchemyDatalistSelect');

            $select.select2();

            $el.on('click', '.jsAlchemyDatalistClear', () => {
                $select.val("").change();
            });
        });
    }
}