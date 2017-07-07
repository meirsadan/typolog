
// Typolog
// by Meir Sadan <meir@sadan.com>

var rePattern = /([A-Za-z0-9\s_-]*)(\.([^.]+))?$/;

var isFileSupported = function( file ) {
	return ( _.isArray( acceptedFileTypes ) ) ? _.indexOf( acceptedFileTypes, rePattern.exec( file.name )[3].toLowerCase() ) > -1 : true; // if no array of file types, accept all
};
 

( function( $ ) {
	
'use strict';


var deleteFontFileHandler = function(e) {
	e.preventDefault();
	var $this = $(this);
	$.post(ajaxurl, {
		action: 'delete_font_file',
		font_id: $(this).attr('data-font-id'),
		file_id: $(this).attr('data-file-id')
	}, function(data) {
		if (_.has(data, 'success'))
			$this.parents('li.font-file-line').remove();
		else if (_.has(data, 'error'))
			console.log('Error', data.error);
		$this.parents('.actions').find('.spinner').removeClass('is-active');
	}, 'json');
	$(this).parents('.actions').find('.spinner').addClass('is-active');
}

var bindFontFileTable = function($el) {
	$el.find('button.delete-font-file').click(deleteFontFileHandler);
	$el.find('input.checkbox').on('change', function() {
		$.post(ajaxurl, {
				action: 'update_font_packages',
				font_id: $("form#post input#post_ID").val(),
				files: parsePackagesList($el)
			}, function(data) {
				if (_.has(data, 'success')) {
					$el.find('.spinner').removeClass('is-active');
				}
			}, 'json');
		$el.find('.spinner').addClass('is-active');
	});
}

var parsePackagesList = function( $list ) {
	var files = {};
	$list.find( '.font-file-line' ).each( function() {
		files[ $( this ).attr( 'data-id' ) ] = $( this ).find( 'input:checked' ).map( function() {
			return $( this ).attr( 'data-license' );
		} ).get();
	});
	console.log( files );
	return files;
}

var regenerateFamilyFonts = function( e ) {
	e.preventDefault();
	
	var $report = $( '.regenerate-fonts-report' );
	var $spinner = $( e.currentTarget ).parents( '.family-row' ).find( '.spinner' );
	
	var familyName = $( e.currentTarget ).parents( '.family-row' ).find( '.family-item' ).text();
	var familyId = $( e.currentTarget ).data( 'id' );

	$report.html( '' );	
	$spinner.addClass( 'is-active' );
	
	if ( familyId ) {
		$report.append( '<p>Regenerating ' + familyName + '...</p>' );
		$.post( {
			url: ajaxurl,
			data: {
				action: 'regenerate_family',
				family_id: familyId
			},
			success: function( data ) {
				if ( data.success ) {
					$report.append( '<p>Regenerated products for ' + familyName + '.</p>' );
				} else if ( data.error ) {
					$report.append( '<p>Error regenerating products for ' + familyName + '...</p>' );
				}
			},
			complete: function( data ) {
				$report.append( '<p>Done!</p>' );
				$spinner.removeClass( 'is-active' );
				location.reload();
			},
			dataType: 'json'
		} );
	}
}

var regenerateAllFonts = function() {
	$( '.regenerate-all-fonts-container .spinner' ).addClass( 'is-active' );
	
	if ( FontefFamilies ) {
		$( '.regenerate-fonts-report' ).html( '' );
		var families = JSON.parse( JSON.stringify ( FontefFamilies ) );
		var _process = function() {
			var family = families.pop();
			$( '.regenerate-fonts-report' ).append( '<p>Regenerating ' + family.name + '...</p>' );
			$.post( {
				url: ajaxurl,
				data: {
					action: 'regenerate_family',
					family_id: family.id
				},
				success: function( data ) {
					if ( data.success ) {
						$( '.regenerate-fonts-report' ).append( '<p>Regenerated products for ' + family.name + '.</p>' );
					} else if ( data.error ) {
						$( '.regenerate-fonts-report' ).append( '<p>Error regenerating products for ' + family.name + '...</p>' );
					}
				},
				complete: function( data ) {
					if ( families.length ) {
						_process();
					} else {
						$( '.regenerate-fonts-report' ).append( '<p>Done!</p>' );
						$( '.regenerate-all-fonts-container .spinner' ).removeClass( 'is-active' );
						$( '.regenerate-all-fonts' ).prop( 'disabled', false );
					}
				},
				dataType: 'json'
			} );
		}
		_process();
		return;
	}
	
	$.post(ajaxurl, {
		action: 'regenerate_fonts'
	}, function(data) {
		if (data.success) {
			alert(data.success);
		} else if (data.error) {
			alert(data.error);
		}
		$('.regenerate-all-fonts-container .spinner').removeClass('is-active');
		$('.regenerate-all-fonts').prop('disabled', false);
	}, 'json');
	
}

var resetProducts = function() {
	$('.reset-products-container .spinner').addClass('is-active');
	$.post(ajaxurl, {
		action: 'reset_products'
	}, function(data) {
		if (data.success) {
			alert(data.success);
		} else if (data.error) {
			alert(data.error);
		}
		$('.reset-products-container .spinner').removeClass('is-active');
		$('.reset-products').prop('disabled', false);
	}, 'json')
}

var deleteAllFonts = function() {
	$('.delete-all-fonts-container .spinner').addClass('is-active');
	$.post(ajaxurl, {
		action: 'delete_all_fonts'
	}, function(data) {
		if (data.success) {
			alert(data.success);
		} else if (data.error) {
			alert(data.error);
		}
		$('.delete-all-fonts-container .spinner').removeClass('is-active');
		$('.delete-all-fonts').prop('disabled', false);
	}, 'json')
}

	 $(function() {
		 
		 Dropzone.autoDiscover = false;
		 
/*
		 if ($('.generator').length) {
			 
			 var Generator = new GeneratorView;
		 
		 }
*/
		 
		 if ( $('#font_file_dropzone').length ) {
		 	var dz = new Dropzone( '#font_file_dropzone', {
				url: ajaxurl,
				clickable: true,
				params: {
					action: 'typolog_upload_font',
					font_id: $( "form#post input#post_ID" ).val()
				},
				init: function() {
					this.on( "addedfile", function( file ) {
						if ( !isFileSupported( file ) ) {
							this.removeFile( file );
						}
					});
				},
				success: function( file, data ) {
					file.previewElement.classList.add( "dz-success" );
					var jsonData = $.parseJSON( data );
					if ( jsonData.table ) {
						var newTable = $( jsonData.table );
						bindFontFileTable( newTable );
						$( "#font_file_list" ).replaceWith( newTable );
					}
				},
				error: function( file, data ) {
					file.previewElement.classList.add("dz-error");
				}
			 } );

		 }

		 if ( $('#font_file_update_dropzone').length ) {
		 	var dz = new Dropzone( '#font_file_update_dropzone', {
				url: ajaxurl,
				clickable: true,
				params: {
					action: 'typolog_upload_font',
					font_file_id: $( "form#post input#post_ID" ).val()
				},
				init: function() {
					this.on( "addedfile", function( file ) {
						if ( !isFileSupported( file ) ) {
							this.removeFile( file );
						}
					});
				},
				success: function( file, data ) {
					file.previewElement.classList.add( "dz-success" );
					var jsonData = $.parseJSON( data );
					if ( jsonData.url ) {
						$( "a.font-file-download" ).attr( 'href', jsonData.url );
					}
				},
				error: function( file, data ) {
					file.previewElement.classList.add("dz-error");
				}
			 } );

		 }
		 
		 bindFontFileTable($('#font_file_list'));
		 
		 $('.delete-all-fonts').click(function(e) {
			e.preventDefault();
			if ( window.confirm( 'Are you sure you want to delete all fonts? This cannot be undone.' ) ) {
				deleteAllFonts();
				$(this).prop('disabled', true);
			}
		 });

		 $('.regenerate-all-fonts').click(function(e) {
			e.preventDefault();
			regenerateAllFonts();
			$(this).prop('disabled', true);
		 });

		 $('.reset-products').click(function(e) {
			e.preventDefault();
			resetProducts();
			$(this).prop('disabled', true);
		 });
		 
		 var mediaFrame;
		 
		 $('.upload-attachment-button').click(function(e) {
			e.preventDefault();

			if (mediaFrame) {
				mediaFrame.open();
				return;
			}
			 
			mediaFrame = wp.media({
				title: 'Select File(s) to Attach',
				button: {
					text: 'Attach File(s)'
				},
				multiple: true
			});
			
			mediaFrame.on('select', function() {
				var attachments = mediaFrame.state().get('selection').toJSON();
				var paramName = $('.typolog-attachment-list').attr('data-name');
				_.each(attachments, function(attachment) {
					$('.typolog-attachment-list').append( '<li class="typolog-attachment-item"><input type="checkbox" name="' + paramName + '[]" value="' + attachment.id + '" checked> <a href="' + attachment.url + '" target="_blank">' + attachment.name + '</a></li>');
				});
			});
			
			mediaFrame.open();
			 
		 });
		 
		 $('.upload-pdf-button').click(function(e) {
			e.preventDefault();

			if (mediaFrame) {
				mediaFrame.open();
				return;
			}
			 
			mediaFrame = wp.media({
				title: 'Select a File to Attach',
				button: {
					text: 'Attach File'
				}
			});
			
			mediaFrame.on('select', function() {
				var attachments = mediaFrame.state().get('selection').toJSON();
				var paramName = $('.typolog-attachment-list').attr('data-name');
				_.each(attachments, function(attachment) {
					$('#pdf_id').val( attachment.id );
					$('.pdf_link').attr( 'href', attachment.url );
					$('.pdf_link').html( attachment.name );
					$('.remove-pdf-link').show();
					$('.upload-pdf-button').hide();
				});
			});
			
			mediaFrame.open();
			 
		 });
		 
		 $('.remove-pdf-link').click(function(e) {
			 
			 e.preventDefault();
			
			$('#pdf_id').val( '' );
			$('.pdf_link').attr( 'href', '' );
			$('.pdf_link').html( '' );
			$('.remove-pdf-link').hide();
			$('.upload-pdf-button').show();
			 
		 });
		 
		 $('.sizes-control .font-choice').change(function(e) {
			$('.wrap.sizes .sizes-display .font-display').removeClass('active');
			_.each($(this).val(), function(el) {
				$('.wrap.sizes .sizes-display .font-display.font-' + el).addClass('active');
			});
		 });
		 
		 $('.sizes-display a.font-display').click(function(e) {
			e.preventDefault();	
			if ($(this).hasClass('active')) {
				if ($(this).hasClass('family-mode') && ($(this).parents('.sizes-display').find('a.font-display.active').length > 1)) {
					$(this).parents('.sizes-display').find('a.font-display.active').removeClass('family-mode').removeClass('active');
					$(this).addClass('active');
				} else {
					$(this).parents('.sizes-display').find('a.font-display').removeClass('active').removeClass('family-mode');
				}
			} else {
				$(this).parents('.sizes-display').find('a.font-display.active').removeClass('active').removeClass('family-mode');
				$(this).parents('.sizes-display').find('a.family-' + $(this).data('family-id')).addClass('active').addClass('family-mode'); 
			}
		 });
		 
		 $('.sizes-control .sub-size').click(function(e) {
			e.preventDefault();
			$('.sizes-control .save-sizes').prop('disabled', false);
			if ($('.sizes-display a.active').length) {
				var theSize = $('.sizes-display a.active').data('size-adjust') - 1;
				$('.sizes-display a.active').each(function() {
					$(this).data('size-adjust', $(this).data('size-adjust') - 1);
					$(this).css('font-size', $(this).data('size-adjust') + '%');
					if (theSize != $(this).data('size-adjust')) {
						console.log($(this).data('size-adjust'));
						theSize = '';
					}
				});
				$('.sizes-control input.size-adjust').val(theSize);
			} else {
				console.log($('.sizes-display').css('font-size'));
				$('.sizes-display').css('font-size', '-=10px');
			}
		 });

		 $('.sizes-control .add-size').click(function(e) {
			e.preventDefault();
			$('.sizes-control .save-sizes').prop('disabled', false);
			if ($('.sizes-display a.active').length) {
				var theSize = $('.sizes-display a.active').data('size-adjust') + 1;
				$('.sizes-display a.active').each(function() {
					$(this).data('size-adjust', $(this).data('size-adjust') + 1);
					$(this).css('font-size', $(this).data('size-adjust') + '%');
					if (theSize != $(this).data('size-adjust')) {
						console.log($(this).data('size-adjust'));
						theSize = '';
					}
				});
				$('.sizes-control input.size-adjust').val(theSize);
			} else {
				console.log($('.sizes-display').css('font-size'));
				$('.sizes-display').css('font-size', '+=10px');
			}
		 });
		 
		$('.sizes-control .save-sizes').click(function(e) {
			e.preventDefault();
			$('.sizes-control .spinner').addClass('is-active');
			$('.sizes-control .save-sizes').prop('disabled', true);
			var sizes = [];
			$('.sizes-display a.font-display').each(function() {
				sizes.push({
					id: $(this).data('font-id'),
					size_adjust: $(this).data('size-adjust')	
				});
			});
			console.log(sizes);
			$.post(ajaxurl, {
				action: 'save_size_adjustments',
				sizes: sizes
			}, function(data) {
				$('.sizes-control .spinner').removeClass('is-active');
				if (data.success) {
					$('.notice-wrap').append('<div class="notice notice-success is-dismissible"><p>' + data.success + '</p></div>');
				} else if (data.error) {
					$('.notice-wrap').append('<div class="notice notice-error is-dismissible"><p>' + data.error + '</p></div>');
					$('.sizes-control .save-sizes').prop('disabled', false);
				}
			}, 'json');
		});
		
		$('.family-font-order').sortable();
		
		$('.family-order-list').sortable();
		
		$( '.edit-fonts' ).on( 'click', function( e ) {
			e.preventDefault();
			
			$.get( {
				url: ajaxurl,
				data: {
					action: 'edit_family_fonts',
					family_id: $( e.currentTarget ).data( 'id' )
				},
				success: function( data ) {
					$( '.edit-family-fonts-container' ).html( data );
				},
				dataType: 'html'
			} );
			
		} );
		
		$( '.close-edit-family-fonts' ).on( 'click', function( e ) {
			$( e.currentTarget ).parent().remove();
		} );
		
		$('.reset-font-order').on( 'click', function( e ) { 
			e.preventDefault();
			$( '.family-font-order li' ).remove();
			$( '.family-font-order' ).append( '<input type="hidden" name="family_font_order" value=""><input type="hidden" name="family_main_font", value="">' );
		} );
		
		if ( $( '.price-table-form' ).length ) {
			
			$( '.price-table input[type=text]' ).on( 'change', function() {
				$( 'input.update-prices' ).prop( 'disabled', false );
			} );
						
		}
		
		$( ".control-table a.update-family-products" ).on( "click", regenerateFamilyFonts );
		
		$( ".admin-table .family-item .family-toggle" ).on( "click", function( e ) { 
			e.preventDefault();
			$( e.currentTarget ).toggleClass( "show" );
			var $el = $( e.currentTarget ).parents( ".family-row" ).next();
			console.log( $el );
			while ( $el.hasClass( "font-row" ) ) {
				$el = $el.toggleClass( "show" ).next();
			}
		} );
		
		
		$( '.delete-unused-fonts-button' ).on( 'click', function( e ) { 
			e.preventDefault();
			var uniqID = 'notice_' + _.random( 1000, 9999 );

			var data = $( '.unused-fonts-list input' ).serializeArray();
			var delete_fonts = _.pluck( data, 'value' );
			console.log( delete_fonts );
			
			var _process = function() {
				var delete_now = _.first( delete_fonts, 50 );
				delete_fonts = _.difference( delete_fonts, delete_now );
				$( '#' + uniqID ).append( "<p>Deleting " + delete_now.length + " fonts...</p>" );
				$.post( {
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'delete_fonts',
						delete_fonts: delete_now
					},
					success: function( data ) {
						if ( data.success ) {
							$( '#' + uniqID ).append( "<p>" + data.success + "</p>" );
						}
					},
					complete: function( data ) {
						if ( delete_fonts.length ) {
							_process();
						} else {
							$( '#' + uniqID ).append( "<p>Done!</p>" );
							$( '.unused-fonts-list input:checked' ).remove();
						}
					}
				} );
			}
			$( '.notice-wrap' ).append( "<div class='notice typolog-notice cleanup-notice updated' id='" + uniqID + "'></div>" );
			_process();
			
		} );


		$( '.delete-unused-files-button' ).on( 'click', function( e ) { 
			e.preventDefault();
			var uniqID = 'notice_' + _.random( 1000, 9999 );

			var data = $( '.unused-files-list input' ).serializeArray();
			var delete_files = _.pluck( data, 'value' );
			console.log( delete_files );
			
			var _process = function() {
				var delete_now = _.first( delete_files, 50 );
				delete_files = _.difference( delete_files, delete_now );
				$( '#' + uniqID ).append( "<p>Deleting " + delete_now.length + " files...</p>" );
				$.post( {
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'delete_font_files',
						delete_files: delete_now
					},
					success: function( data ) {
						if ( data.success ) {
							$( '#' + uniqID ).append( "<p>" + data.success + "</p>" );
						}
					},
					complete: function( data ) {
						if ( delete_files.length ) {
							_process();
						} else {
							$( '#' + uniqID ).append( "<p>Done!</p>" );
							$( '.unused-files-list input:checked' ).remove();
						}
					}
				} );
			}
			
			$( '.notice-wrap' ).append( "<div class='notice typolog-notice cleanup-notice updated' id='" + uniqID + "'></div>" );
			_process();
			
		} );


		$( '.delete-unused-products-button' ).on( 'click', function( e ) { 
			e.preventDefault();
			var uniqID = 'notice_' + _.random( 1000, 9999 );

			var data = $( '.unused-products-list input' ).serializeArray();
			var delete_products = _.pluck( data, 'value' );
			console.log( delete_products );
			
			var _process = function() {
				var delete_now = _.first( delete_products, 50 );
				delete_products = _.difference( delete_products, delete_now );
				$( '#' + uniqID ).append( "<p>Deleting " + delete_now.length + " products...</p>" );
				$.post( {
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'delete_products',
						delete_products: delete_now
					},
					success: function( data ) {
						if ( data.success ) {
							$( '#' + uniqID ).append( "<p>" + data.success + "</p>" );
						}
					},
					complete: function( data ) {
						if ( delete_products.length ) {
							_process();
						} else {
							$( '#' + uniqID ).append( "<p>Done!</p><" );
							$( '.unused-products-list input:checked' ).remove();
						}
					}
				} );
			}
			
			$( '.notice-wrap' ).append( "<div class='notice typolog-notice cleanup-notice updated' id='" + uniqID + "'></div>" );
			_process();
			
		} );

		$( '.delete-unused-downloads-button' ).on( 'click', function( e ) { 
			e.preventDefault();
			var uniqID = 'notice_' + _.random( 1000, 9999 );

			var data = $( '.unused-downloads-list input' ).serializeArray();
			var delete_downloads = _.pluck( data, 'value' );
			console.log( delete_downloads );
			
			var _process = function() {
				var delete_now = _.first( delete_downloads, 50 );
				delete_downloads = _.difference( delete_downloads, delete_now );
				$( '#' + uniqID ).append( "<p>Deleting " + delete_now.length + " downloads...</p>" );
				$.post( {
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'delete_downloads',
						delete_downloads: delete_now
					},
					success: function( data ) {
						if ( data.success ) {
							$( '#' + uniqID ).append( "<p>" + data.success + "</p>" );
						}
					},
					complete: function( data ) {
						if ( delete_downloads.length ) {
							_process();
						} else {
							$( '#' + uniqID ).append( "<p>Done!</p>" );
							$( '.unused-downloads-list input:checked' ).remove();
						}
					}
				} );
			}
			
			$( '.notice-wrap' ).append( "<div class='notice typolog-notice cleanup-notice updated' id='" + uniqID + "'></div>" );
			_process();
			
		} );

		$( '.delete-unused-originals-button' ).on( 'click', function( e ) { 
			e.preventDefault();
			var uniqID = 'notice_' + _.random( 1000, 9999 );

			var data = $( '.unused-originals-list input' ).serializeArray();
			var delete_originals = _.pluck( data, 'value' );
			console.log( delete_originals );
			
			var _process = function() {
				var delete_now = _.first( delete_originals, 50 );
				delete_originals = _.difference( delete_originals, delete_now );
				$( '#' + uniqID ).append( "<p>Deleting " + delete_now.length + " original font files...</p>" );
				$.post( {
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'delete_originals',
						delete_originals: delete_now
					},
					success: function( data ) {
						if ( data.success ) {
							$( '#' + uniqID ).append( "<p>" + data.success + "</p>" );
						}
					},
					complete: function( data ) {
						if ( delete_originals.length ) {
							_process();
						} else {
							$( '#' + uniqID ).append( "<p>Done!</p>" );
							$( '.unused-originals-list input:checked' ).remove();
						}
					}
				} );
			}
			
			$( '.notice-wrap' ).append( "<div class='notice typolog-notice cleanup-notice updated' id='" + uniqID + "'></div>" );
			_process();
			
		} );
		
		$( '.styles-dictionary-wrapper .add-entry' ).on( 'click', function( e ) { 
			e.preventDefault();
			var $entry = $( $( '#styles_dictionary_entry' ).html() );
			$entry.find( '.delete-entry' ).on( 'click', deleteEntry );
			$( this ).before( $entry );
		} );

		$( '.weights-map-wrapper .add-entry' ).on( 'click', function( e ) { 
			e.preventDefault();
			var $entry = $( $( '#weights_map_entry' ).html() );
			$entry.find( '.delete-entry' ).on( 'click', deleteEntry );
			$( this ).before( $entry );
		} );

		$( '.styles-map-wrapper .add-entry' ).on( 'click', function( e ) { 
			e.preventDefault();
			var $entry = $( $( '#styles_map_entry' ).html() );
			$entry.find( '.delete-entry' ).on( 'click', deleteEntry );
			$( this ).before( $entry );
		} );
		
		$( '.allowed-file-extensions-wrapper .add-entry' ).on( 'click', function( e ) { 
			e.preventDefault();
			var $entry = $( $( '#allowed_file_extensions_entry' ).html() );
			$entry.find( '.delete-entry' ).on( 'click', deleteEntry );
			$( this ).before( $entry );
		} );

		$( '.strip-from-family-names-wrapper .add-entry' ).on( 'click', function( e ) { 
			e.preventDefault();
			var $entry = $( $( '#strip_from_family_names_entry' ).html() );
			$entry.find( '.delete-entry' ).on( 'click', deleteEntry );
			$( this ).before( $entry );
		} );
		
		var deleteEntry = function( e ) {
			e.preventDefault();
			$( this ).parent().remove();
		}

		$( 'a.delete-entry' ).on( 'click', deleteEntry );
		
		$( '.typolog-settings-form h2' ).on( 'click', function( e ) { 
			$( e.currentTarget ).toggleClass( 'active' ).nextUntil( 'h2, .submit' ).toggleClass( 'show' );
		} );
		 
	 });

} )( jQuery );
