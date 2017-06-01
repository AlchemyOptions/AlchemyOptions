import repeaterFunctions from './methods/repeaterFunctions';
import saveOptions from './methods/saveOptions';
import togglePassword from './methods/togglePasswordVisibility';
import datalistFunctions from './methods/datalistFunctions';
import colorpicker from './methods/colorpicker';

(function(document, window, $){
    $(() => {
        repeaterFunctions();
        saveOptions();
        togglePassword();
        datalistFunctions();
        colorpicker();
    });
})(document, window, jQuery);