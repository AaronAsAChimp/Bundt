(function($) {
	var pauselength = 750;
	
	$.fn.typing_stopped = function (funk) {
		return this.each(function () {
			var timer = null;
			var ele = this;

			$(this).keydown(function () {
				if(timer != null) {
					clearTimeout(timer);
				}
				
				timer = setTimeout(function() {
					funk.call(ele);
				}, pauselength);
			});
			
			$(this).blur(function () {
				if(timer != null) {
					clearTimeout(timer);
				}
				
				funk.call(ele);
			});
		});
	};
	
	$.fn.editing_stopped = function (funk) {
		var timer = null;
		
		return this.click(function () {
			if(timer != null) {
				clearTimeout(timer);
			}
			
			timer = setTimeout(function() {
				funk.call(this);
			}, pauselength);
		});
	};
	
	$.fn.toString = function () {
		var out = '';
		if (typeof XMLSerializer == 'function') {
		    var xs = new XMLSerializer();
		    this.each(function() {
		        out += xs.serializeToString(this);
		    });
		} else if (this[0] && this[0].xml != 'undefined') {
		    this.each(function() {
		        out += this.xml;
		    });
		}
		return out;
	};
	
	$.fn.replace_font_face = function (font_family, src) {
		return this.each(function() {
			$(this).text("@font-face {\n\t" +
				"font-family: \"" + font_family + "\";\n\t" +
				"src: url(\"" + src + "\") format (\"svg\");\n" + 
			"}");
		});
	}

})(jQuery)
