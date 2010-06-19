/*
	a transformer thread
	WARNING: this code executes in a parallel environment.
 */
 
/*
	Function: onmessage
	accepts an array of point objects and a affine matrix then transforms them
*/

onmessage = function (data) {
	var geo = data.data.geometry;
	var mat = data.data.matrix;
	
	/*
		1 0 0   x
		0 1 0 * y
		0 0 1   1
	*/
	
	// loop over the points
	for(var i = 0; i < geo.length; i++) {
		geo[i] = {
			"x": (mat[0] * geo[i].x) + (mat[1] * geo[i].y) + (mat[2] * 1),
			"y": (mat[3] * geo[i].x) + (mat[4] * geo[i].y) + (mat[5] * 1) 
		}
	}
	
	postMessage(data.data);
	
	if(typeof(close) == 'function') {
		close();
	}
}
