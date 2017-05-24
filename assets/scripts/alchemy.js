import repeaterFunctions from './methods/repeaterFunctions';
import saveOptions from './methods/saveOptions';
import togglePassword from './methods/togglePasswordVisibility';

(function(document, window, $){
    $(() => {
        repeaterFunctions();
        saveOptions();
        togglePassword();
    });
})(document, window, jQuery);