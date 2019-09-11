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
                    const $sections = $metaBox.children('.jsAlchemySectionsFiled');

                    const $repeaters = $sections[0]
                        ? $sections.children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab').children('.jsAlchemyRepeaterField')
                        : $metaBox.children('.jsAlchemyRepeaterField');

                    if( $repeaters[0] ) {
                        $repeaters.each((i, repeater) => {
                            const $repeater = $(repeater);
                            let value = getFieldValue($repeater, true);

                            value = JSON.stringify(value);
                            value = value.replace(/\\\"/g, '&#34;'); // hack to replace escaped double quotes in JSON

                            $repeater.children('fieldset').children('.jsRepeaterHidden').val(value);
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
    } else if( wp.data && wp.data.subscribe ) {
        const $metaBoxes = $('.jsAlchemyMetaBox');

        if( $metaBoxes[0] ) {
            wp.data.subscribe(() => {
                const isSavingPost = wp.data.select('core/editor').isSavingPost();
                const isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();

                if ( isSavingPost && ! isAutosavingPost ) {
                    $metaBoxes.each((i, metabox) => {
                        const $metaBox = $(metabox);
                        const $sections = $metaBox.children('.jsAlchemySectionsFiled');

                        const $repeaters = $sections[0]
                            ? $sections.children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab').children('.jsAlchemyRepeaterField')
                            : $metaBox.children('.jsAlchemyRepeaterField');

                        if( $repeaters[0] ) {
                            $repeaters.each((i, repeater) => {
                                const $repeater = $(repeater);
                                let value = getFieldValue($repeater, true);

                                value = JSON.stringify(value);
                                value = value.replace(/\\\"/g, '&#34;'); // hack to replace escaped double quotes in JSON

                                $repeater.children('fieldset').children('.jsRepeaterHidden').val(value);
                            });
                        }
                    });
                }
            })
        }
    }
}