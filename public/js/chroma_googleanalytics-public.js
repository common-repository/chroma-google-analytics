var options = chroma_googleanalytics_public_vars.options;
var tracking_id = options.settings["chroma_googleanalytics-id"];

// Google analytics
//-----------------

// Log Tracking key if in debug mode
if (chroma_googleanalytics_public_vars.options.debug){
	console.log("Chroma Google analytics - Tracking ID:", tracking_id);
}

// GA Tracking code
var add_tracker = function(){
	(function(i,s,o,g,r,a,m){i.GoogleAnalyticsObject=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', tracking_id, 'auto');
	ga('send', 'pageview');
};

add_tracker();
