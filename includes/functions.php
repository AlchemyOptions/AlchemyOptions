<?php

if( ! function_exists( 'alch_get_option' ) ) {
    function alch_get_option( $id, $default = '' ) {
        $id = alch_modify_alchemy_id( $id );

        return alch_get_prepared_value(
            get_option( $id ), $id, $default
        );
    }
}

if( ! function_exists( 'alch_get_i18n_option' ) ) {
    function alch_get_i18n_option( $id, $icl_language_code, $default = '' ) {
        $id = alch_get_db_prefix() . $id . "_{$icl_language_code}";

        return alch_get_prepared_value(
            get_option( $id ), $id, $default
        );
    }
}

if ( ! function_exists( 'alch_get_post_meta' ) ) {
    function alch_get_post_meta( $postID, $metaID, $default = '' ) {
        $metaID = alch_modify_alchemy_id( $metaID );

        return alch_get_prepared_value(
            get_post_meta( $postID, $metaID, true ), $metaID, $default
        );
    }
}

if ( ! function_exists( 'alch_get_i18n_post_meta' ) ) {
    function alch_get_i18n_post_meta( $postID, $metaID, $icl_language_code, $default = '' ) {
        $metaID = alch_get_db_prefix() . $metaID . "_{$icl_language_code}";

        return alch_get_prepared_value(
            get_post_meta( $postID, $metaID, true ), $metaID, $default
        );
    }
}

if ( ! function_exists( 'alch_get_user_meta' ) ) {
    function alch_get_user_meta( $metaID, $userID, $default = '' ) {
        $metaID = alch_modify_alchemy_id( $metaID );

        return alch_get_prepared_value(
            get_the_author_meta( $metaID, $userID ), $metaID, $default
        );
    }
}

if ( ! function_exists( 'alch_get_i18n_user_meta' ) ) {
    function alch_get_i18n_user_meta( $metaID, $userID, $icl_language_code, $default = '' ) {
        $metaID = alch_get_db_prefix() . $metaID . "_{$icl_language_code}";

        return alch_get_prepared_value(
            get_the_author_meta( $metaID, $userID ), $metaID, $default
        );
    }
}

if( ! function_exists( 'alch_get_prepared_value' ) ) {
    function alch_get_prepared_value( $saved, $id, $default ) {
        if( empty( $saved ) ) {
            return $default;
        }

        $repeater = Alchemy\Options::get_repeater_id_details( $saved['type'] );
        $valueType = $repeater ? 'repeater' : $saved['type'];

        $savedValue = apply_filters( "alch_prepare_{$valueType}_value", $saved['value'], $id );

        if( ! empty( $savedValue ) ) {
            return $savedValue;
        }

        return $default;
    }
}

if( ! function_exists( 'alch_admin_get_saved_option' ) ) {
    function alch_admin_get_saved_option( $id, $default = '' ) {
        $savedOption = get_option( $id );
        $value = $default;

        if( ! empty( $savedOption ) ) {
            $value = $savedOption['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_get_saved_network_option' ) ) {
    function alch_admin_get_saved_network_option( $id, $default = '' ) {
        $savedOption = get_site_option( $id );
        $value = $default;

        if( ! empty( $savedOption ) ) {
            $value = $savedOption['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_get_saved_meta' ) ) {
    function alch_admin_get_saved_meta( $postID, $id, $default = '' ) {
        $savedValue = get_post_meta( $postID, $id, true );
        $value = $default;

        if( ! empty( $savedValue ) ) {
            $value = $savedValue['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_get_saved_user_meta' ) ) {
    function alch_admin_get_saved_user_meta( $id, $userID, $default = '' ) {
        $savedValue = get_the_author_meta( $id, $userID );
        $value = $default;

        if( ! empty( $savedValue ) ) {
            $value = $savedValue['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_delete_option' ) ) {
    function alch_admin_delete_option( $id ) {
        return update_option( $id, '' );
    }
}

if( ! function_exists( 'alch_admin_get_field_label' ) ) {
    function alch_admin_get_field_label( $data, $useLabel ) {
        if( $useLabel ) {
            return sprintf( '<label class="field__label" for="%1$s">%2$s</label>', $data['id'], $data['title'] );
        }

        return sprintf( '<h3 class="field__label">%s</h3>', $data['title'] );
    }
}

if( ! function_exists( 'alch_admin_get_field_description' ) ) {
    function alch_admin_get_field_description( $text ) {
        return sprintf( '<div class="field__description">%s</div>', $text );
    }
}

if( ! function_exists( 'alch_admin_get_field_sidebar' ) ) {
    function alch_admin_get_field_sidebar( $data, $useLabel = true ) {
        $html = '';

        if( ! empty( $data['title'] ) || ! empty( $data['desc'] ) ) {
            $html .= '<div class="field__sidebar">';

            if( ! empty( $data['title'] ) ) {
                $html .= alch_admin_get_field_label( $data, $useLabel );
            }

            if( ! empty( $data['desc'] ) ) {
                $html .= alch_admin_get_field_description( $data['desc'] );
            }

            $html .= '</div>';
        }

        return $html;
    }
}

if( ! function_exists( 'alch_get_validation_tooltip' ) ) {
    function alch_get_validation_tooltip() {
        return '<span class="tooltip jsAlchemyValidationTooltip" role="tooltip"><span class="jsAlchemyTooltipText"></span><span class="tooltip__arrow" data-popper-arrow></span></span>';
    }
}

if( ! function_exists( 'alch_get_db_prefix' ) ) {
    function alch_get_db_prefix() {
        return apply_filters( 'alch_db_prefix', '_alchemy_options_' );
    }
}

if( ! function_exists( 'alch_get_db_suffix' ) ) {
    function alch_get_db_suffix() {
        $suffix = apply_filters( 'alch_db_default_suffix', '' );
        $languageCode = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';

        if ( ! empty( $languageCode ) && is_string( $languageCode ) ) {
            $languageCode = strtolower( $languageCode );

            $suffix = apply_filters( "alch_db_i18n_{$languageCode}_suffix",
                apply_filters( 'alch_db_i18n_suffix', "_{$languageCode}" )
            );
        }

        return apply_filters( 'alch_db_suffix', $suffix );
    }
}


if( ! function_exists( 'alch_modify_alchemy_id' ) ) {
    function alch_modify_alchemy_id( $id ) {
        return alch_get_db_prefix() . $id . alch_get_db_suffix();
    }
}