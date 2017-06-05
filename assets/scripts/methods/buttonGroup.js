export default function (scope = document) {
    const $btnGroups = $('.jsAlchemyButtonGroup', scope);

    if( $btnGroups[0] ) {
        $btnGroups.each((i, el) => {
            const $btnGroup = $(el);
            const $choices = $('.jsAlchemyButtonGroupChoice', $btnGroup);
            const $input = $('.jsAlchemyButtonGroupInput', $btnGroup);

            $btnGroup.on('click', '.jsAlchemyButtonGroupChoice', function() {
                const $btn = $(this);

                $input.val( $btn.data('value') );
                $choices.removeClass('button-primary');
                $btn.addClass('button-primary');
            });
        });
    }
}