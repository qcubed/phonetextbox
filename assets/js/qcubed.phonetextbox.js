/**************************************************************************
 * JQuery Phone Text Box Widget
 * 
 * Cleans up a U.S. based phone number. Intended for use as a QCubed plugin,
 * but can be used as a standalone widget.
 * 
 * Requires: JQuery UI 1.9 or later for widget factory support.
 * 
 * Hosted at: https://github.com/qcubed/QPhoneTextBox. 
 * Post bug reports, suggestions and new code there.
 * 
 * Usage: $("#myTextBox").phoneTextBox({defaultAreaCode: "650"});
 * 
 * Copyright 2013 Shannon Pekary
 * Licensed under the MIT License
 * 
 ****************************************************************************/

jQuery.widget( "qcubed.phoneTextBox",  {
	options: {
		defaultAreaCode: null
	},
	_create: function() {
		this._on( this.element, {
			focus: function() {
				this.placeDefault();
			},
			blur: function( event ) {
				this.checkChanged();
			}
		});
	},
	
	placeDefault: function () {
		var input = this.element.get(0);
		
		if (input.value == '' && this.options.defaultAreaCode && this.options.defaultAreaCode != '') {
			input.value = '(' + this.options.defaultAreaCode + ') ';
			if (input.setSelectionRange) {
				input.focus();
				setTimeout(function() { input.setSelectionRange(input.value.length, input.value.length) }, 0);
			}
			else if (input.createTextRange) {
				var range = input.createTextRange();
				range.collapse(true);
				range.moveEnd('character', input.value.length);
				range.moveStart('character', input.value.length);
				range.select();
			}
		}
	},
	
	checkChanged: function () {
		var input = this.element.get(0);
		var strOut;

		if (input.value == '(' + this.options.defaultAreaCode + ') ') {
			input.value = '';
			return;
		}
		var str = input.value.replace(/[^0-9x]/g,''); // strip bad chars
		
		var extOffset = str.indexOf('x');
		var strExtension;
		
		if (extOffset != -1) { 
			strExtension = str.substr(extOffset + 1);
			str = str.substr(0, extOffset);
		}
		
		if (str.length == 7) {
			if (this.options.defaultAreaCode) {
				strOut = "(" + this.options.defaultAreaCode + ") ";
			} else {
				strOut = "";
			}
			strOut = strOut + str.substr(0,3) + "-" + str.substr(3);
		}
		else if (str.length == 10) {
			strOut = "(" + str.substr(0,3) + ") " + str.substr(3,3) + "-" +	str.substr(6);
		}
		if (strOut && strExtension) {
			strOut = strOut + ' x' + strExtension;
		}
		if (strOut) {
			input.value = strOut;
		}

	}		
});