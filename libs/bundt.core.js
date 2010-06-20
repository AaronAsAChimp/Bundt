$(function () {
	const service_url = "/fontservice/";
	var font_name = "UbuntuTitle";
	var font_variant = "UbuntuTitleBold";
	var glyph = "exclam";
	
	Raphael.fn.editable = function (path) {
		var canvas = this;
		
		var x_accum = 0;
		var y_accum = 0;
		
		var last_control_x = 0;
		var last_control_y = 0;
		
		$.each(Raphael.parsePathString(path), function () {
			console.log(this);
			
			switch(this[0]) {
				case "q":
					canvas.circle(x_accum + this[1], y_accum + this[2], 5);
					canvas.circle(x_accum += this[3], y_accum += this[4], 5);
					break;
				case "v":
					canvas.circle(x_accum, y_accum += this[1], 5);
					break;
				case "t":
					canvas.circle(x_accum += this[1], y_accum += this[2], 5);
					break;
				case "M":
					canvas.circle(x_accum = this[1], y_accum = this[2], 5);
					break;
			}
		});
		
		canvas.path(path);
	}
	
	var canvas = Raphael("canvas", 1000, 1000);
	
	$.get(service_url + font_name + ".svg/" + font_variant + "/" + glyph, function(data) {
		canvas.editable($("glyph[glyph-name=" + glyph + "]", data).attr("d"));
	});
})
