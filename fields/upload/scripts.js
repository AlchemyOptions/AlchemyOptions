"use strict";

(function(window, document, $) {
    window.AO = window.AO || {};

    const $uploaders = $('.jsAlchemyUploader');

    if( $uploaders[0] ) {
        $uploaders.each((i, el) => {
            initialise_uploader(el);
        });
    }

    $(document).on('alch_repeatee_added', function(e, data) {
        const $repeatee = data.repeatee;
        const $uploaders = $repeatee.find('.jsAlchemyUploader');

        if( $uploaders[0] ) {
            $uploaders.each((i, el) => {
                initialise_uploader(el);
            });
        }
    });

    function initialise_uploader( uploader ) {
        const $uploader = $(uploader);
        const $results = $('.jsAlchemyUploaderResults', $uploader);
        const $input = $('.jsAlchemyUploaderInput', $uploader);
        const $uploadButton = $('.jsAlchemyUploadTrigger', $uploader);
        const uploadStrings = $uploadButton.data('strings');

        let file_frame;
        let json;

        $uploader.on('click', '.jsAlchemyUploadTrigger', () => {
            if ( file_frame ) {
                file_frame.open();

                return;
            }

            file_frame = wp.media.frames.file_frame = wp.media({
                title: uploadStrings.title,
                button: {
                    text: uploadStrings.text
                },
                frame: 'select',
                multiple: true
            });

            file_frame.on( 'select', () => {
                json = file_frame.state().get('selection').first().toJSON();

                if ( 0 > $.trim( json.url.length ) ) {
                    return;
                }

                if( 'image' === json.type ) {
                    const source = json.sizes.thumbnail ? json.sizes.thumbnail.url : json.sizes.full.url;

                    $results.html(`<div class="field__image"><img src="${source}" alt="" /></div>`);

                    $input.val(json.id);
                } else if ( 'video' === json.type || 'audio' === json.type ) {
                    const results = [
                        $('<img />', {
                            src: json.icon,
                            title: json.filename
                        }),
                        $('<div />', {
                            html: `${json.filename} <span class="alchemy__filesize">(${json.filesizeHumanReadable})</span>`
                        })
                    ];

                    $results.addClass('field__results--visible').html(results);
                    $input.val(json.id);
                }
            });

            file_frame.open();
        });

        $uploader.on('click', '.jsAlchemyUploadRemove', () => {
            $input.val('');
            $results.removeClass('field__results--visible').html('');
        });
    }

    AO.get_upload_value = id => {
        return Promise.resolve( {
            'type': 'upload',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, document, jQuery);