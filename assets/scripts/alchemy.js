import repeaterFunctions from './methods/repeaterFunctions';
import saveOptions from './methods/saveOptions';
import togglePassword from './methods/togglePasswordVisibility';
import datalistFunctions from './methods/datalistFunctions';
import colorpicker from './methods/colorpicker';
import datepicker from './methods/datepicker';
import buttonGroup from './methods/buttonGroup';
import mediaUploader from './methods/mediaUploader';
import editor from './methods/editor';
import imageRadios from './methods/imageRadios';
import slider from './methods/slider';
import postTypeSelect from './methods/postTypeSelect';
import taxonomySelect from './methods/taxonomySelect';
import conditions from './methods/conditions';
import sections from './methods/sections';

(function(document, window, $){
    $(() => {
        sections();
        repeaterFunctions();
        saveOptions();
        togglePassword();
        datalistFunctions();
        colorpicker();
        datepicker();
        buttonGroup();
        mediaUploader();
        editor();
        imageRadios();
        slider();
        postTypeSelect();
        taxonomySelect();
        conditions();
    });
})(document, window, jQuery);