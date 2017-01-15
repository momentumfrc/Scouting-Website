
$("#searchli").click(function() {
	$("#hamburgermenu").slideUp(100);
	$("#container").css('top','130px');
	$("#TeamSearch").show(0, function() {
		if($("#TeamSearch").is(':visible')) {
			window.clearInterval(loop);
			filter.enabled = true;
		} else {
			filter.enabled = false;
			loop = window.setInterval(get(),5000);
	}});
});

$("#TeamSearchbox > input").on('input', function() {
	if($("#TeamSearchbox > input").val() === '')
		clearFilters();
	else
		filter.enabled = true;
		filter.Team = $("#TeamSearchbox > input").val();
	get();
});

$("#closesearchbar").click(function(){
	$("#TeamSearch").hide(0);
	$("#TeamSearchbox > input").val(''); //empty contents of searchbar
	$("#container").css('top','80px');
	clearFilters();
});