export default function (scope = document) {
    const $hiddenFields = $('.jsAlchemyConditionallyHidden', scope);

    if( $hiddenFields[0] ) {
        $hiddenFields.each((i, el) => {
            const $hiddenField = $(el).hide();
            const condition = $hiddenField.data('condition');

            if( condition ) {
                const condArr = condition.split('=');
                const $btnGroup = $(`#field--${condArr[0]}`);

                if( $btnGroup[0] ) {
                    const $input = $btnGroup.find('.jsAlchemyButtonGroupInput');
                    const value = $input.val();

                    shouldShow(value, condArr[1], $hiddenField);

                    $btnGroup.on('click', '.jsAlchemyButtonGroupChoice', function(){
                        shouldShow($input.val(), condArr[1], $hiddenField);
                    });
                }
            }
        });

        function shouldShow( inputVal, realVal, element ) {
            if( inputVal === realVal ) {
                element.show();
            } else {
                element.hide();
            }
        }
    }
}