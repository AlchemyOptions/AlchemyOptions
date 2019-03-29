import mediaUploader from './mediaUploader';
import togglePassword from './togglePasswordVisibility';
import colorpicker from './colorpicker';
import datepicker from './datepicker';
import editor from './editor';
import buttonGroup from './buttonGroup';
import imageRadios from './imageRadios';
import slider from './slider';
import postTypeSelect from './postTypeSelect';
import taxonomySelect from './taxonomySelect';
import datalistFunctions from './datalistFunctions';
import conditions from './conditions';
import sections from './sections';
import getFieldValue from './getFieldValue';

function getThingsGoing(scope = document) {
    const $repeaterFields = $('.jsAlchemyRepeaterField', scope);

    if( $repeaterFields[0] ) {
        const isNetworkForm = $("#jsAlchemyForm").data('is-network');
        const $document = $(document);

        $repeaterFields.each((i, el) => {
            const $repeater = $(el);
            const $dropIn = $repeater.children('fieldset').children('.field__content').children('.jsAlchemyRepeaterSortable');
            const $addNew = $repeater.children('fieldset').children('.field__content').children('.alchemy__add-new').children('.button-primary');

            let clickIndex = $dropIn.children().length;

            $repeater.on('click', '.jsAlchemyRepeaterAdd', function(e) {
                e.stopPropagation();

                const $btn = $(this);
                const $loader = $btn.closest('.alchemy__add-new').children('.jsAlchemyRepeaterLoader');
                const nonce = $btn.data('nonce');

                $btn.attr('disabled', true);
                $loader.removeClass('alchemy__repeater-add-spinner--hidden');

                $.ajax({
                    'type': 'get',
                    'url': alchemyData.adminURL,
                    'data': {
                        'action': 'alchemy_options_repeater_item_add',
                        'nonce': [nonce.id, nonce.value],
                        'repeater': $btn.data('repeater-data'),
                        'index': clickIndex,
                        'network': isNetworkForm
                    },
                    'success': data => {
                        console.log('success');

                        const $data = $(data);

                        $dropIn.append($data);
                        $dropIn.sortable( "refresh" );

                        sections($data);
                        mediaUploader($data);
                        togglePassword($data);
                        colorpicker($data);
                        datepicker($data);
                        editor($data);
                        buttonGroup($data);
                        imageRadios($data);
                        slider($data);
                        postTypeSelect($data);
                        taxonomySelect($data);
                        datalistFunctions($data);
                        conditions($data);
                        getThingsGoing($data);
                    },
                    'error': err => {
                        console.error('error', err);
                    },
                    'complete': () => {
                        $btn.closest('.jsTypeList').removeClass('type-list--visible');
                        $btn.closest('.alchemy__add-new').removeClass('alchemy__add-new--active');
                        $btn.removeAttr('disabled');
                        $loader.addClass('alchemy__repeater-add-spinner--hidden');
                    }
                });

                clickIndex++;
            });

            $repeater.on('input', '.jsAlchemyRepeateeTitle', function() {
                const $input = $(this);

                $input.closest('.repeatee').children('.jsAlchemyRepeateeToolbar').find('.jsAlchemyRepeateeTitle').text($input.val());
            });

            $repeater.on('click', '.jsAlchemyRepeaterAddType', function(e){
                const $btn = $(this);
                const $parent = $btn.parent('.alchemy__add-new');
                const $list = $btn.siblings('.jsTypeList');

                e.stopPropagation();

                $parent.addClass('alchemy__add-new--active');
                $list.addClass('type-list--visible');

                $document.one('click', function() {
                    $parent.removeClass('alchemy__add-new--active');
                    $list.removeClass('type-list--visible');
                });
            });

            $repeater.on('click', '.jsTypeList', function(e){
                e.stopPropagation();
            });

            $repeater.on('click', '.jsAlchemyRepeateeToolbar', function(e) {
                e.stopPropagation();
                $document.trigger('click');

                const $toolbar = $(this);
                const $repeatee = $toolbar.closest( '.repeatee' );

                if( $repeatee.hasClass('repeatee--expanded') ) {
                    const $editor = $repeatee.children('.repeatee__content').children('.field--editor').find('.jsAlchemyEditorTextarea');
                    
                    $editor.each((i, el) => {
                        let tmcEditor = tinymce.get($(el).attr('id'));

                        $(el).removeClass('tinymce--init');
                        $repeatee.removeClass('repeatee--expanded');

                        $repeatee.children('.repeatee__content').children('.field--editor').find('.wp-editor-tools').remove();

                        if( tmcEditor ) {
                            $(el).html(tmcEditor.getContent());

                            tmcEditor.destroy();
                        }
                    });
                } else {
                    editor($repeatee.children('.repeatee__content').children('.field--editor'));

                    $repeatee.addClass('repeatee--expanded');
                }
            });

            $repeater.on('click', '.jsAlchemyRepeateeHide', function() {
                const $toolbar = $(this);
                const $parent = $toolbar.closest( '.repeatee' );
                const $visibilityInput = $parent.children('.jsAlchemyRepeateeVisible');

                $toolbar.find('span').toggleClass('dashicons-hidden');
                $parent.removeAttr('style').toggleClass('repeatee--hidden');

                $visibilityInput.val( $visibilityInput.val() === 'true' ? 'false' : 'true' );
            });

            $repeater.on('click', '.jsAlchemyRepeateeRemove', function() {
                const $toolbar = $(this);
                const $parent = $toolbar.closest( '.repeatee' );

                $parent.fadeOut(() => {
                    $parent.remove();

                    $dropIn.sortable( "refresh" );
                });
            });

            $repeater.on('click', '.jsAlchemyRepeateeCopy', function() {
                const $btn = $(this);
                const nonce = $btn.data('nonce');
                const $parent = $btn.closest('.repeatee');
                const $loader = $addNew.closest('.alchemy__add-new').children('.jsAlchemyRepeaterLoader');
                const repeaterValues = getFieldValue($repeater);

                $addNew.attr('disabled', true);
                $loader.removeClass('alchemy__repeater-add-spinner--hidden');

                saveEditors($repeater);

                $.ajax({
                    'type': 'get',
                    'url': alchemyData.adminURL,
                    'data': {
                        'action': 'alchemy_options_repeater_item_add',
                        'nonce': [nonce.id, nonce.value],
                        'repeater': $addNew.data('repeater-data'),
                        'index': clickIndex,
                        'network': isNetworkForm,
                        'value': repeaterValues[$parent.index()]
                    },
                    'success': data => {
                        console.log('success');

                        const $data = $(data);

                        $parent.after($data);
                        $dropIn.sortable( "refresh" );

                        sections($data);
                        mediaUploader($data);
                        togglePassword($data);
                        colorpicker($data);
                        datepicker($data);
                        editor($data);
                        buttonGroup($data);
                        imageRadios($data);
                        slider($data);
                        postTypeSelect($data);
                        taxonomySelect($data);
                        datalistFunctions($data);
                        conditions($data);
                        getThingsGoing($data);
                    },
                    'error': err => {
                        console.error('error', err);
                    },
                    'complete': () => {
                        $addNew.closest('.jsTypeList').removeClass('type-list--visible');
                        $addNew.closest('.alchemy__add-new').removeClass('alchemy__add-new--active');
                        $addNew.removeAttr('disabled');
                        $loader.addClass('alchemy__repeater-add-spinner--hidden');
                    }
                });

                restoreEditors($repeater);

                clickIndex++;
            });

            $repeater.on('click', '.repeatee__actions', e => {
                e.stopPropagation();
            });

            $dropIn.sortable({
                placeholder: "repeatee--placeholder",
                opacity: 0.8,
                handle: ".jsAlchemyRepeateeToolbar",
                start: (event, ui) => {
                    $dropIn.find('.repeatee--placeholder').height(ui.helper.height() - 2);

                    saveEditors($repeater);
                },
                stop: (event, ui) => {
                    restoreEditors($repeater);
                }
            });

            $dropIn.disableSelection();
        });

        function saveEditors(repeater) {
            let $editors = $('.jsAlchemyEditorTextarea', repeater);

            $editors.each((i, el) => {
                const $editor = $(el);

                if( $editor.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
                    const $field = $editor.closest('.field--editor');
                    const selfHeight = $editor.closest('.field--editor').outerHeight();
                    const editorHeight = $editor.prev('.mce-tinymce').outerHeight();
                    const editorWidth = $editor.prev('.mce-tinymce').width();

                    $editor.next('.field__cover').height(editorHeight).width(editorWidth);
                    $field.height(selfHeight).addClass('tinymce--destroyed');

                    tinymce.get($editor.attr('id')).destroy();
                }
            });
        }

        function restoreEditors(repeater) {
            let $editors = $('.jsAlchemyEditorTextarea', repeater);

            $editors.each((i, el) => {
                const $editor = $(el);
                const $field = $editor.closest('.field--editor');

                if( $field.hasClass('tinymce--destroyed') && $editor.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {

                    const settings = window.tweakSettings($editor.attr('id'));

                    settings.min_height = 250;
                    tinymce.init(settings);

                    $editor.next('.field__cover').removeAttr('style');
                    $field.removeAttr('style').removeClass('tinymce--destroyed');
                }
            });
        }
    }
}

export default getThingsGoing;
