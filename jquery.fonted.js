function FontedGlif(name, glif) {
	var this_c = this;
	var server_path = "";
	var call_render = false;
	var doc = null;
	var surface = null;
	
	var em = 0;
	
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
	
	function glyph_2_filename(glyph) {
		return glyph + ".glif"
	}
	
	this.render = function(w,h,em_width) {
		if(!surface) {
			surface = document.createElement("canvas");
			surface.width = w;
			surface.height = h;
		}

		var ctx = surface.getContext('2d');
		
		// doc might not have loaded yet, delay rendering
		if(!doc) {
			call_render = true;
			em = em_width;
		} else {
			var fudge = em / 50;
			var scale = (surface.width 
			- fudge) / em;
			//console.log(surface.width);
			ctx.translate(0, surface.height - fudge);
			ctx.transform(1,0,0,-1,0,0);
			ctx.scale(scale, scale);
			//ctx.scale(.2, .2);

			$("contour", doc).each(function () {
				var offcurve_pts = Array();
				var deferred = null;
				var closed = true;
				ctx.globalCompositeOperation = "xor";
				ctx.beginPath();
				$(this).children("point").each(function(itm) {
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
			
			});
			
			/**/$("contour", doc).each(function () {
				$(this).children("point").each(function(itm) {
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
			});
		}
		
		return surface;
	}
	
	this.load = function(name, glif) {
		//console.log("Reticulating Splines");
		
		server_path = name + ".ufo/glyphs/" + glyph_2_filename(glif);
		$.ajax({
			type: "GET",
			dataType: "xml",
			url: server_path,
			success: function(data, text) {
				doc = data;
				if(call_render) {
					this_c.render();
				}
			}
		});
	}
	
	this.load(name, glif);
};

jQuery.fn.fonted = function (font) {
	var glyphs = [
		new FontedGlif(font, "A_"),
		new FontedGlif(font, "germandbls"),
		new FontedGlif(font, "C_"),
		new FontedGlif(font, "D_"),
		new FontedGlif(font, "E_"),
		new FontedGlif(font, "at"),
		new FontedGlif(font, "b"),
		new FontedGlif(font, "copyright"),
		new FontedGlif(font, "d"),
		new FontedGlif(font, "percent")
	];
	return this.each(function(){
		for(var i = 0; i < glyphs.length; i++) {
			$(this).append(glyphs[i].render(1000,1000, 1000));
		}
	});
};
