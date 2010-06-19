function FontedGlif(name, glif, surface) {
	var this_c = this;
	var server_path = "";
	var queue_draw = false;
	var draw_queue = {};
	
	var em = 0;
	
	var renderers = {
		"contour": function (item, ctx) {
			var offcurve_pts = Array();
			var deferred = null;
			var closed = true;
			ctx.globalCompositeOperation = "xor";
			ctx.beginPath();
			$(item).children("point").each(function(itm) {
				var sx = $(this).attr("x");
				var sy = $(this).attr("y");
				switch($(this).attr("type")) {
					case "move":
						ctx.moveTo(sx, sy);
						closed = false;
						break;
					case "line":
						ctx.lineTo(sx, sy);
						break;
					case "curve":
						if(offcurve_pts.length == 0 && itm == 0 && closed) {
							deferred = mkpt(sx,sy);
						}
						draw_curve(ctx, offcurve_pts, mkpt(sx,sy));
						offcurve_pts = Array();
						break;
					case "qcurve":
						ctx.quadraticCurveTo(offcurve_pts[0].x,offcurve_pts[0].y, sx, sy);
						offcurve_pts = Array();
						break;
					case "offcurve":
					default:
						offcurve_pts.push(mkpt(sx,sy));
						break;
				}
				
				
			});
			
			//console.debug(deferred);
			if(deferred) {
				draw_curve(ctx, offcurve_pts, deferred)
			}
			ctx.fill();
			//ctx.strokeStyle = "#ffcc00";
			//ctx.lineWidth = 3;
			//ctx.stroke();
		},
		
		"contour-dbg": function (item, ctx) {
			$(item).children("point").each(function(itm) {
				var sx = $(this).attr("x");
				var sy = $(this).attr("y");
				var type = $(this).attr("type");
				var smooth = $(this).attr("smooth");
				ctx.save();
				ctx.globalCompositeOperation = "source-over";
				ctx.beginPath();
				//console.log(type);
				switch(type) {
					case "line":
					case "curve":
					case "qcurve":
						ctx.fillStyle = "#00ff00";
						//ctx.arc(sx,sy,5, 0, 2 * Math.PI, false);
						break;
					default:
						ctx.fillStyle = "#ff0000";
				}
				
				if(!itm) {
					ctx.fillStyle = "#0000ff";
				}
				ctx.arc(sx,sy,5, 0, 2 * Math.PI, false);
				ctx.fill();
				ctx.restore();
			});
		},
		
		"component": function (item, ctx) {
			var base = $(item).attr("base");
			var xscale = ($(item).attr("xScale"))? $(item).attr("xScale") : 1 ;
			var xyscale = ($(item).attr("xyScale"))? $(item).attr("xyScale") : 0 ;
			var yxscale = ($(item).attr("yxScale"))? $(item).attr("yxScale") : 0 ;
			var yscale = ($(item).attr("yScale"))? $(item).attr("yScale") : 1 ;
			var xoffset = ($(item).attr("xOffset"))? $(item).attr("xOffset") : 0 ;
			var yoffset = ($(item).attr("yOffset"))? $(item).attr("yOffset") : 0 ;
			
			var mat = [xscale, xyscale, yxscale, yscale, xoffset, yoffset];
			
			queue_draw = true;
			draw_queue[base] = {matrix: mat, doc: null}; 
			this_c.load(base);
		},
		
		"component-dbg": function (item, ctx) {
			var base = $(item).attr("base");
			var xscale = ($(item).attr("xScale"))? $(item).attr("xScale") : 1 ;
			var xyscale = ($(item).attr("xyScale"))? $(item).attr("xyScale") : 0 ;
			var yxscale = ($(item).attr("yxScale"))? $(item).attr("yxScale") : 0 ;
			var yscale = ($(item).attr("yScale"))? $(item).attr("yScale") : 1 ;
			var xoffset = ($(item).attr("xOffset"))? $(item).attr("xOffset") : 0 ;
			var yoffset = ($(item).attr("yOffset"))? $(item).attr("yOffset") : 0 ;
			
			ctx.save();
				ctx.transform(xscale, xyscale, yxscale, yscale, xoffset, yoffset);
				// x offset
				ctx.strokeStyle = "#00F";
			
				ctx.beginPath();
				ctx.moveTo( -5, 0.5);
				ctx.lineTo(100, 0.5);
				ctx.stroke();
			
				// y offset
				ctx.strokeStyle = "#F00";
			
				ctx.beginPath();
				ctx.moveTo(0.5, -5);
				ctx.lineTo(0.5,  100);
				ctx.stroke();
				
				// y offset
				ctx.strokeStyle = "#0F0";
			
				ctx.beginPath();
				ctx.moveTo(-5, -5);
				ctx.lineTo(100,  100);
				ctx.stroke();
			
			ctx.restore();
			
			console.log(base);
			console.log([xscale, xyscale, xoffset]);
			console.log([yxscale, yscale, yoffset]);
		}
	}
	
	function draw_curve(ctx, offcurve_pts, pt) {
		if(offcurve_pts.length == 0) {
			ctx.lineTo(pt.x, pt.y);
		} else if (offcurve_pts.length == 1) {
			ctx.quadraticCurveTo(offcurve_pts[0].x,offcurve_pts[0].y, pt.x, pt.y);
		} else if (offcurve_pts.length == 2) {
			ctx.bezierCurveTo(offcurve_pts[0].x,offcurve_pts[0].y,offcurve_pts[1].x,offcurve_pts[1].y, pt.x, pt.y);
		} else {
			//Super Bezier Algorithm????
		}
		//console.log(offcurve_pts);
		return null;
	}
	
	function mkpt(sx,sy) {
		return {x:sx, y:sy}
	}
	
	this.glyph_2_filename =  function(glyph) {
		var parts = glyph.split(".");
		var out = "";
		
		if(parts[0].length == 0) { 
			out = "_";
			parts.shift();
		} 
		
		if(parts[0].indexOf("_") >= 0) {
			var complex = parts[0].split("_");
			for( var i = 0; i < complex.length; i++) {
				if(!(complex[i].charCodeAt(0) & 0x20)) {
					complex[i] += "_";
				}
			}
			
			parts[0] = complex.join("_");
		} else {
			if(!(parts[0].charCodeAt(0) & 0x20)) {
				parts[0] += "_";
			}
		}
		
		out += parts.join(".");
		
		console.log(out, parts);
		return out + ".glif"
	},
	
	this.render = function(doc, mat, em_width) {

		var ctx = surface.getContext('2d');
		

		em = em_width;

		var fudge = em / 50;
		var scale = (surface.width 
		- fudge) / em;

		ctx.translate(0, surface.height - fudge);
		ctx.transform(1,0,0,-1,0,0);
		ctx.scale(scale, scale);
		
		
		$("outline", doc).children().each(function () {
	
			renderers[this.tagName](this, ctx);

		});

		/*$("outline", doc).children().each(function () {
	
			renderers[this.tagName + "-dbg"](this, ctx);

		});*/
		
		console.log(queue_draw, draw_queue);
			
	},

	
	this.load = function(glif) {
		//console.log("Reticulating Splines");
		
		server_path = "fontservice/" + name + ".ufo/" + glif;
		
		$.ajax({
			type: "GET",
			dataType: "xml",
			url: server_path,
			success: function(data, text) {
				if(queue_draw) {
					draw_queue[glif].doc = data;
				} else {
					this_c.render(data, null, 1000);
				}
			},
			error: function(xhr, status, error){
				console.log(xhr, status, error);
			}
		});
	}
	
	this.load(glif);
};

jQuery.fn.fonted = function (font) {
	var glyphs = [
		//"F_A_B",
		//"testglyph1",
		"F",
		"A",
		"B",
		"Zcaron",
		"Agrave"
	];
	
	return this.each(function(){
		for(var i = 0; i < glyphs.length; i++) {
			var surface = document.createElement("canvas");
			surface.width = 1000;
			surface.height = 1000;
			
			var let = new FontedGlif(font, glyphs[i],surface);
			
			$(this).append(surface);
		}
	});
};
