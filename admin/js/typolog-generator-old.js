// Globals:

var startElement;

var stripFromFamilyNames = [ 'fot' ];

var rePattern = /([A-Za-z0-9\s_-]*)(\.([^.]+))?$/;

var generatedFontListSelector = '.generated-fonts-list';

var fontWeightMap = {
	'thin': 		250,
	'extralight': 	250,
	'ultralight': 	250,
	'light': 		300,
	'normal': 		400,
	'regular': 		400,
	'book':			400,
	'medium': 		500,
	'demi': 		600,
	'semibold': 	600,
	'bold': 		700,
	'ultrabold': 	800,
	'extrabold': 	800,
	'black': 		900,
	'heavy':		900
};

var fontStyleMap = {
	'normal': 'normal',
	'italic':	'italic',
	'oblique':	'oblique'
};

var fonts = [];

var mapMatch = function(map, value, def) { // finds substring match of keys in object and returns the value if found, returns optional default value if no match is found - false if no default value is provided
	var match = _.pick(map, function(v, key, object) {
		return value.toLowerCase().indexOf(key) > -1;
	});
	if (_.values(match).length) {
		return _.values(match).pop();
	}
	if (!_.isUndefined(def)) {
		return def;
	}
	return false;
};

// Font Model

var Font = Backbone.Model.extend({
	
	defaults: {
		familyName:	'Unknown',
		styleName:	'Regular',
		files:		[]
	},
	
	initialize: function() {
		var _this = this;
		_.each(stripFromFamilyNames, function(str) {
			var _familyName = _.clone(_this.get('familyName'));
			_this.set('familyName', _familyName.replace(new RegExp(str + '$', 'i'), ''));
		});
		this.set({
			cid:				this.cid,
			displayFamilyName: 	this.get('familyName'),
			displayStyleName: 	mapMatch( fontStylesDictionary, this.get('styleName'), this.get('styleName') ),
			fontWeight: 		mapMatch(fontWeightMap, this.get('styleName'), 400),
			fontStyle:			mapMatch(fontStyleMap, this.get('styleName'), 'normal')
		});
		this.on({
			'change:familyName': this.updateWebFamilyName,
			'change:styleName': this.updateWebFamilyName
		});
		this.updateWebFamilyName();
	},
	
	findFileIndex: function(filename) {
		return _.findIndex(this.get('files'), function(f) { return f.name == filename; }); // find 
	},
	
	addFile: function(file) {
		var _files = _.clone(this.get('files'));
		_files.push(file);
		this.set('files', _files);
		return file;
	},
	
	removeFile: function(filename) {
		var fileIndex = this.findFileIndex(filename);
		if (fileIndex > -1) {
			var _files = _.clone(this.get('files'));
			var _file = _files.splice(this.findFileIndex(filename), 1);
			if (_.isArray(_file)) {
				_file = _file[0];
			}
			this.set('files', _files);
			return _file;
		}
		return false;
	},
	
	removeAllFiles: function() {
		var _files = _.clone(this.get('files'));
		this.set('files', []);
		return _files;
	},
	
	simplifyFilesArray: function() {
		return this.set('files',  _.map(this.get('files'), function(file) {
			return file.name;
		}));
	},
	
	updateWebFamilyName: function() {
		return this.set('webFamilyName', this.get('familyName') + ' ' + this.get('styleName'));
	}
	
});

// Font List Collection Model

var FontList = Backbone.Collection.extend({
	
	model: Font,
	
	comparator: 'familyName',
	
	matchFont: function(font) {
		return this.find(function (f) {
			return (f.get('familyName').toLowerCase().replace(/\s/g, '') == font.get('familyName').toLowerCase().replace(/\s/g, '')) && (f.get('styleName').toLowerCase().replace(/\s/g, '') == font.get('styleName').toLowerCase().replace(/\s/g, ''));
	 	});
	},
	
	addFont: function(font) {
		var match = this.matchFont(font);
		if (!_.isUndefined(match)) {
			match.set('files', _.union(match.get('files'), font.get('files')));
			return match;
		}
		this.add(font);
		return font;
	},
	
	addFromFile: function(file, callbackFunc) {
		var _this = this;
		var reader = new FileReader();
		reader.onload = function(e) {
			var familyName, styleName;
			try { // try to parse the font using the opentype.js parser
				var parsedFont = opentype.parse(e.target.result);
			}
			catch(err) {
				console.log(err.message);
			}
			if (!_.isUndefined(parsedFont)) { // did it work?
				console.log(parsedFont);
				if (parsedFont.names.preferredFamily) {
					familyName = parsedFont.names.preferredFamily.en;
					styleName = parsedFont.names.preferredSubfamily.en;
				} else if (parsedFont.names.compatibleFullName) { // some woff files hide the font name under this property
					var fullName = parsedFont.names.compatibleFullName.en;
					familyName = fullName.substr(0, fullName.lastIndexOf(' '));
					styleName = fullName.split(" ").splice(-1)[0];
				} else if (parsedFont.names.fontFamily) { // this is after the woff thing bc woffs usually define this as "."
					familyName = parsedFont.names.fontFamily.en;
					if ( parsedFont.names.preferredSubfamily ) {
						styleName = parsedFont.names.preferredSubfamily.en;
					} else {
						styleName = parsedFont.names.fontSubfamily.en;
					}
				}
			}
			if (_.isString(familyName)) {
				familyName = familyName.replace(/[^A-Za-z0-9\s]/g, ''); // filter out family names that contain only "."
			}
			if (!familyName) { // if it didn't, try breaking up the file name
				var fullName = rePattern.exec(file.name)[1];
				fullName = fullName.replace(/-webfont/, '').replace(/[-]/g, ' ');
				var familyArray = fullName.replace(/([a-z0-9])([A-Z])/g, ' $1 $2').replace(/^./, function(str){ return str.toUpperCase(); }).split( ' ' );
				console.log( familyArray );
				if ( familyArray.length > 1 ) {
					styleName = familyArray.splice(-1)[0];
					familyName = familyArray.join('');
				} else {
					familyName = familyArray.pop();
					styleName = 'Regular';
				}
/*
				familyName = fullName.substr(0, fullName.lastIndexOf(' '));
				styleName = fullName.split(" ").splice(-1)[0];
*/
			}
			if (familyName) {
				var f = new Font({
					familyName: familyName,
					styleName: styleName,
					files: [ file ]
				});
				if (_.isUndefined(callbackFunc)) {
					_this.addFont(f);
				} else {
					callbackFunc(_this.addFont(f));
				}
			}
		}
		return reader.readAsArrayBuffer(file);
	},
	
	exportToJSON: function() {
		
	}
	
});

var Fonts = new FontList;


 
( function( $ ) {

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

// Font View Class

 var FontView = Backbone.View.extend({
	
	tagName: "li",
	
	className: "font-item",
	
	id: function() {
		return this.model.cid;
	},
	
/*
	template: _.template($('#font-item-template').html()),
	
	fileTemplate: _.template($('#file-item-template').html()),
*/
	
	events: {
		'click .remove-file': 'removeFile',
		'click .remove-font':	'removeFont',
		'input input[type=text]': 'updateFontNames',
		'focus input[type=text]': 'bindTextFields'
	},
	
	initialize: function(options) {
		
		this.template = _.template($('#font-item-template').html());
		this.fileTemplate = _.template($('#file-item-template').html());
		this.removeFontPrompt = $('#remove-font-prompt').text();
		
		if (!_.isUndefined(options)) {
			if (!_.isUndefined(options.dz)) {
				this.dz = options.dz;
			}
		}
		
		this.listenTo(this.model, 'change:lock', this.lock);
		this.listenTo(this.model, 'change:files', this.render);
// 		this.listenTo(this.model, 'change', this.render);
		this.listenTo(this.model, 'remove', this.remove);
		this.listenTo(this.model, 'change:familyName', this.checkNames );
		this.listenTo(this.model, 'change:styleName', this.checkNames );
		
	},
	
	lock: function() {
		this.stopListening();
	},
	
	render: function() {
		this.$el.html(this.template(this.model.toJSON()));
		var _this = this;
		var $files = this.$el.find('ul.files');
		_.each(this.model.get('files'), function(f) {
			$files.append(_this.fileTemplate({ name: f.name }));
		});
		this.$el.sortable({
			connectWith: '.font-item',
			items: '.files > .file-item',
			start: function(e, ui) {
				startElement = $(this); 
			},
			beforeStop: function(e, ui) {
				var destItem = ui.item.parents('.font-item');
				if (!ui.item.parents('.files').length) {
					ui.item.detach().appendTo(destItem.find('.filenames'));
				}
				var file = _this.model.removeFile(ui.item.text().trim());
				if (file) {
					_this.model.collection.get({ cid: destItem.attr('id') }).addFile(file);
				}
			}
		});
		this.checkNames();
		return this;
	},
	
	checkNames: function() {
		console.log('checking names');
		if ( _.where( FontefFontNames, { family: this.model.get('familyName'), style: this.model.get('styleName') } ).length ) {
			this.$('.font-notice').html( $('#font-exists').html() );
		} else if ( _.contains( FontefFamilyNames, this.model.get('familyName') ) ) {
			this.$('.font-notice').html( $('#family-exists').html() );
		} else {
			this.$('.font-notice').html( '' );
		}
	},

	removeFile: function(e) {
		e.preventDefault();
		var file = this.model.removeFile(jQuery(e.currentTarget).parents('.file-item').text().trim());
		if (file) {
			this.dz.removeFile(file);
		}
	},
	
	removeFont: function(e) {
		e.preventDefault();
		var _this = this;
		 if (this.model.get('files').length) {
			 if (!confirm(this.removeFontPrompt)) {
				 return false;
			 }
		 }
		 var files = this.model.removeAllFiles();
		 _.each(files, function(f) {
			 _this.dz.removeFile(f);
		 });
		 this.model.collection.remove(this.model);
	},
	
	updateFontNames: function(e) {
		this.model.set('familyName', this.$el.find('input.family-name').val());
		this.model.set('styleName', this.$el.find('input.style-name').val());
		this.model.set('displayFamilyName', this.$el.find('input.display-family-name').val());
		this.model.set('displayStyleName', this.$el.find('input.display-style-name').val());
	},
	
	bindTextFields: function( e ) {
		
		var _field = $( e.currentTarget );
		
		var _fieldVal = _field.val().toLowerCase();
		
		var _fieldId = _field.parents( '.font-item' ).attr( 'id' );
		
		var _fieldClass = _field.attr( 'class' );
		
		var _boundTextFields = _field.parents( generatedFontListSelector ).find( 'input[class="' + _fieldClass + '"]' ).filter( function() {
			
			return 	( $( this ).val().toLowerCase() == _fieldVal ) && 
			
					( $( this ).parents( '.font-item' ).attr( 'id' ) !== _fieldId );
					
		});
		
		if ( !_boundTextFields.length ) return;

		var _unbindTimeout = null;
		
		var _unbindButton = $('<a></a>').attr( 'href', '#' ).addClass( 'bind-text-fields-button' );
		
		var _unbind = function() {
			
			_boundTextFields.change();
			
			_field.off( 'keyup.unbind' ).add( _boundTextFields ).removeClass( 'bound' );
			
			$( '.bind-text-fields-button' ).remove();
			
		};
		
	 	_field.add( _boundTextFields ).addClass( 'bound' ).parent().append( _unbindButton );
	 	
	 	$( '.bind-text-fields-button' ).on( 'click.unbind', function( e ) {
		 	
			e.preventDefault();
			
		 	if ( _unbindTimeout ) clearTimeout( _unbindTimeout );
		 	
			_unbind();
			
	 	});
	 	
	 	_field
	 	
	 		.on( 'blur.unbind', function( e ) {
		 		
			 	_unbindTimeout = setTimeout( _unbind, 100 );
			 	
		 	})
		 	
		 	.on( 'keyup.unbind', function(e) {
			 	
			 	if ( 27 == e.which ) {
				 	
			 		unbind();
			 		
			 	}
			 	
			 	_boundTextFields.val( _field.val() ).trigger( 'input' );
			 	
		 	});
	 	
 	}

	 
 });
 
 // App View Class
 
 var GeneratorView = Backbone.View.extend({
	 
	 el: '.generator',
	 
	 events: {
		 'click .button-upload-generator-files': 'uploadFiles',
		 'click .button-add-font': 'addEmptyFont'
	 },
	 
	 initialize: function() {
		var _this = this;
		this.addFontPrompt = $('#add-font-prompt').text();

		 this.$(generatedFontListSelector).sortable({
			 handle: '.sort-handle',
			 items: '> .font-item'
		 });
		 
		 this.dz = new Dropzone('.dnd-zone', {
			url: ajaxurl,
			autoProcessQueue: false,
			clickable: true,
			init: function() {
				this.on("addedfile", function(file) {
					if (isFileSupported(file)) {
						Fonts.addFromFile(file);
					} else {
						_this.dz.removeFile(file);
					}
				});
			}
		 });
		 			 			 
		 this.listenTo(Fonts, 'add', this.addFont);		
	 },
	 
	 addFont: function(font) {
		 var view = new FontView({ model: font, dz: this.dz });
		 this.$el.find(generatedFontListSelector).append(view.render().el);
	 },
	 
	 uploadFiles: function(e) {
		 var _this = this;
			this.dz.processQueue();
			this.dz.on("complete", function() {
				_this.dz.processQueue();
			});
			Fonts.each(function(f) {
				f.set('lock', true);
				f.simplifyFilesArray();
			});
			this.dz.on("queuecomplete", function() {
				var commercial = 0;
				if ($('#commercial_all').prop('checked')) {
					commercial = 1;
				}
				var collectionID = 0;
				if ( $('#collection_all').val() ) {
					collectionID = $('#collection_all').val();
				}
				var familyList = _.groupBy( Fonts.toJSON(), 'familyName' );
				var familyNames = _.keys( familyList );
				var _processFamily = function() {
					var curFamily = familyNames.pop();
					$( '.generator-notice' ).append( "<p>" + $( "#generating-message" ).text() + " " + familyList[ curFamily ][0].displayFamilyName + "...</p>" );
					$.post( {
						url: ajaxurl, 
						data: {
						'action': 'typolog_upload_catalog',
						'commercial': commercial,
						'collection': collectionID,
						'fonts': JSON.stringify( familyList[ curFamily ] )
						}, 
						success: function(data) {
							if (data) {
								if (_.has(data, 'error')) {
									console.log('Error: ', data.error);
									$('.generator-notice').append('<p>' + familyList[ curFamily ][0].displayFamilyName + ': ' + data.error + '</p>');
								} else if (_.has(data, 'success')) {
									console.log('Success: ', data.success, data.report);
									var reportMessage = '';
									if ( data.report_message ) {
										reportMessage = data.report_message;
									}
									$('.generator-notice').append('<p>' + familyList[ curFamily ][0].displayFamilyName + ': ' + data.success + " " + reportMessage + '</p>');
								} else {
									console.log('Unknown error', data);
									$('.generator-notice').append('<p>'  + familyList[ curFamily ][0].displayFamilyName + ': ' + $( "#unknown-error-message" ).text() + '</p>');
								}
							} else {
								console.log('Error: no data', data);
								$('.generator-notice').append('<p>'  + familyList[ curFamily ][0].displayFamilyName + ': ' + $( "#error-message" ).text() + ' ' + $( "#no-data-error-message" ).text() + '</p>');
							}
						},
						error: function( xhr, status, errorText ) {
							if ( errorText ) {
								$( '.generator-notice' ).append( '<p>'  + familyList[ curFamily ][0].displayFamilyName + ': ' + $( "#error-message" ).text() + ' ' + errorText + '</p>' );
							} else {
								$( '.generator-notice' ).append( '<p>'  + familyList[ curFamily ][0].displayFamilyName + ': ' + $( "#error-message" ).text() + ' ' + status + '</p>' );
							}
						},
						complete: function() {
							if ( familyNames.length ) {
								_processFamily();
							} else {
								$('.generator-notice').addClass('updated').append('<p>' + $( "#done-message" ).text() + '</p>');
								$('.generator-controls .spinner').removeClass('is-active');
							}
						},
						dataType: 'json'
					} );
				}
				_processFamily();
			});
			$(e.currentTarget).prop('disabled', true);
			$('.button-add-font').prop('disabled', true);
			$(generatedFontListSelector).sortable('disable').find('.font-item').sortable('disable').find('input').prop('disabled', true);
			$(generatedFontListSelector).find('.toolbox').remove();
			$(generatedFontListSelector).find('.remove-file').remove();
			$('.generator-controls .spinner').addClass('is-active');
			$('.generator-notice').html( '<p>' + $( "#uploading-files-message" ).text() + '</p>' );
	 },
	 
	 addEmptyFont: function() {
		Fonts.addFont(new Font({ familyName: prompt(this.addFontPrompt) }));
	 }
	 
 });

	
} )( jQuery );