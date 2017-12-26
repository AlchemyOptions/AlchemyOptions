import getFieldValue from './getFieldValue';

export default function() {
    const $postForm = $('#post');

    if( $postForm[0] ) {
        const $metaBoxes = $('.jsAlchemyMetaBox', $postForm);

        if( $metaBoxes[0] ) {
            $postForm.on('submit', function(e){
                let index = 0;

                $metaBoxes.each((i, metabox) => {
                    const $metaBox = $(metabox);
                    const $repeaters = $metaBox.children('.jsAlchemyRepeaterField');

                    if( $repeaters[0] ) {
                        $repeaters.each((i, repeater) => {
                            const $repeater = $(repeater);
                            const value = getFieldValue($repeater);

                            $repeater.children('fieldset').children('.jsRepeaterHidden').val(JSON.stringify(value));
                        });
                    }

                    index++;
                });

                if(index === $metaBoxes.length) {
                    return;
                }

                e.preventDefault();
            });
        }
    }
}