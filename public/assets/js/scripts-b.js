( function( w, j ) {
    const _di = {
        dependencies: {},
        set: function( key, value ) {
            this.dependencies[key] = value;
        },
        get: function( key ) {
            return this.dependencies[key];
        }
    };

    /* session */
    _di.set( 'session', {
        storage: w.sessionStorage,
        set: function( key, value ) {
            this.storage.setItem( key, JSON.stringify( value ) );
        },
        get: function( key ) {
            return JSON.parse( this.storage.getItem( key ) );
        },
        remove: function( key ) {
            return this.storage.removeItem( key );
        },
        clear: function() {
            return this.storage.clear();
        }
    } );
    /* session */

    const _cookie = ( function() {
        return {
            set: function( name, value, minutes ) {
                var expires = new Date();
                expires.setTime( expires.getTime() + ( minutes * 60 * 1000 ) );
                expires = expires.toUTCString();
                document.cookie = name + '=' + value + ';expires=' + expires + ';path=/';
            },
            get: function( name ) {
                name = name + '=';
                console.log( name );
                var cookies = document.cookie.split( ';' );
                console.log( cookies );
                for ( var i = 0; i < cookies.length; i++ ) {
                    var cookie = cookies[i];
                    console.log( cookie );
                    while ( cookie.charAt( 0 ) == ' ' ) {
                        cookie = cookie.substring( 1 );
                    }
                    console.log( cookie );
                    if ( cookie.indexOf( name ) == 0 ) {
                        return cookie.substring( name.length, cookie.length );
                    }
                }
                return '';
            }
        };
    } )();
    const _string = ( function() {
        return {
            encode: function( value ) {
                return encodeURIComponent( JSON.stringify( value ) );
            },
            decode: function( value ) {
                return JSON.parse( decodeURIComponent( value ) );
            }
        };
    } )();
    const _input = function( selector, options ) {
        if ( 'undefined' === typeof options ) {
            options = {};
        }
        const element = j( selector );
        const defaults = {
            initial: null,
            old: null,
            new: null,
            changed: false,
            type: null,
        };
        const settings = j.extend( {}, defaults, options );
        settings.type = element.attr( 'type' );
        if ( 'checkbox' === settings.type ) {
            settings.old = [];
            settings.new = [];
            settings.initial = [];
            element.each( function( i, e ) {
                e = j( e );
                if ( e.is( ':checked' ) ) {
                    settings.initial.push( e.val() );
                }
            } );
        } else {
            settings.old = null;
            settings.new = null;
            settings.initial = element.val();
        }
        element.focus( function( event ) {
            if ( 'checkbox' === settings.type ) {
                settings.old = [];
                element.each( function( i, e ) {
                    e = j( e );
                    if ( e.is( ':checked' ) ) {
                        settings.old.push( e.val() );
                    }
                } );
            } else {
                settings.old = element.val();
            }
        } ).change( function( event ) {
            if ( 'checkbox' === settings.type ) {
                settings.new = [];
                element.each( function( i, e ) {
                    e = j( e );
                    if ( e.is( ':checked' ) ) {
                        settings.new.push( e.val() );
                    }
                } );
            } else {
                settings.new = element.val();
            }
        } );
        return {
            element: element,
            setValue: function( value  ) {
                if ( 'checkbox' === settings.type ) {
                    settings.old = [];
                    element.each( function( i, e ) {
                        e = j( e );
                        if ( e.is( ':checked' ) ) {
                            settings.old.push( e.val() );
                        }
                        if ( value === e.val() ) {
                            e.prop( 'checked', true );
                            if ( !settings.new.includes( value ) ) {
                                settings.new.push( value );
                            }
                        }
                    } );
                } else {
                    settings.old = element.val();
                    settings.new = value;
                    element.val( value );
                }
            },
            getValue: function() {
                if ( 'checkbox' === settings.type ) {
                    let values = [];
                    element.each( function( i, e ) {
                        e = j( e );
                        if ( e.is( ':checked' ) ) {
                            values.push( e.val() );
                        }
                    } );
                    return values;
                } else {
                    let value = element.val();
                    return value;
                }
            },
            getInitialValue: function() {
                return settings.initial;
            },
            getOldValue: function() {
                return settings.old;
            },
            getNewValue: function() {
                return settings.new;
            },
            setChanged: function( value  ) {
                settings.changed = value;
            },
            getChanged: function() {
                return settings.changed;
            },
            onChange: function( value  ) {
                element.change( function( event ) {
                    if ( typeof value === 'function' ) {
                        value( event );
                    }
                } );
            },
            onClick: function( value  ) {
                element.click( function( event ) {
                    if ( typeof value === 'function' ) {
                        value( event );
                    }
                } );
            },
            onInput: function( value  ) {
                element.on( 'input', function( event ) {
                    if ( typeof value === 'function' ) {
                        value( event );
                    }
                } );
            },
        };
    };
    _di.set( 'input', _input );
    const _alert = function( options ) {
        const defaults = {
            message: null,
            type: null,
            close: false,
            closeClass: 'alert-dismissible fade show',
            closeButton: '\
                <button type="button" class="close" data-dismiss="alert">\
                    <span>&times;</span>\
                </button>\
            ',
        };
        const settings = j.extend( {}, defaults, options );
        return j( '\
            <div class="alert alert-' + settings.type + ' ' + ( settings.close ? settings.closeClass : '' ) + '" role="alert">\
                ' + settings.message + '\
                ' + ( settings.close ? settings.closeButton : '' ) + '\
            </div>\
        ' );
    };
    _di.set( 'alert', _alert );
    const _ajax = function( options ) {
        const defaults = {
            url: null,
            method: null,
            data: null,
            async: true,
            success: function( response ) {},
            error: function( xhr ) {},
        };
        const settings = j.extend( {}, defaults, options );
        j.ajax( {
            url: settings.url,
            method: settings.method,
            dataType: 'json',
            data: settings.data,
            async: settings.async,
            success: function( response ) {
                if ( typeof settings.success === 'function' ) {
                    settings.success( response );
                }
            },
            error: function( xhr ) {
                if ( typeof settings.error === 'function' ) {
                    settings.error( xhr );
                }
            }
        } );
    };
    _di.set( 'ajax', _ajax );
    const _form = function( options ) {
        const defaults = {
            form: null,
            reset: true,
            validationError: true,
            disableInputs: false,
            hideSubmit: false,
            success: function( response ) {},
            error: function( xhr ) {},
        };
        const settings = j.extend( {}, defaults, options );
        settings.form.submit( function( event ) {
            event.preventDefault();
            j( 'body' ).addClass( 'processing' );
            settings.form.find( '.invalid-feedback' ).remove();
            settings.form.find( '.is-invalid' ).removeClass( 'is-invalid' );
            settings.form.siblings( '.alert' ).remove();
            _ajax( {
                url: settings.form.attr( 'action' ),
                method: settings.form.attr( 'method' ),
                data: settings.form.serialize(),
                success: function( response ) {
                    if ( 0 === response.code ) {
                        if ( true === settings.reset ) {
                            settings.form.trigger( 'reset' );
                        }
                        if ( false !== settings.disableInputs ) {
                            if ( typeof settings.disableInputs === 'boolean' ) {
                                const inputs = settings.form.find( 'input:not([type=hidden]), select' );
                                inputs.prop( 'disabled', true );
                            }
                            else if ( typeof settings.disableInputs === 'object' ) {
                                j.each( settings.disableInputs, function( i, name ) {
                                    const input = settings.form.find( '[name=' + name + ']' );
                                    input.prop( 'disabled', true );
                                } );
                            }
                        }
                        if ( true === settings.hideSubmit ) {
                            settings.form.find( '[type=submit]' ).hide();
                        }
                        settings.form.before( _alert( {
                            message: response.message,
                            type: 'success',
                        } ) );
                    }
                    if ( typeof settings.success === 'function' ) {
                        settings.success( response );
                    }
                    j( 'body' ).removeClass( 'processing' );
                },
                error: function( xhr ) {
                    const response = xhr.responseJSON;
                    if ( 422 === xhr.status && true === settings.validationError ) {
                        j.each( response.errors, function( field, errors ) {
                            const input = settings.form.find( '[name=' + field + ']' );
                            const __alert = j( '<span class="invalid-feedback font-weight-bold"></span>' );
                            j.each( errors, function( index, error ) {
                                __alert.append( error );
                            } );
                            input.after( __alert );
                            input.addClass( 'is-invalid' );
                        } );
                    }
                    if ( typeof settings.error === 'function' ) {
                        settings.error( response );
                    }
                    j( 'body' ).removeClass( 'processing' );
                }
            } );
            return false;
        } );
        return {
            disableInput: function( selector ) {
                settings.form.find( selector ).prop( 'disabled', true );
            },
            emptyInputs: function() {
                const inputs = settings.form.find( 'input:not([type=hidden])' );
                j.each( inputs, function( i, input ) {
                    settings.form.find( input ).val( '' );
                } );
                const selects = settings.form.find( 'select' );
                j.each( selects, function( i, select ) {
                    settings.form.find( select ).find( 'option' ).prop( 'selected', false );
                } );
            },
            enableInputs: function( inputs ) {
                if ( 'undefined' === typeof inputs ) {
                    inputs = true;
                }
                if ( typeof inputs === 'boolean' ) {
                    const inputs = settings.form.find( 'input:not([type=hidden]), select' );
                    inputs.prop( 'disabled', false );
                }
                else if ( typeof inputs === 'object' ) {
                    j.each( inputs, function( i, name ) {
                        const input = settings.form.find( '[name=' + name + ']' );
                        input.prop( 'disabled', false );
                    } );
                }
            },
            hideSubmit: function() {
                settings.form.find( '[type=submit]' ).hide();
            },
            removeErrors: function() {
                settings.form.find( '.is-invalid' ).removeClass( 'is-invalid' );
                settings.form.find( '.invalid-feedback' ).remove();
            },
            reset: function() {
                settings.form.trigger( 'reset' );
            },
            setAction: function( action ) {
                settings.form.attr( 'action', action );
            },
            setInputValue: function( selector, value ) {
                settings.form.find( selector ).val( value );
            },
            setSelectValue: function( selector, value ) {
                settings.form.find( selector + ' option[value=' + value + ']' ).prop( 'selected', true );
            },
            showAlert: function( message, type ) {
                console.log( 'formShowAlert', type );
                if ( 'undefined' === typeof type ) {
                    type = 'success';
                }
                console.log( 'formShowAlert', type );
                settings.form.before( _alert( {
                    message: message,
                    type: type
                } ) );
            },
            showSubmit: function() {
                settings.form.find( '[type=submit]' ).show();
            }
        };
    };
    _di.set( 'form', _form );
    const _modal = function( options ) {
        const defaults = {
            modal: null,
            onShown: function( event ) {},
            onHidden: function( event ) {},
        };
        const settings = j.extend( {}, defaults, options );
        settings.modal.on( 'shown.bs.modal', function( event ) {
            if ( typeof settings.onShown === 'function' ) {
                settings.onShown( event );
            }
        } );
        settings.modal.on( 'hidden.bs.modal', function( event ) {
            if ( typeof settings.onHidden === 'function' ) {
                settings.onHidden( event );
            }
        } );
        return {
            hideQuestion: function() {
                settings.modal.find( '.modal-question' ).hide();
            },
            onHidden: function( callable ) {
                if ( typeof callable === 'function' ) {
                    settings.modal.on( 'hidden.bs.modal', function( event ) {
                        callable( event );
                    } );
                }
            },
            onShown: function( callable ) {
                if ( typeof callable === 'function' ) {
                    settings.modal.on( 'shown.bs.modal', function( event ) {
                        callable( event );
                    } );
                }
            },
            removeAlert: function() {
                settings.modal.find( '.alert' ).remove();
            },
            setBody: function( content ) {
                settings.modal.find( '.modal-body' ).html( content );
            },
            show: function() {
                settings.modal.modal( 'show' );
            },
            showAlert: function( message, type ) {
                if ( 'undefined' === typeof type ) {
                    type = 'success';
                }
                settings.modal.find( '.modal-body' ).prepend( _alert( {
                    message: message,
                    type: type
                } ) );
            },
            showQuestion: function() {
                settings.modal.find( '.modal-question' ).show();
            },
        };
    };
    _di.set( 'modal', _modal );

    /* filterTable */
    _di.set( 'filterTable', {
        prefix: 'filterTable',
        key: null,
        dataTable: null,
        columns: [
            'a', 'b', 'c', 'd', 'f', 'h',
            'j', 'l', 'm', 'nd', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y',
            'z', 'aa', 'ab', 'file_name',
        ],
        titles: {
            'a': '年度',
            'b': '請求月',
            'c': '題名',
            'd': '種類',
            'f': '売上名',
            'h': '現場',
            'j': '会社',
            'l': 'NO',
            'm': '工事名',
            'nd': '日付',
            'r': '見積名',
            's': '工事内訳',
            't': '工事内訳２',
            'u': '商品名',
            'v': '*',
            'w': '数量',
            'x': '総重量',
            'y': '単位',
            'z': '単価',
            'aa': '金額',
            'ab': '材積㎡単価',
            'file_name': 'ファイル名',
        },
        default: {
            filterByCondition: {
                column: '',
                condition: '',
                keyword: ''
            },
            filterByValue: {
                column: '',
                keyword: '',
                selectedValues: []
            },
            hide: {
                column: '',
                status: false
            },
            sort: {
                column: '',
                direction: ''
            }
        },
        data: {
            filterByCondition: {},
            filterByValue: {},
            hide: {},
            sort: {}
        },
        clone: function( obj ) {
            return JSON.parse( JSON.stringify( obj ) );
        },
        cloneDefaultIfUndefined: function( column, key ) {
            if ( 'undefined' === typeof this.data[key][column] ) {
                this.data[key][column] = this.clone( this.default[key] );
            }
        },
        init: function( key ) {
            this.setKey( key );
            this.generateHTML();
            this.saveData();
        },
        setKey: function( key ) {
            this.key = key;
        },
        saveData: function() {
            const session = _di.get( 'session' );
            session.set( this.key, this.data );
        },
        revertData: function() {
            const session = _di.get( 'session' );
            this.data = session.get( this.key );
        },
        clearData: function() {
            const session = _di.get( 'session' );
            session.remove( this.key );
        },
        setHidden: function( column, value ) {
            this.data[column]['hidden'] = value;
        },
        setSort: function( column, direction ) {
            this.data['sort'] = this.clone( this.default.sort );
            this.data['sort']['column'] = column;
            this.data['sort']['direction'] = direction;
        },
        getSortColumn: function() {
            return this.data.sort.column;
        },
        getSortDirection: function() {
            return this.data.sort.direction;
        },
        setDataTable: function( dataTable ) {
            this.dataTable = dataTable;
        },
        setConditionFilterByCondition: function( column, condition ) {
            const key = 'filterByCondition';
            this.cloneDefaultIfUndefined( column, key );
            this.data[key][column]['column'] = column;
            this.data[key][column]['condition'] = condition;
        },
        getConditionFilterByCondition: function( column ) {
            const key = 'filterByCondition';
            return 'undefined' !==  typeof this.data[key][column] ? this.data[key][column]['condition'] : '';
        },
        setKeywordFilterByCondition: function( column, keyword ) {
            const key = 'filterByCondition';
            this.cloneDefaultIfUndefined( column, key );
            this.data[key][column]['column'] = column;
            this.data[key][column]['keyword'] = keyword;
        },
        getKeywordFilterByCondition: function( column ) {
            const key = 'filterByCondition';
            return 'undefined' !==  typeof this.data[key][column] ? this.data[key][column]['keyword'] : '';
        },
        setKeywordFilterByValue: function( column, keyword ) {
            const key = 'filterByValue';
            this.cloneDefaultIfUndefined( column, key );
            this.data[key][column]['column'] = column;
            this.data[key][column]['keyword'] = keyword;
        },
        getKeywordFilterByValue: function( column ) {
            const key = 'filterByValue';
            return 'undefined' !==  typeof this.data[key][column] ? this.data[key][column]['keyword'] : '';
        },
        setSelectedValuesFilterByValue: function( column, selectedValues ) {
            const key = 'filterByValue';
            this.cloneDefaultIfUndefined( column, key );
            this.data[key][column]['column'] = column;
            this.data[key][column]['selectedValues'] = selectedValues;
        },
        getSelectedValuesFilterByValue: function( column ) {
            const key = 'filterByValue';
            return 'undefined' !==  typeof this.data[key][column] ? this.data[key][column]['selectedValues'] : '';
        },
        generateHtml: function() {
            const filterTable = this;
            filterTable.columns.forEach( function( column ) {
                let title = filterTable.titles[column];
                let icon = column === filterTable.getSortColumn()
                    ? ( 'asc' === filterTable.getSortDirection()
                        ? 'arrow_downward'
                        : ( 'desc' === filterTable.getSortDirection()
                            ? 'arrow_upward'
                            : ''
                        )
                    )
                    : '';
                let sortAscActive = column === filterTable.getSortColumn()
                    ? ( 'asc' === filterTable.getSortDirection()
                        ? 'active'
                        : ''
                    )
                    : '';
                let sortAscChecked = column === filterTable.getSortColumn()
                    ? ( 'asc' === filterTable.getSortDirection()
                        ? 'checked'
                        : ''
                    )
                    : '';
                let sortDescActive = column === filterTable.getSortColumn()
                    ? ( 'desc' === filterTable.getSortDirection()
                        ? 'active'
                        : ''
                    )
                    : '';
                let sortDescChecked = column === filterTable.getSortColumn()
                    ? ( 'desc' === filterTable.getSortDirection()
                        ? 'checked'
                        : ''
                    )
                    : '';
                let html = '\
                    <div class="d-flex flex-wrap align-items-end justify-content-between">\
                        <span class="mr-auto">\
                            ' + title + '\
                            <i class="material-icons ' + ( filterTable.getSortColumn() === column ? '' : 'd-none ' ) + filterTable.prefix + 'SortIcon ' + filterTable.prefix + filterTable.key + 'SortIcon" id="' + filterTable.prefix + filterTable.key + 'SortIcon' + column + '">\
                                ' + icon + '\
                            </i>\
                        </span>\
                        <div class="dropdown ml-auto ' + filterTable.prefix + 'Dropdown ' + filterTable.prefix + filterTable.key + 'Dropdown">\
                            <button class="btn btn-secondary btn-sm shadow-none rounded-0 dropdown-toggle ' + filterTable.prefix + 'DropdownToggle" type="button" id="' + filterTable.prefix + filterTable.key + 'DropdownToggle' + column + '"></button>\
                            <div class="dropdown-menu py-0 ' + filterTable.prefix + 'DropdownMenu" id="' + filterTable.prefix + filterTable.key + 'DropdownMenu' + column + '">\
                                <form id="' + filterTable.prefix + filterTable.key + 'Form' + column + '">\
                                    <ul class="list-group list-group-flush">\
                                        <li class="list-group-item py-1 ' + filterTable.prefix + 'Sort ' + filterTable.prefix + filterTable.key + 'Sort" id="' + filterTable.prefix + filterTable.key + 'Sort' + column + '">\
                                            <div class="btn-group btn-group-toggle">\
                                                <label class="btn btn-primary btn-sm ' + sortAscActive + '">\
                                                    <input type="radio" name="' + filterTable.prefix + filterTable.key + 'Sort' + column + '" value="asc" ' + sortAscChecked + '/>\
                                                    <div class="sort-asc"></div>\
                                                </label>\
                                                <label class="btn btn-primary btn-sm ' + sortDescActive + '">\
                                                    <input type="radio" name="' + filterTable.prefix + filterTable.key + 'Sort' + column + '" value="desc" ' + sortDescChecked + '/>\
                                                    <div class="sort-desc"></div>\
                                                </label>\
                                            </div>\
                                        </li>\
                                        <li class="list-group-item py-1">\
                                            <label for="' + filterTable.prefix + filterTable.key + 'ConditionFilterByCondition' + column + '" class="mb-0">\
                                                <small class="text-secondary">Filter by condition:</small>\
                                            </label>\
                                            <select\
                                                class="custom-select custom-select-sm shadow-none"\
                                                id="' + filterTable.prefix + filterTable.key + 'ConditionFilterByCondition' + column + '"\
                                                name="' + filterTable.prefix + filterTable.key + 'ConditionFilterByCondition' + column + '">\
                                                <option value="" selected>None</option>\
                                                <option disabled>----------</option>\
                                                <option value="isEmpty">Is empty</option>\
                                                <option value="isNotEmpty">Is not empty</option>\
                                                <option disabled>----------</option>\
                                                <option value="isEqualTo">Is equal to</option>\
                                                <option value="isNotEqualTo">Is not equal to</option>\
                                                <option disabled>----------</option>\
                                                <option value="beginsWith">Begins with</option>\
                                                <option value="endsWith">Ends with</option>\
                                                <option disabled>----------</option>\
                                                <option value="contains">Contains</option>\
                                                <option value="doesNotContain">Does not contain</option>\
                                            </select>\
                                            <input\
                                                type="text"\
                                                class="form-control form-control-sm shadow-none d-none mt-1"\
                                                id="' + filterTable.prefix + filterTable.key + 'KeywordFilterByCondition' + column + '"\
                                                placeholder="Value"\
                                                name="' + filterTable.prefix + filterTable.key + 'KeywordFilterByCondition' + column + '"\
                                                spellcheck="false"/>\
                                        </li>\
                                        <li class="list-group-item py-1">\
                                            <label for="' + filterTable.prefix + filterTable.key + 'KeywordFilterByValue' + column + '" class="mb-0">\
                                                <small class="text-secondary">Filter by value:</small>\
                                            </label>\
                                            <input\
                                                type="text"\
                                                class="form-control form-control-sm shadow-none"\
                                                id="' + filterTable.prefix + filterTable.key + 'KeywordFilterByValue' + column + '"\
                                                placeholder="Search"\
                                                name="' + filterTable.prefix + filterTable.key + 'KeywordFilterByValue' + column + '"\
                                                spellcheck="false"/>\
                                        </li>\
                                        <li class="list-group-item py-1 border-0">\
                                            <div class="text-right">\
                                                <button type="button" class="btn btn-sm shadow-none" id="' + filterTable.prefix + filterTable.key + 'SelectAllFilterByValue' + column + '">Select All</button>\
                                                <button type="button" class="btn btn-sm shadow-none ml-2" id="' + filterTable.prefix + filterTable.key + 'ClearFilterByValue' + column + '">Clear</button>\
                                            </div>\
                                            <div class="mt-2 px-1" style="min-height: 100px; max-height: 100px; overflow-y: auto;" id="' + filterTable.prefix + filterTable.key + 'SelectedValuesContainerFilterByValue' + column + '"></div>\
                                        </li>\
                                        <li class="list-group-item py-1 d-flex justify-content-between">\
                                            <button type="button" class="btn btn-sm btn-primary shadow-none rounded-0 px-4" id="' + filterTable.prefix + filterTable.key + 'Ok' + column + '">OK</button>\
                                            <button type="button" class="btn btn-sm btn-default shadow-none rounded-0 px-4 ml-2" id="' + filterTable.prefix + filterTable.key + 'Cancel' + column + '">Cancel</button>\
                                        </li>\
                                    </ul>\
                                </form>\
                            </div>\
                        </div>\
                    </div>\
                ';
                j( '#' + filterTable.prefix + filterTable.key + 'Th' + column ).append( html );
            } );
        },
        generateScript: function( route ) {
            const filterTable = this;
            const input = _di.get( 'input' );
            const ajax = _di.get( 'ajax' );
            filterTable.columns.forEach( function( column ) {
                const dropdownToggle = j( '#' + filterTable.prefix + filterTable.key + 'DropdownToggle' + column );
                const iconSort = j( '#' + filterTable.prefix + filterTable.key + 'SortIcon' + column );
                const containerSelectedValues = j( '#' + filterTable.prefix + filterTable.key + 'SelectedValuesContainerFilterByValue' + column );
                const inputSort = input( '[name="' + filterTable.prefix + filterTable.key + 'Sort' + column + '"]' );
                const inputCondition = input( '#' + filterTable.prefix + filterTable.key + 'ConditionFilterByCondition' + column );
                const inputKeyword = input( '#' + filterTable.prefix + filterTable.key + 'KeywordFilterByCondition' + column );
                const inputSearch = input( '#' + filterTable.prefix + filterTable.key + 'KeywordFilterByValue' + column );
                let inputSelectedValues, selectedValues = [];
                const buttonSelectAll = j( '#' + filterTable.prefix + filterTable.key + 'SelectAllFilterByValue' + column );
                const buttonClear = j( '#' + filterTable.prefix + filterTable.key + 'ClearFilterByValue' + column );
                const okButton = j( '#' + filterTable.prefix + filterTable.key + 'Ok' + column );
                const buttonCancel = j( '#' + filterTable.prefix + filterTable.key + 'Cancel' + column );
                dropdownToggle.click( function( event ) {
                    event.stopPropagation();
                    const dropdownToggle = j( event.target );
                    const dropdownMenu = j( '#' + filterTable.prefix + filterTable.key + 'DropdownMenu' + column );
                    const dropdownMenus = j( '.' + filterTable.prefix + 'Dropdown .' + filterTable.prefix + 'DropdownMenu' );
                    // dropdownMenus.not( dropdownMenu ).removeClass( 'show' );
                    j( okButton ).removeAttr( 'disabled' );
                    j.each( dropdownMenus.not( dropdownMenu ), function( idx, ddm ) {
                        var btn;
                        ddm = j( ddm );
                        if ( ddm.hasClass( 'show' ) ) {
                            btn = ddm.find( '[id^=filterTableMaterialCancel]' );
                            btn.click();
                        }
                    } );
                    if ( ( dropdownToggle.offset().left + dropdownMenu.width() ) < j( window ).width() ) {
                        dropdownMenu.css( 'left', dropdownToggle.offset().left );
                    } else {
                        dropdownMenu.css( 'right', 0 );
                    }
                    const checkHasShowClass = j( dropdownMenu ).hasClass( 'show' );
                    dropdownMenu.toggleClass( 'show' );
                    if( checkHasShowClass ) {
                        // dropdownMenu.removeClass( 'show' );
                        buttonCancel.click();
                    }
                    j( document ).one( 'click', function( event ) {
                        if ( dropdownMenu.hasClass( 'show' )
                            && !dropdownMenu.is( event.target )
                            && 0 === dropdownMenu.has( event.target ).length ) {
                            dropdownMenu.removeClass( 'show' );
                            j( filterTable.dataTable.table().body() ).off( 'scroll.' + filterTable.prefix + filterTable.key + 'Tbody' );
                            /* if ( ( 'undefined' !== typeof inputSelectedValues
                                && 0 === inputSelectedValues.getValue().length )
                                && '' === inputCondition.getValue()
                                && '' === inputKeyword.getValue() ) {
                                buttonCancel.click();
                                console.log( 'closed' );
                            } */
                            /* console.log(
                                true === inputSort.getChanged(),
                                true === inputCondition.getChanged(),
                                true === inputKeyword.getChanged(),
                                typeof inputSelectedValues,
                                ( 'undefined' !== typeof inputSelectedValues && true === inputSelectedValues.getChanged() )
                            ); */
                            /* if ( true === inputSort.getChanged()
                                || true === inputCondition.getChanged()
                                || true === inputKeyword.getChanged()
                                || ( 'undefined' !== typeof inputSelectedValues
                                && true === inputSelectedValues.getChanged() ) ) {
                                buttonCancel.click();
                            } */
                            // console.log( 'hello' );
                            // console.log( inputSelectedValues.getChanged() );
                            if (
                                // true === inputSort.getChanged() ||
                                true === inputCondition.getChanged()
                                || true === inputKeyword.getChanged()
                                || true === inputSearch.getChanged()
                                || (
                                    'undefined' !== typeof inputSelectedValues
                                    && true === inputSelectedValues.getChanged()
                                )
                            ) {
                                buttonCancel.click();
                            }
                        }
                    } );
                    j( filterTable.dataTable.table().body() ).one( 'scroll.' + filterTable.prefix + filterTable.key + 'Tbody', function( event ) {
                        console.log( 'scroll' );
                        dropdownMenu.removeClass( 'show' );
                        buttonCancel.click();
                    } );
                } );
                inputSort.element.parent( 'label' ).click( function( event ) {
                    event.preventDefault();
                    buttonCancel.click();
                    inputSort.setChanged( true );
                    const label = j( event.currentTarget );
                    const input = label.find( 'input:radio' );
                    const inputs = j( '.' + filterTable.prefix + filterTable.key + 'Sort' ).find( 'input:radio' ).not( input );
                    if( true === input.is( ':checked' ) ) {
                        label.removeClass( 'active' );
                        input.prop( 'checked', false );
                        filterTable.setSort( '', '' );
                        iconSort.text( '' );
                        iconSort.addClass( 'd-none' );
                        filterTable.dataTable.draw();
                        j( '#' + filterTable.prefix + filterTable.key + 'DropdownMenu' + column ).removeClass( 'show' );
                        return;
                    }
                    inputs.each( function( k, v ) {
                        const input = j( v );
                        const label = input.parent( 'label' );
                        input.prop( 'checked', false );
                        label.removeClass( 'active' );
                        label.closest( '.' + filterTable.prefix + filterTable.key + 'Dropdown' ).prev( 'span' )
                            .find( '.' + filterTable.prefix + filterTable.key + 'SortIcon' ).text( '' );
                    } );
                    if ( 'desc' === input.val() ) {
                        iconSort.text( 'arrow_upward' );
                        iconSort.removeClass( 'd-none' );
                    } else if ( 'asc' === input.val() ) {
                        iconSort.text( 'arrow_downward' );
                        iconSort.removeClass( 'd-none' );
                    }
                    label.addClass( 'active' );
                    input.prop( 'checked', true );
                    filterTable.setSort( '' +column, input.val() );
                    filterTable.saveData();
                    filterTable.dataTable.draw();
                    j( '#' + filterTable.prefix + filterTable.key + 'DropdownMenu' + column ).removeClass( 'show' );
                } );
                inputCondition.onChange( function( event ) {
                    const value = inputCondition.getValue();
                    /* if (
                        (
                            'undefined' === typeof inputSelectedValues
                            || 'undefined' === typeof inputSelectedValues.getValue()
                            || 0 === inputSelectedValues.getValue().length
                        )
                        && '' === value
                        && '' === inputKeyword.getValue()
                    ) {
                        j( okButton ).attr( 'disabled', true );
                    } else {
                        j( okButton ).removeAttr( 'disabled' );
                    } */
                    j( okButton ).removeAttr( 'disabled' );
                    filterTable.setConditionFilterByCondition( column, value );
                    if ( ['isEqualTo','isNotEqualTo','beginsWith','endsWith','contains','doesNotContain'].includes( value ) ) {
                        inputKeyword.element.removeClass( 'd-none' );
                    } else {
                        inputKeyword.element.addClass( 'd-none' );
                        inputKeyword.setValue( '' );
                    }
                    inputCondition.setChanged( true );
                } );
                inputKeyword.onChange( function( event ) {
                    filterTable.setKeywordFilterByCondition( column, inputKeyword.getValue() );
                    inputKeyword.setChanged( true );
                } );
                inputSearch.onInput( function( event ) {
                    filterTable.setKeywordFilterByValue( column, inputSearch.getValue() );
                    inputSearch.setChanged( true );
                    containerSelectedValues.empty();
                    filterTable.setSelectedValuesFilterByValue( column, '' ); // if cleared input search box, data was not cleared from request values
                    ajax( {
                        url: route.replace( /%column%/g, column ),
                        method: 'post',
                        data: { value: inputSearch.getValue() },
                        async: false,
                        success: function( response ) {
                            j.each( response.data, function( index, value ) {
                                value = value + "";
                                if ( -1 !== value.indexOf( '"' ) ) {
                                    value = value.replace( /"/g, '&quot;' );
                                }
                                containerSelectedValues.append( '\
                                    <div class="custom-control custom-checkbox">\
                                        <input\
                                            type="checkbox"\
                                            checked\
                                            class="custom-control-input"\
                                            id="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + index + '"\
                                            name="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + '"\
                                            value="' + ( isNaN( value ) ? value : value /* parseFloat( value ) */ ) + '"/>\
                                        <label\
                                            class="custom-control-label"\
                                            for="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + index + '">\
                                            ' + value + '\
                                        </label>\
                                    </div>\
                                ' );
                                inputSelectedValues = input( '[name="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + '"]' );
                                filterTable.setSelectedValuesFilterByValue( column, inputSelectedValues.getValue() );
                            } );
                            // console.log( inputSelectedValues );
                            inputSelectedValues = input( '[name="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + '"]' );
                            /* inputSelectedValues.onChange( function( event ) {
                                filterTable.setSelectedValuesFilterByValue( column, inputSelectedValues.getValue() );
                                inputSelectedValues.setChanged( true );
                            } ); */
                            inputSelectedValues.onChange( function( event ) {
                                filterTable.setSelectedValuesFilterByValue( column, inputSelectedValues.getValue() );
                                if (
                                    (
                                        'undefined' === typeof inputSelectedValues
                                        || 'undefined' === typeof inputSelectedValues.getValue()
                                        || 0 === inputSelectedValues.getValue().length
                                    )
                                    && '' === inputCondition.getValue()
                                    && '' === inputKeyword.getValue()
                                ) {
                                    j( okButton ).attr( 'disabled', true );
                                    // inputSelectedValues.setChanged( false );
                                } else {
                                    j( okButton ).removeAttr( 'disabled' );
                                    // inputSelectedValues.setChanged( true );
                                }
                            } );
                        }
                    } );
                    /* if (
                        (
                            'undefined' === typeof inputSelectedValues
                            || 'undefined' === typeof inputSelectedValues.getValue()
                            || 0 === inputSelectedValues.getValue().length
                        )
                        && '' === inputCondition.getValue()
                        && '' === inputKeyword.getValue()
                    ) {
                        j( okButton ).attr( 'disabled', true );
                    } else {
                        j( okButton ).removeAttr( 'disabled' );
                    } */
                    j( okButton ).removeAttr( 'disabled' );
                } );
                buttonSelectAll.click( function( event ) {
                    j( '[id^=' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + ']' ).prop( 'checked', true );
                    inputSelectedValues = input( '[name="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + '"]' );
                    filterTable.setSelectedValuesFilterByValue( column, inputSelectedValues.getValue() );
                    if (
                        (
                            'undefined' === typeof inputSelectedValues
                            || 'undefined' === typeof inputSelectedValues.getValue()
                            || 0 === inputSelectedValues.getValue().length
                        )
                        && '' === inputCondition.getValue()
                        && '' === inputKeyword.getValue()
                    ) {
                        j( okButton ).attr( 'disabled', true );
                        // inputSelectedValues.setChanged( false );
                    } else {
                        j( okButton ).removeAttr( 'disabled' );
                        // inputSelectedValues.setChanged( true );
                    }
                } );
                buttonClear.click( function( event ) {
                    j( '[id^=' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + ']' ).prop( 'checked', false );
                    inputSelectedValues = input( '[name="' + filterTable.prefix + filterTable.key + 'SelectedValuesFilterByValue' + column + '"]' );
                    filterTable.setSelectedValuesFilterByValue( column, '' );
                    if (
                        (
                            'undefined' === typeof inputSelectedValues
                            || 'undefined' === typeof inputSelectedValues.getValue()
                            || 0 === inputSelectedValues.getValue().length
                        )
                        && '' === inputCondition.getValue()
                        && '' === inputKeyword.getValue()
                    ) {
                        j( okButton ).attr( 'disabled', true );
                        // inputSelectedValues.setChanged( false );
                    } else {
                        j( okButton ).removeAttr( 'disabled' );
                        // inputSelectedValues.setChanged( true );
                    }
                } );
                okButton.click( function( event ) {
                    filterTable.saveData();
                    j( event.target ).closest( '#' + filterTable.prefix + filterTable.key + 'DropdownMenu' + column ).removeClass( 'show' );
                    dropdownToggle.addClass( 'active' );
                    /* console.log(
                        'undefined' !== typeof inputSelectedValues,
                        'undefined' !== typeof inputSelectedValues && 'undefined' !== typeof inputSelectedValues.getValue(),
                        'undefined' !== typeof inputSelectedValues && 'undefined' !== typeof inputSelectedValues.getValue() && 0 === inputSelectedValues.getValue().length,
                        (
                            'undefined' === typeof inputSelectedValues
                            || 'undefined' === typeof inputSelectedValues.getValue()
                            || 0 === inputSelectedValues.getValue().length
                        ),
                        '' === inputCondition.getValue(),
                        '' === inputKeyword.getValue()
                    ); */
                    if (
                        (
                            'undefined' === typeof inputSelectedValues
                            || 'undefined' === typeof inputSelectedValues.getValue()
                            || 0 === inputSelectedValues.getValue().length
                        )
                        && '' === inputCondition.getValue()
                        && '' === inputKeyword.getValue()
                    ) {
                        dropdownToggle.removeClass( 'active' );
                    }
                    // console.log( j( inputSort ).attr( 'checked' ) );
                    inputSort.setChanged( false );
                    inputCondition.setChanged( false );
                    inputKeyword.setChanged( false );
                    inputSearch.setChanged( false );
                    if ( 'undefined' !== typeof inputSelectedValues ) {
                        selectedValues = inputSelectedValues.getValue();
                        inputSelectedValues.setChanged( false );
                    }
                    j( filterTable.dataTable.table().body() ).off( 'scroll.' + filterTable.prefix + filterTable.key + 'Tbody' );
                    filterTable.dataTable.draw();
                } );
                buttonCancel.click( function( event ) {
                    
                    // j( '[name="' + filterTable.prefix + filterTable.key + 'Sort' + filterTable.getSortColumn() + '"][value="' + filterTable.getSortDirection() + '"]' ).parent( 'label' ).trigger( 'click' );
                    
                    // filterTable.revertData();
                    
                    j( event.target ).closest( '#' + filterTable.prefix + filterTable.key + 'DropdownMenu' + column ).removeClass( 'show' );
                    /* if ( true === inputSort.getChanged() ) {
                        j( '[name="' + filterTable.prefix + filterTable.key + 'Sort' + filterTable.getSortColumn() + '"][value="' + filterTable.getSortDirection() + '"]' ).parent( 'label' ).trigger( 'click' );
                        inputSort.setChanged( false );
                    } */
                    if ( true === inputCondition.getChanged() ) {
                        inputCondition.setValue( inputCondition.getOldValue() );
                        inputCondition.setChanged( false );
                    }
                    if ( true === inputKeyword.getChanged() ) {
                        inputKeyword.setValue( inputKeyword.getOldValue() );
                        inputKeyword.setChanged( false );
                    }
                    if ( ( '' === inputCondition.getValue()
                        || null === inputCondition.getValue() )
                        && ( '' === inputKeyword.getValue()
                        || null === inputKeyword.getValue() ) ) {
                        inputKeyword.element.addClass( 'd-none' );
                    }

                    // console.log( 'closed 3' );
                    if ( inputSelectedValues ) {
                        // console.log( 'closed 4' );
                        if ( true === inputSelectedValues.getChanged() ) {
                            // console.log( 'closed 5' );
                            inputSelectedValues.setChanged( false );
                            inputSelectedValues.element.prop( 'checked', false );
                            j.each( selectedValues, function( i, v ) {
                                // console.log( 'closed 7' );
                                inputSelectedValues.setValue( v );
                            } );
                            if ( true === inputSearch.getChanged() ) {
                                containerSelectedValues.empty();
                                inputSearch.setValue( inputSearch.getOldValue() );
                                inputSearch.setChanged( false );
                                console.log( 'closed 1' );
                            }
                        } else if ( false === inputSelectedValues.getChanged() ) {
                            j( okButton ).removeAttr( 'disabled' );
                            console.log( 'closed 6' );
                            if ( true === inputSearch.getChanged() ) {
                                // containerSelectedValues.empty();
                                // inputSearch.setValue( '' );
                                inputSearch.setValue( inputSearch.getOldValue() );
                                inputSearch.element.trigger( 'input' );
                                j.each( selectedValues, function( i, v ) {
                                    inputSelectedValues.setValue( v );
                                } );
                                inputSearch.setChanged( false );
                                console.log( 'closed 2' );
                            }
                        }
                    }
                    // if ( true === isChanged ) {
                    //     dropdownToggle.removeClass( 'active' );
                    // }
                    j( filterTable.dataTable.table().body() ).off( 'scroll.' + filterTable.prefix + filterTable.key + 'Tbody' );

                    filterTable.revertData();
                } );
            } );
        }
    } );
    /* filterTable */

    w['mms'] = {
        alert     : _alert,
        ajax      : _ajax,
        cookie    : _cookie,
        di        : _di,
        form      : _form,
        modal     : _modal,
        string    : _string,
    };
    j.ajaxSetup( { headers: { 'X-CSRF-TOKEN': j( 'meta[name="csrf-token"]' ).attr( 'content' ) } } );
    j( document ).ajaxError( function( event, xhr ) {
        const response = xhr.responseJSON;
        if ( 419 === xhr.status ) {
            window.location = '/login';
            return false;
        }
    } );
    j( '[data-toggle="tooltip"]' ).tooltip();
} )( window, jQuery );
