import repeaterFunctions from './methods/repeaterFunctions';
import saveOptions from './methods/saveOptions';
import togglePassword from './methods/togglePasswordVisibility';
import datalistFunctions from './methods/datalistFunctions';
import colorpicker from './methods/colorpicker';
import datepicker from './methods/datepicker';
import buttonGroup from './methods/buttonGroup';

(function(document, window, $){
    $(() => {
        repeaterFunctions();
        saveOptions();
        togglePassword();
        datalistFunctions();
        colorpicker();
        datepicker();
        buttonGroup();
    });
})(document, window, jQuery);