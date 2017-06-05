export default function (scope = document) {
    const $uploaders = $('.jsAlchemyUploader', scope);

    if( $uploaders[0] ) {
        $uploaders.each((i, el) => {
            const $uploader = $(el);
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
                    multiple: false
                });

                file_frame.on( 'select', () => {
                    json = file_frame.state().get('selection').first().toJSON();

                    if ( 0 > $.trim( json.url.length ) ) {
                        return;
                    }

                    if( 'image' === json.type ) {
                        $results.html($('<img />', {
                            src: json.url,
                            alt: json.caption,
                            title: json.title
                        }));

                        $input.val(json.id);
                    }
                });

                file_frame.open();
            });

            $uploader.on('click', '.jsAlchemyUploadRemove', () => {
                $input.val('');
                $results.html('');
            });
        });
    }
}