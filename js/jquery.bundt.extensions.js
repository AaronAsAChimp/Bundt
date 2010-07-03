(function($) {
	$.fn.typing_stopped = function (funk) {
		return this.each(function () {
			var pauselength = 750;
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

})(jQuery)
