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

if ( ! function_exists( 'alch_get_radios_html' ) ) {
    function alch_get_radios_html( $choices ) {
        $radiosHTML = "";

        if( is_array( $choices ) && count( $choices ) > 0 ) {
            foreach ( $choices[ 'radios' ] as $choice ) {
                $radiosHTML .= '<label for="' . esc_attr( $choice[ 'id' ] ) . '"><input type="radio" name="' . esc_attr( $choice[ 'name' ] ) . '" id="' . esc_attr( $choice[ 'id' ] ) . '" ' . checked( $choices[ 'checked' ], $choice[ 'value' ], false ) . '></label>';
            }
        }

        return $radiosHTML;
    }
}

if ( ! function_exists( 'alch_concat_select_options' ) ) {
    function alch_concat_select_options( $options ) {
        $optionsHTML = "";

        if( is_array( $options[ 'optgroups' ] ) && count( $options[ 'optgroups' ] ) > 0 ) {
            foreach ( $options[ 'optgroups' ] as $group ) {
                $optionsHTML .= '<optgroup label="' . $group[ 'label' ] . '" ' . ( isset( $group[ 'disabled' ] ) ? disabled( $group[ 'disabled' ], true, false ) : "" ) . '>';

                foreach ( $group[ 'options' ] as $choice ) {
                    $optionsHTML .= '<option value="' . $choice[ 'value' ] . '" ' . selected( $options[ 'selected' ], $choice[ 'value' ], false ) . ( isset( $choice[ 'disabled' ] ) ? disabled( $choice[ 'disabled' ], true, false ) : "" ) . '>' . $choice[ 'text' ] . '</option>';
                }

                $optionsHTML .= '</optgroup>';
            }
        } else if( is_array( $options[ 'options' ] ) && count( $options[ 'options' ] ) > 0 ) {
            if( ! alch_array_has_string_keys( $options[ 'options' ] ) && 'array' !== gettype( $options[ 'options' ][0] ) ) {
                foreach ( $options[ 'options' ] as $choice ) {
                    $optionsHTML .= '<option value="' . $choice . '" ' . selected( $options[ 'selected' ], $choice, false ) . '>' . $choice . '</option>';
                }
            } else {
                foreach ( $options[ 'options' ] as $choice ) {
                    $optionsHTML .= '<option value="' . $choice[ 'value' ] . '" ' . selected( $options[ 'selected' ], $choice[ 'value' ], false ) . ( isset( $choice[ 'disabled' ] ) ? disabled( $choice[ 'disabled' ], true, false ) : "" ) . '>' . $choice[ 'text' ] . '</option>';
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

if ( ! function_exists( 'alch_get_option' ) ) {
    function alch_get_option( $optionID, $default = "" ) {
        //todo: use get_option() but filter hidden values
    }
}