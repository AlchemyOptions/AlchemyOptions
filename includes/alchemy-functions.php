<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_options_id' ) ) {
    function alch_options_id() {
        return apply_filters( 'alch_options_id', 'alchemy_options' );
    }
}

if ( ! function_exists('alch_network_options_id') ) {
    function alch_network_options_id() {
        return apply_filters( 'alch_multisite_options_id', 'alchemy_multisite_options' );
    }
}

if ( ! function_exists( 'alch_max_repeater_nesting_level' ) ) {
    function alch_max_repeater_nesting_level() {
        return apply_filters( 'alch_max_repeater_nesting_level', 3 );
    }
}

if ( ! function_exists( 'alch_get_checkboxes_html' ) ) {
    function alch_get_checkboxes_html( $data ) {
        $checksHTML = "";

        if( is_array( $data[ 'choices' ] ) && count( $data[ 'choices' ] ) > 0 ) {
            foreach ( $data[ 'choices' ] as $choice ) {
                $id = esc_attr( $data[ 'id' ] . '_' . alch_make_label( $choice[ 'value' ] ) );

                $checksHTML .= sprintf (
                    '<label%1$s><input%2$s data-value=\'' . esc_attr( $choice[ 'value' ] ) . '\' ' . alch_disabled( $choice[ 'disabled' ] ) . ' ' . alch_checked( $choice[ 'checked' ] ) . '/> ' . $choice[ 'label' ] . '</label><br>',
                    alch_concat_attributes( array( 'for' => $id ) ),
                    alch_concat_attributes( array( 'id' => $id, 'name' => $id, 'type' => 'checkbox' ) )
                );
            }
        }

        return $checksHTML;
    }
}

if ( ! function_exists( 'alch_get_radios_html' ) ) {
    function alch_get_radios_html( $data ) {
        $checksHTML = "";

        if( is_array( $data[ 'choices' ] ) && count( $data[ 'choices' ] ) > 0 ) {
            foreach ( $data[ 'choices' ] as $choice ) {
                $id = esc_attr( $data[ 'id' ] . '_' . alch_make_label( $choice[ 'value' ] ) );

                $checksHTML .= sprintf (
                    '<label%1$s><input%2$s data-value=\'' . esc_attr( $choice[ 'value' ] ) . '\' ' . alch_disabled( $choice[ 'disabled' ] ) . ' ' . alch_checked( $choice[ 'checked' ] ) . '/> ' . $choice[ 'label' ] . '</label><br>',
                    alch_concat_attributes( array( 'for' => $id ) ),
                    alch_concat_attributes( array( 'id' => $id, 'name' => $data[ 'id' ], 'type' => 'radio' ) )
                );
            }
        }

        return $checksHTML;
    }
}

if ( ! function_exists( 'alch_concat_select_options' ) ) {
    function alch_concat_select_options( $options ) {
        //todo: refactor HTML formation

        $optionsHTML = "";

        if( isset( $options[ 'optgroups' ] ) && is_array( $options[ 'optgroups' ] ) && count( $options[ 'optgroups' ] ) > 0 ) {
            foreach ( $options[ 'optgroups' ] as $group ) {
                $optionsHTML .= '<optgroup label="' . esc_attr( $group[ 'label' ] ) . '" ' . ( isset( $group[ 'disabled' ] ) ? disabled( $group[ 'disabled' ], true, false ) : "" ) . '>';

                if( ! alch_array_has_string_keys( $group[ 'choices' ] ) && 'array' !== gettype( $group[ 'choices' ][0] ) ) {
                    foreach ( $group[ 'choices' ] as $choice ) {
                        $optionsHTML .= '<option value="' . esc_attr( $choice ) . '" ' . selected( $options[ 'selected' ], $choice, false ) . '>' . $choice . '</option>';
                    }
                } else {
                    foreach ( $group[ 'choices' ] as $choice ) {
                        $optionsHTML .= '<option value="' . esc_attr( $choice[ 'value' ] ) . '" ' . selected( $options[ 'selected' ], $choice[ 'value' ], false ) . ( isset( $choice[ 'disabled' ] ) ? disabled( $choice[ 'disabled' ], true, false ) : "" ) . '>' . $choice[ 'label' ] . '</option>';
                    }
                }

                $optionsHTML .= '</optgroup>';
            }
        } else if( is_array( $options[ 'choices' ] ) && count( $options[ 'choices' ] ) > 0 ) {
            if( ! alch_array_has_string_keys( $options[ 'choices' ] ) && 'array' !== gettype( $options[ 'choices' ][0] ) ) {
                foreach ( $options[ 'choices' ] as $choice ) {
                    $optionsHTML .= '<option value="' . esc_attr( $choice ) . '" ' . selected( $options[ 'selected' ], $choice, false ) . '>' . $choice . '</option>';
                }
            } else {
                foreach ( $options[ 'choices' ] as $choice ) {
                    $optionsHTML .= '<option value="' . esc_attr( $choice[ 'value' ] ) . '" ' . selected( $options[ 'selected' ], $choice[ 'value' ], false ) . ( isset( $choice[ 'disabled' ] ) ? disabled( $choice[ 'disabled' ], true, false ) : "" ) . '>' . $choice[ 'label' ] . '</option>';
                }
            }
        }

        return $optionsHTML;
    }
}

if ( ! function_exists( 'alch_concat_attributes' ) ) {
    function alch_concat_attributes( $attrs ) {
        $attrString = "";

        if( is_array( $attrs ) && count( $attrs ) > 0 ) {
            foreach ( $attrs as $attrName => $attrValue ) {
                $attrString .= sprintf( ' %1$s="%2$s"', $attrName, $attrValue );
            }
        }

        return $attrString;
    }
}

if ( ! function_exists( 'alch_populate_field_template' ) ) {
    function alch_populate_field_template( $fieldType, $options ) {
        $fileTemplate = file_get_contents( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/templates/' . $fieldType . '.php' );

        if( ! $fileTemplate ) {
            throw new Exception( sprintf( __( "Cannot parse the %s file type", "alchemy-options" ), $fieldType ) );
        }

        foreach ( $options as $key => $val ) {
            $fileTemplate = str_replace( "{{" . strtoupper( $key ) . "}}", $val, $fileTemplate );
        }

        return $fileTemplate;
    }
}

if ( ! function_exists( 'alch_array_has_string_keys' ) ) {
    function alch_array_has_string_keys( $array ) {
        return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
    }
}

if ( ! function_exists( 'alch_disabled' ) ) {
    function alch_disabled( $value ) {
        return disabled( $value, true, false );
    }
}

if ( ! function_exists( 'alch_selected' ) ) {
    function alch_selected( $value ) {
        return selected( $value, true, false );
    }
}

if ( ! function_exists( 'alch_checked' ) ) {
    function alch_checked( $value ) {
        return checked( $value, true, false );
    }
}

if ( ! function_exists( 'alch_make_label' ) ) {
    function alch_make_label( $text ) {
        return strtolower( str_replace( " ", "_", trim( $text ) ) );
    }
}

if ( ! function_exists( 'alch_get_option' ) ) {
    function alch_get_option( $optionID, $default = "" ) {
        //todo: use get_option() but filter hidden values

        /*
         * e.g. alch_get_option( 'second-checkbox-option' ) returns
         *
         * array(
         *      'one value', 'two value'
         * );
         * */
    }
}