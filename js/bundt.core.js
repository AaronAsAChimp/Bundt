$(function () {
	const service_url = "/fontservice/";
	var font_name = "UbuntuTitle";
	var font_variant = "UbuntuTitleBold";
	var font_data = null;
	var glyph = "one";
	
	var canvas = null;

	function is_upper(character) {
		return !(character.charCodeAt(0) & 0x20);
	}
	
	Raphael.fn.editable = function (path) {
		var canvas = this;
		var accum = {x:0, y:0};
		canvas.editable_path = Raphael.parsePathString(path);
		
		canvas.static_path = canvas.path(canvas.editable_path).attr({
			fill: "#000",
			stroke: "none"
		});
		
		var controls = {
			"m":{
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
			"l": {
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
			"h":{
				renderer: standard_control,
				drag: change_to_l_drag,
				mousedown: h_mousedown,
			},
			"v":{
				renderer: standard_control,
				drag: change_to_l_drag,
				mousedown: v_mousedown,
			},
			"c":{
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
			"s":{
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
			"q":{
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
			"t":{
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
			"a":{
				renderer: standard_control,
				drag: standard_drag,
				mousedown: standard_mousedown,
			},
		}
		
		function standard_control (a, idx, ix, iy) {
			var render_command = canvas.editable_path[idx][0].toLowerCase();
			
			canvas.circle(a.x, a.y, 5).attr({
				fill: "#f00",
				stroke: "none"
			}).drag(
				// drag
				function (dx, dy) {
					controls[render_command].drag.apply(this, [dx, dy, idx, ix, iy]);
				},
				// mousedown
				function () {
					controls[render_command].mousedown.apply(this, [idx, ix, iy]);
				}
			)
			
			console.log(a.x, a.y);
			
			if(render_command != "h") { // this is for everything except "h"
				a.y -= canvas.editable_path[idx][canvas.editable_path[idx].length - 1];
			}
			
			if(render_command == "h") { // this is for "h"
				a.x -= canvas.editable_path[idx][canvas.editable_path[idx].length - 1];
			} else if(render_command != "v") { // this is for all others
				a.x -= canvas.editable_path[idx][canvas.editable_path[idx].length - 2];
			}
			
			console.log(a.x, a.y);
			
			for(var point = (canvas.editable_path[idx].length - 3); point > 1; point-=2 ) {
				//console.log(point - 1, point);
				// save a copy of the point index
				var pix = point - 1;
				var piy = point;
				
				canvas.circle(a.x + canvas.editable_path[idx][pix], a.y + canvas.editable_path[idx][piy], 5).attr({
					fill: "#00f",
					stroke: "none"
				}).drag(
					// drag
					function (dx, dy) {
						controls[render_command].drag.apply(this, [dx, dy, idx, pix, piy]);
					},
					// mousedown
					function () {
						controls[render_command].mousedown.apply(this, [idx, pix, piy]);
					}
				)
			}
			
			if(render_command != "h") { // this is for everything except "h"
				a.y += canvas.editable_path[idx][canvas.editable_path[idx].length - 1];
			}
			
			if(render_command == "h") { // this is for "h"
				a.x += canvas.editable_path[idx][canvas.editable_path[idx].length - 1];
			} else if(render_command != "v") { // this is for all others
				a.x += canvas.editable_path[idx][canvas.editable_path[idx].length - 2];
			}
		}
		
		function standard_drag (dx, dy, idx, ix, iy) {
			//console.log("Ahh! Move", canvas.editable_path[idx][ix], canvas.editable_path[idx][iy]);
			this.attr({
				cx: dx + this.sx,
				cy: dy + this.sy
			})
	
			canvas.editable_path[idx][ix] = dx + this.px;
			canvas.editable_path[idx][iy] = dy + this.py;

			// if its not a control point 
			if(iy == (canvas.editable_path[idx].length - 1)) {
				if(canvas.editable_path[idx][0].toLowerCase() == "q") {
					// adjust the control points
				}
				
				// if the next element is not the last 
				// and its relative
				var next = canvas.editable_path[idx+1];		
				if(next[0] != "z" && !is_upper(next[0])){
					// move the next point relative to this one, so it appears like it never moves
					canvas.editable_path[idx+1][next.length - 2] = this.nx - dx;
					canvas.editable_path[idx+1][next.length - 1] = this.ny - dy;
				}
			}
	
			canvas.static_path.attr({path: canvas.editable_path});
		}
		
		function change_to_l_drag (dx, dy, idx, ix, iy) {
			//console.log("Ahh! Move", canvas.editable_path[idx][ix], canvas.editable_path[idx][iy]);
			this.attr({
				cx: dx + this.sx,
				cy: dy + this.sy
			})
	
			console.log(canvas.editable_path[idx][ix], canvas.editable_path[idx][iy]);
			canvas.editable_path[idx][0] = is_upper(canvas.editable_path[idx][0])? "L": "l";
			canvas.editable_path[idx][1] = dx + this.px;
			canvas.editable_path[idx][2] = dy + this.py;
			console.log(canvas.editable_path[idx]);

			// if the next element is not the last 
			// and its relative
			// and its not a control point
			var next = canvas.editable_path[idx+1];		
			if(next[0] != "z" && !is_upper(next[0]) && iy == (canvas.editable_path[idx].length - 1)){
				canvas.editable_path[idx+1][next.length - 2] = this.nx - dx;
				canvas.editable_path[idx+1][next.length - 1] = this.ny - dy;
			}
	
			canvas.static_path.attr({path: canvas.editable_path});
		}
		
		function standard_mousedown(idx, ix, iy) {
			this.sx = this.attr("cx");
			this.sy = this.attr("cy");
	
			this.px = canvas.editable_path[idx][ix];
			this.py = canvas.editable_path[idx][iy];
		
			this.nx = canvas.editable_path[idx+1][canvas.editable_path[idx+1].length - 2];
			this.ny = canvas.editable_path[idx+1][canvas.editable_path[idx+1].length - 1];
		}
		
		function v_mousedown(idx, garbage, iy) {
			this.sx = this.attr("cx");
			this.sy = this.attr("cy");
	
			this.px = 0;
			this.py = canvas.editable_path[idx][iy];
		
			this.nx = canvas.editable_path[idx+1][canvas.editable_path[idx+1].length - 2];
			this.ny = canvas.editable_path[idx+1][canvas.editable_path[idx+1].length - 1];
		}
		
		function h_mousedown(idx, garbage, ix) {
			this.sx = this.attr("cx");
			this.sy = this.attr("cy");
	
			this.px = canvas.editable_path[idx][ix];
			this.py = 0;
		
			this.nx = canvas.editable_path[idx+1][canvas.editable_path[idx+1].length - 2];
			this.ny = canvas.editable_path[idx+1][canvas.editable_path[idx+1].length - 1];
		}		
		
		$.each(canvas.editable_path, function (idx) {
			console.log(this);
			
			// modify the accumulator
			if(this.length > 2) {			
				if(is_upper(this[0])) {
					/* this is an absolutely positioned node */
					accum.x = this[this.length - 2];
					accum.y = this[this.length - 1];
				} else {
					/* this is an relatively positioned node */
					accum.x += this[this.length - 2];
					accum.y += this[this.length - 1];
				}
			} else {
				switch(this[0]) {
					case "v":
						accum.y += this[1];
						break;
					case "V":
						accum.y = this[1];
						break;
					case "h":
						accum.x += this[1];
						break;
					case "H":
						accum.x = this[1];
						break;
				}
			}
			
			// if there is a renderer for this type of control, render it.			
			if(controls[this[0].toLowerCase()]) {
				var ix = this.length - 2;
				var iy = this.length - 1;
				controls[this[0].toLowerCase()].renderer(accum, idx, ix, iy);
			}

		});
		
		return canvas.static_path;
	}
	
	$.get(service_url + font_name + ".svg", function(data) {
	
		$("#font-face").replace_font_face("Bundt-Current", service_url + font_name + ".svg#" + font_variant);
	
		font_data = data;
		
		canvas = Raphael("canvas", 1000, 1000);
		
		// select the font family, the font-face tag is inside the font tag
		var $font = $("font-face[font-family=" + font_variant + "]", data).parent();
		
		// extract the desired glyph
		canvas.editable($("glyph[glyph-name=" + glyph + "]", $font).attr("d"));
	});
	
	$("#canvas").editing_stopped(function () {
		console.log($(font_data).toString());
		
		// select the font family, the font-face tag is inside the font tag
		var $font = $("font-face[font-family=" + font_variant + "]", font_data).parent();
		
		// extract the desired glyph
		$("glyph[glyph-name=" + glyph + "]", $font).attr("d", canvas.editable_path.toString());
		
		if(font_data != null && canvas != null) {
			$.ajax({
				type: "PUT",
				url: service_url + font_name + ".svg",
				data: $(font_data).toString(),
				success: function(response) {
					console.log("Got: " + $(response));
				}
			});
		}
	});
})		
