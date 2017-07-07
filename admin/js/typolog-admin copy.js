var typologDZ;

var startElement;

var acceptedFileTypes = [ 'otf', 'ttf', 'woff', 'woff2', 'eot', 'svg' ];

var stripFromFamilyNames = [ 'fot' ];

var rePattern = /([A-Za-z0-9\s_-]*)(\.([^.]+))?$/;

var generatorUILabels = {
	displayNames: 'Display Names:',
	fontNames: 'Font Names:'
};

var generatedFontListSelector = '.generated-fonts-list';

var generatorDZSelector = 'form#generator_dropzone';

var fontWeightMap = {
	'thin': 		250,
	'extralight': 	250,
	'ultralight': 	250,
	'light': 		300,
	'normal': 		400,
	'regular': 		400,
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
	'italic':	'italic',
	'oblique':	'oblique'
};

var fonts = [];

var isFileSupported = function(file) {
	return _.indexOf(acceptedFileTypes, rePattern.exec(file.name)[3].toLowerCase()) > -1;
}
 
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

var FontItem = function(options) {
 	_.defaults(options, {
		familyName: 'Unknown',
		styleName: 'Regular',
		files: []
	});
	if (_.isString(options.files)) {
		options.files = [ options.files ];
	}
	this.uid = _.uniqueId('font_');
	_.each(stripFromFamilyNames, function(str) {
		options.familyName = options.familyName.replace(new RegExp(str + '$', 'i'), '');
	});
	this.displayFamilyName = options.familyName;
	this.displayStyleName = options.styleName;
	this.familyName = options.familyName;
	this.styleName = options.styleName;
	this.fontWeight = mapMatch(fontWeightMap, options.styleName, 400);
	this.fontStyle = mapMatch(fontStyleMap, options.styleName, 'normal');
	this.files = options.files;
	this.simplifyFilesObject = function() {
		this.files = _.map(this.files, function(file) {
			file = file.name;
			return file;
		});
	}
	this.updateWebFamilyName = function() {
		this.webFamilyName = this.familyName + ' ' + this.styleName;
	}
	this.updateWebFamilyName();
};

var addFontToList = function(fontList, font) {
	 var match = _.find(fontList, function (f) {
		 return (f.familyName.toLowerCase().replace(/\s/g, '') == font.familyName.toLowerCase().replace(/\s/g, '')) && (f.styleName.toLowerCase().replace(/\s/g, '') == font.styleName.toLowerCase().replace(/\s/g, ''));
	 });
	 if (!_.isUndefined(match)) {
		 match.files = _.union(match.files, font.files);
		 return match;
	 }
	 fontList.push(font);
	 return font;
};

var createEmptyFontItem = function(fontList, options) {
	if (options.familyName) {
		var newFont = new FontItem(options);
		addFontToList(fontList, newFont);
		return newFont;
	} else {
		return false;
	}
}

var createFontItem = function(fontList, file, callbackFunc) {
	var reader = new FileReader();
	reader.onload = function(e) {
		var familyName, styleName;
		try { // try to parse the font using the opentype.js parser
			var font = opentype.parse(e.target.result);
		}
		catch(err) {
			console.log(err.message);
		}
		if (!_.isUndefined(font)) { // did it work?
			console.log(font.names);
			if (font.names.preferredFamily) {
				familyName = font.names.preferredFamily.en;
				styleName = font.names.preferredSubfamily.en;
			} else if (font.names.compatibleFullName) { // some woff files hide the font name under this property
				var fullName = font.names.compatibleFullName.en;
				familyName = fullName.substr(0, fullName.lastIndexOf(' '));
				styleName = fullName.split(" ").splice(-1)[0];
			} else if (font.names.fontFamily) { // this is after the woff thing bc woffs usually define this as "."
				familyName = font.names.fontFamily.en;
				styleName = font.names.fontSubfamily.en;
			}
		}
		if (_.isString(familyName)) {
			familyName = familyName.replace(/[^A-Za-z0-9\s]/g, ''); // filter out family names that are only "."
		}
		if (!familyName) { // if it didn't, try breaking up the file name
			var fullName = rePattern.exec(file.name)[1];
			fullName = fullName.replace(/-webfont/, '').replace(/[-]/g, ' ');
			familyName = fullName.substr(0, fullName.lastIndexOf(' '));
			styleName = fullName.split(" ").splice(-1)[0];
		}
		if (familyName) {
			var newFont = new FontItem({
				familyName: familyName,
				styleName: styleName,
				files: [ file ]
			});
			callbackFunc(addFontToList(fontList, newFont));
		}
	}
	reader.readAsArrayBuffer(file);
};

(function( $ ) {
	'use strict';

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
	 
	 var findUidIndex = function(fontsList, uid) {
		 return _.findIndex(fontsList, function(f) { return f.uid == uid; });
	 }
	 
	 var findFileIndex = function(files, filename) {
		 return _.findIndex(files, function(f) { return f.name == filename; })
	 }
	 
	 var unbindTextFields = function($el, $boundTextFields) {
		 $el.off('keyup').add($boundTextFields).removeClass('bound');
		 $('.bind-text-fields-button').remove();
	 }
	 
	 var bindTextFields = function(fontsList, $el) {
		 var $boundTextFields = $el.parents(generatedFontListSelector).find('input[class="' + $el.attr('class') + '"]').filter(function() {
			return ($(this).val().toLowerCase() == $el.val().toLowerCase()) && ($(this).parents('.font-item').attr('id') !== $el.parents('.font-item').attr('id'));
		 });
		 if ($boundTextFields.length) {
			 var $bindButton = $('<a>').addClass('bind-text-fields-button').attr('href', '#').click(function(e) {
				 e.preventDefault();
				 console.log('unbind');
				 unbindTextFields($el, $boundTextFields);
			 });
			 $el.add($boundTextFields).addClass('bound').parent().append($bindButton);
			 $el.blur(function(e) {
				 unbindTextFields($(this), $boundTextFields);
			 });
			 $el.on('keyup', function(e) {
				 if (e.which == 27) {
					 unbindTextFields($(this), $boundTextFields);
				 }
				$boundTextFields.val($(this).val()).change();
			 });
		 }
	 }
	 
	 var removeFile = function(fontsList, $fileElement) {
		var fontIndex = findUidIndex(fontsList, $fileElement.parents('.font-item').attr('id'));
		var fileIndex = findFileIndex(fontsList[fontIndex].files, $fileElement.text());
		typologDZ.removeFile(fontsList[fontIndex].files[fileIndex]);
		fontsList[fontIndex].files.splice(fileIndex, 1);
		$fileElement.remove();
	 }

	 var removeAllFontFiles = function(fontsList, $fontElement) {
		var fontIndex = findUidIndex(fontsList, $fontElement.attr('id'));
		_.each(fontsList[fontIndex].files, function(file) {
			typologDZ.removeFile(file);
		});
		fontsList[fontIndex].files = [];
		$fontElement.find('.filename').remove();
	 }
	 
	 var updateFontItem = function(fontsList, uid, property, value) {
		 fontsList[findUidIndex(fontsList, uid)][property] = value;
	 }

	 var removeFontItem = function(fontsList, uid) {
		 fontsList.splice(findUidIndex(fontsList, uid), 1);
	 }
	 
	 var updateFontList = function(fontsList, font) {
		var filenamesString = _.pluck(font.files, 'name').join("</li><li class='filename'>");
		if (filenamesString) {
			filenamesString = "<li class='filename'>" + filenamesString + "</li>";
		}
		var $newFontElement = $('<li>').addClass('font-item').attr('id', font.uid).html("<div class='font-details'><div class='font-names-section display-names'><label><span class='font-names-section-label-text'>" + generatorUILabels.displayNames + "</span> <div class='text-field-wrapper'><input type='text' class='display-family-name' name='displayFamilyName' value='" + font.displayFamilyName + "'></div></label> <div class='text-field-wrapper'><input type='text' class='display-style-name' name='displayStyleName' value='" + font.displayStyleName + "'></div></div> <div class='font-names-section font-names'><label><span class='font-names-section-label-text'>" + generatorUILabels.fontNames + "</span> <div class='text-field-wrapper'><input type='text' class='family-name' name='familyName' value='" + font.familyName + "'></div></label> <div class='text-field-wrapper'><input type='text' class='style-name' name='styleName' value='" + font.styleName + "'></div></div></div> <ul class='filenames'>" + filenamesString + "</ul>");
		$newFontElement.find('.filename').append('<a href="#" class="remove-file"></a>');
		$newFontElement.append('<div class="toolbox"><a href="#" class="toolbox-button remove-font"></a> <a href="#" class="toolbox-button sort-handle"></a></div>');
		 if ($('#' + font.uid).length) {
			 $('#' + font.uid).replaceWith($newFontElement);
		 } else {
			 $(generatedFontListSelector).append($newFontElement);
		 }
		 $newFontElement.find('.remove-file').click(function(e) {
			 e.preventDefault();
			 removeFile(fontsList, $(this).parents('.filename'));
		 });
		 $newFontElement.find('.remove-font').click(function(e) {
			 e.preventDefault();
			 if ($(this).parents('.font-item').find('.filename').length) {
				 if (!confirm('Are you sure you want to remove this font? This will remove all associated font files, as well.')) {
					 return false;
				 }
			 }
			 var $fontItem = $(this).parents('.font-item');
			 removeAllFontFiles(fontsList, $fontItem);
			 removeFontItem(fontsList, $fontItem.attr('id'));
			 $fontItem.remove();
		 });
		 $newFontElement.find('input[type=text]').focus(function(e) {
			 bindTextFields(fontsList, $(this));
		 }).change(function(e) {
			 updateFontItem(fontsList, $(this).parents('.font-item').attr('id'), $(this).attr('name'), $(this).val());
		 });
		 $newFontElement.sortable({
			 connectWith: '.font-item',
			 items: '.filenames > .filename',
			 start: function(e, ui) {
				startElement = $(this); 
			 },
			 beforeStop: function(e, ui) {
				 console.log(ui);
				 var destItem = ui.item.parents('.font-item');
				 if (!ui.item.parents('filenames').length) {
					 ui.item.detach().appendTo(destItem.find('.filenames'));
				 }
				 var origin_index = findUidIndex(fontsList, startElement.attr('id'));
				 var dest_index = findUidIndex(fontsList, destItem.attr('id'));
				 var origin_file_index = findFileIndex(fontsList[origin_index].files, ui.item.text());
				 var fileElement = fontsList[origin_index].files.splice(origin_file_index, 1).pop();
				 fontsList[dest_index].files.push(fileElement);
			 }
		 });
		 
		 $(generatedFontListSelector).sortable("refresh");
	 }
	 
	 var simplifyFontObjects = function(fontsList) {
		 return _.map(fontsList, function(font) {
			font.files = _.map(font.files, function(file) {
				file = file.name;
				return file;
			});
			return font;
		 });
	 }

	 var validateWebFamilyNames = function(fontsList) {
		 return _.map(fontsList, function(font) {
			font.updateWebFamilyName();
			return font;
		 });
	 }
	 
	 $(function() {
		 
		 if ($(generatorDZSelector).length) {
			 
			 $(generatedFontListSelector).sortable({
				 handle: '.sort-handle',
				 items: '> .font-item'
			 });
			 
			 typologDZ = new Dropzone(generatorDZSelector, {
				url: ajaxurl,
				autoProcessQueue: false,
				clickable: true,
				init: function() {
					this.on("addedfile", function(file) {
						if (isFileSupported(file)) {
							createFontItem(fonts, file, function(font) {
								console.log(font);
								updateFontList(fonts, font);
							});
						} else {
							typologDZ.removeFile(file);
						}
					});
				}
			 });
			 
			 $('.button-upload-generator-files').click(function(e) {
				e.preventDefault();
// 				typologDZ.processQueue();
				$.post(ajaxurl, {
					'action': 'generate_catalog',
					'fonts': JSON.stringify(validateWebFamilyNames(simplifyFontObjects(fonts)))
				}, function(data) {
					if (data) {
						alert('success!!!');
					}
				});
				$(this).prop('disabled', true);
				$('.button-add-font').prop('disabled', true);
				$(generatedFontListSelector).sortable('disable').find('.font-item').sortable('disable').find('input').prop('disabled', true);
				$(generatedFontListSelector).find('.toolbox').remove();
				$(generatedFontListSelector).find('.remove-file').remove();
			 });
			 
			 $('.button-add-font').click(function(e) {
				e.preventDefault(); 
				var font = createEmptyFontItem(fonts, {
					familyName: prompt('Enter new family name:')
				});
				if (font) {
					updateFontList(fonts, font);
				}
			 });
		 
		 }
		 
	 });

})( jQuery );
