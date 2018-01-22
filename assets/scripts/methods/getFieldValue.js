function getFieldValue( alchemyField ) {
    const data = alchemyField.data('alchemy');

    if( ! data ) {
        return;
    }

    let value;

    switch (data.type) {
        case 'text' :
        case 'url' :
        case 'password' :
        case 'email' :
        case 'tel' :
        case 'select' :
        case 'textarea' :
        case 'colorpicker' :
        case 'datepicker' :
        case 'button-group' :
        case 'upload' :
        case 'slider' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    value.data[variationID] = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    }).find('input,select,textarea').val()
                });
            } else {
                value = alchemyField.find('input,select,textarea').val();
            }
        break;
        case 'checkbox':
        case 'radio':
        case 'image-radio':
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const checkedValues = [];

                    $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    }).find(':checked').each((i, el) => {
                        checkedValues.push($(el).data('value'));
                    });

                    value.data[variationID] = checkedValues;
                });
            } else {
                value = [];

                alchemyField.find(':checked').each((i, el) => {
                    value.push($(el).data('value'));
                });
            }
        break;
        case 'editor' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const $block = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    });

                    const $area = $('.jsAlchemyEditorTextarea', $block);

                    if( $area.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
                        value.data[variationID] = tinymce.get($area.attr('id')).getContent()
                    } else {
                        value.data[variationID] = $area.val();
                    }
                });
            } else {
                const $area = $('.jsAlchemyEditorTextarea', alchemyField);

                if( $area.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
                    value = tinymce.get($area.attr('id')).getContent()
                } else {
                    value = $area.val();
                }
            }
        break;
        case 'sections' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const sectionValues = [];
                    const $block = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    });

                    const $grandChildFields = $block.children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab').children('.alchemy__field');

                    if( $grandChildFields[0] ) {
                        $grandChildFields.each((i, el) => {
                            const $el = $(el);
                            const data = $el.data('alchemy');

                            if( data ) {
                                sectionValues.push({
                                    'type': data.type,
                                    'value': getFieldValue($el)
                                })
                            }
                        });
                    }

                    value.data[variationID] = sectionValues;
                });
            } else {
                value = [];

                const $childFields = alchemyField.children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab').children('.alchemy__field');

                if( $childFields[0] ) {
                    $childFields.each((i, el) => {
                        const $el = $(el);
                        const data = $el.data('alchemy');

                        if( data ) {
                            value.push({
                                'type': data.type,
                                'value': getFieldValue($el)
                            })
                        }
                    });
                }
            }
        break;
        case 'repeater' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const sectionValues = [];
                    const $block = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    });

                    const fields = $block.children('fieldset').children('.field__content').children('.jsAlchemyRepeaterSortable').children('.repeatee');

                    if( fields[0] ) {
                        fields.each((i, el) => {
                            const $repeatee = $(el);
                            const repeateeData = $repeatee.data('alchemy');
                            const $childFields = $repeatee.children('.repeatee__content').children('.alchemy__field');
                            const valueToStore = {
                                isVisible: $repeatee.children('.jsAlchemyRepeateeVisible').val(),
                                fields: {}
                            };

                            if(data.typed) {
                                valueToStore.typeID = repeateeData.repeateeTypeID
                            }

                            if( repeateeData.fieldIDs ) {
                                $.each(repeateeData.fieldIDs, (ind, field) => {
                                    valueToStore.fields[field.id] = {
                                        'type': field.type,
                                        'value': getFieldValue( $childFields.eq(ind) )
                                    };
                                });
                            }

                            sectionValues.push(valueToStore);
                        });
                    }

                    value.data[variationID] = sectionValues;
                });
            } else {
                value = [];

                const fields = alchemyField.children('fieldset').children('.field__content').children('.jsAlchemyRepeaterSortable').children('.repeatee');

                if( fields[0] ) {
                    fields.each((i, el) => {
                        const $repeatee = $(el);
                        const repeateeData = $repeatee.data('alchemy');
                        const $childFields = $repeatee.children('.repeatee__content').children('.alchemy__field');
                        const valueToStore = {
                            isVisible: $repeatee.children('.jsAlchemyRepeateeVisible').val(),
                            fields: {}
                        };

                        if(data.typed) {
                            valueToStore.typeID = repeateeData.repeateeTypeID
                        }

                        if( repeateeData.fieldIDs ) {
                            $.each(repeateeData.fieldIDs, (ind, field) => {
                                valueToStore.fields[field.id] = {
                                    'type': field.type,
                                    'value': getFieldValue( $childFields.eq(ind) )
                                };
                            });
                        }

                        value.push(valueToStore);
                    });
                }
            }
        break;
        case 'post-type-select' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const $block = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    });

                    const selectVal = $block.find('select').val();
                    const data = $block.data('alchemy');

                    value.data[variationID] = {
                        'type': data['post-type'],
                        'ids': typeof selectVal === 'string' ? [selectVal]: selectVal
                    }
                });
            } else {
                const selectVal = alchemyField.find('select').val();

                value = {
                    'type': data['post-type'],
                    'ids': typeof selectVal === 'string' ? [selectVal]: selectVal
                };
            }
        break;
        case 'taxonomy-select' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const $block = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    });

                    const taxSelectVal = $block.find('select').val();
                    const data = $block.data('alchemy');

                    value.data[variationID] = {
                        'taxonomy': data.taxonomy,
                        'ids': typeof taxSelectVal === 'string' ? [taxSelectVal]: taxSelectVal
                    }
                });
            } else {
                const taxSelectVal = alchemyField.find('select').val();

                value = {
                    'taxonomy': data.taxonomy,
                    'ids': typeof taxSelectVal === 'string' ? [taxSelectVal]: taxSelectVal
                };
            }
        break;
        case 'datalist' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const datalistSelectVal = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    }).find('select').val();

                    value.data[variationID] = typeof datalistSelectVal === 'string' ? [datalistSelectVal]: datalistSelectVal;
                });
            } else {
                const datalistSelectVal = alchemyField.find('select').val();

                value = typeof datalistSelectVal === 'string' ? [datalistSelectVal]: datalistSelectVal;
            }
        break;
        case 'field-group' :
            if( data.variations ) {
                value = {
                    'variations': true,
                    'data': {}
                };

                const $childFields = alchemyField.children('.field__content').children('.jsAlchemyVariationsContent').children('.jsAlchemyVariation');

                $.each(data.variations, (i, variationID) => {
                    const $block = $childFields.filter((i, el) => {
                        return $(el).data('variation-id') === variationID;
                    });
                    const $groupFields = $block.children('fieldset').children('.jsAlchemyFiledGroupWrapper');
                    const groupValue = {};

                    if( $groupFields[0] ) {
                        $groupFields.each((i, el) => {
                            const $group = $(el);
                            const groupData = $group.data('fields');
                            const $childFields = $group.children('.alchemy__field');

                            if( groupData ) {
                                $.each(groupData, (ind, field) => {
                                    groupValue[field.id] = {
                                        'type': field.type,
                                        'value': getFieldValue( $childFields.eq(ind) )
                                    };
                                });

                            }
                        });
                    }

                    value.data[variationID] = groupValue
                });
            } else {
                value = {};

                const $groupFields = alchemyField.children('fieldset').children('.jsAlchemyFiledGroupWrapper');

                if( $groupFields[0] ) {
                    $groupFields.each((i, el) => {
                        const $group = $(el);
                        const groupData = $group.data('fields');
                        const $childFields = $group.children('.alchemy__field');

                        if( groupData ) {
                            $.each(groupData, (ind, field) => {
                                value[field.id] = {
                                    'type': field.type,
                                    'value': getFieldValue( $childFields.eq(ind) )
                                };
                            });

                        }
                    });
                }
            }
        break;
        default : break;
    }

    return value;
}

export default getFieldValue;