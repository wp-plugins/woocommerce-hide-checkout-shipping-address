wc_hcsa_settings = wc_hcsa_settings || {};

jQuery( document ).ready(function() {
	jQuery( '.shipping_address', jQuery( '.woocommerce-shipping-fields' ) ).prevAll().addBack().wrapAll( '<div class="woocommerce-shipping-fields-cnt"></div>' );
	jQuery( 'body' ).trigger( 'updated_checkout_hcsa', ['init'] );
} ).on( 'updated_checkout', 'body',function() {
	jQuery( 'body' ).trigger( 'updated_checkout_hcsa' );
} ).on( 'updated_checkout_hcsa', 'body', function( event, type ) {
	var e = wc_hcsa_settings.effect || 'slide', m = jQuery( 'select.shipping_method, input[name^=shipping_method][type=radio]:checked, input[name^=shipping_method][type=hidden]' ).val(), s, t = jQuery( '.woocommerce-shipping-fields-cnt' );
	wc_hcsa_settings.fields = wc_hcsa_settings.fields || {};
	wc_hcsa_settings.fields_num = wc_hcsa_settings.fields_num || 0;

	if ( type === 'init' ) {
		e = 'none';
	}

	if ( t.length && typeof wc_hcsa_settings.methods == 'object' ) {
		s = t.is( ':visible' );
		if ( typeof wc_hcsa_settings.methods[m] == 'undefined' ) {
			wc_hcsa_settings.methods[m] = 'no';
		}

		if ( wc_hcsa_settings.methods[m] == 'yes' && s ) {
			t.find( ':input' ).each( function() {
				var _t = jQuery( this );
				if ( typeof _t.data( 'hcsaid' ) == 'undefined' ) {
					_t.data( 'hcsaid', wc_hcsa_settings.fields_num );
					wc_hcsa_settings.fields_num ++;
				}
				if ( _t.is( ':checkbox,:radio' ) ) {
					wc_hcsa_settings.fields[_t.data( 'hcsaid' )] = _t.is(':checked');
					_t.prop('checked', false);
				} else {
					wc_hcsa_settings.fields[_t.data( 'hcsaid' )] = _t.val();
					_t.val('');
				}
			} );
			switch ( e ) {
				case 'fade':
					t.fadeOut( function() { t.addClass( 'shipping-fields-hidden' ); } );
					break;
				case 'none':
					t.hide( 0, function() { t.addClass( 'shipping-fields-hidden' ); } );
					break;
				default:
					t.slideUp( function() { t.addClass( 'shipping-fields-hidden' ); } );
			}
		} else if ( wc_hcsa_settings.methods[m] == 'no' && ! s ) {
			t.find( ':input' ).each( function() {
				var _t = jQuery( this );
				if ( typeof _t.data( 'hcsaid' ) != 'undefined' && typeof wc_hcsa_settings.fields[_t.data( 'hcsaid' )] != 'undefined' ) {
					if ( _t.is( ':checkbox,:radio' ) ) {
						_t.prop('checked', wc_hcsa_settings.fields[_t.data( 'hcsaid' )]);
					} else {
						_t.val(wc_hcsa_settings.fields[_t.data( 'hcsaid' )]);
					}
				}
			} );
			switch ( e ) {
				case 'fade':
					t.fadeIn( function() { t.removeClass( 'shipping-fields-hidden' ); } );
					break;
				case 'none':
					t.show( 0, function() { t.removeClass( 'shipping-fields-hidden' ); } );
					break;
				default:
					t.slideDown( function() { t.removeClass( 'shipping-fields-hidden' ); } );
			}
		}
	}
} );