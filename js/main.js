//Set global variables (name describes them)
var killScroll = true;
var pastIntro = false;
//var nextOff = true;
//Onload
$(function(){
	update();
        new_item();
	$('#header,#aboutlink,#feedbacklink').fadeIn(1000);
	$("#search").focus();
});
//Detect scrolling towards the bottom of the page (infinity rows)
//$(window).scroll(function(){
//	//If the window is 200 pixels from the bottom then lets get more
//	if  ($(window).scrollTop()+200 >= ($(document).height() - ($(window).height()))){
//		getnext();
//	};
//});
//Keybinds
//$(document).bind("keydown", function(e){if (String.fromCharCode(e.keyCode) == "M") {if (!$(":focus").length) getnext(); }});
//Detect a window resize
$(window).resize(function(){
	update();
	if ($("#about").length) {
		$("#about").css({"margin-top": -($("#about").height() / 2), "margin-left":-($("#about").width() / 2)});
	}
});
if(!String.linkify) {
    String.prototype.linkify = function() {
        // http://, https://, ftp://
        var urlPattern = /\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;
        // www. sans http:// or https://
        var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
        // Email addresses
        var emailAddressPattern = /\w+@[a-zA-Z_]+?(?:\.[a-zA-Z]{2,6})+/gim;
        return this
            .replace(urlPattern, '<a target="_blank" href="$&">$&</a>')
            .replace(pseudoUrlPattern, '$1<a target="_blank" href="http://$2">$2</a>')
            .replace(emailAddressPattern, '<a target="_blank" href="mailto:$&">$&</a>');
    };
}
//Removes border from first element on each row of meta info on the results
function chkbor() {
	var top = 0;
	$(".nobr").each(function(i){
		if (top < $(this).offset().top) {
			top = $(this).offset().top;
			$(this).css("border",0);
		} else {
			$(this).css("border-left","dotted 1px gray");
		};
	});
}
//Random news
function new_item() {
    var news = [
        "Advanced: Search by user ex: <a href=\"javascript:link('funny user:(louis)');\">funny user:(louis)</a>"
    ];
    var i = Math.floor((Math.random()*news.length));
    $("#new").html(news[i]);
}
//javascript link to query
function link(query) {
    $("#search").val(query);
    get();
}
//Fade error message in and out
function fadeerror() {
	//Set the height of the parent div so we do not expand it
	$("#error").parent().css("height",$("#error").parent().height());
	$("#error").fadeIn("fast",function(){
		//Delay for 1000 then fadeout
		$(this).delay(1000).fadeOut("fast",function(){
			//Return the height to auto
			$("#error").parent().height("auto");
			//Remove error DIV
			$(this).remove();
			$("#search").focus();
		});
	});
}
//Submits query
function get() {
	//Blur the input to hide the keyboard
	$("#search").blur(); $("#exclude").blur(); $("#group").blur();
	//Check if query is missing
	if ($("#search").val() == "") {
		$("#fbsearch").after("<div id=\"error\">Query Required</div>");
		fadeerror();
		return;
	}
	//Grabs the values of the inputs, removes any empty values, removes trailing &, and appends the chunk number and search action
	var str = $("#fbsearch").serialize().replace(/[^&]+=\.?(&|$)/g, '').replace(/&$/, '') + "&action=search";
	//Hide the canvas
	$("#content").fadeOut("fast");
	$("#header").fadeOut("fast",function(){
		$(this).css({"position":"relative","top":"20px", "margin":"0 0 0 " + -$(this).width() / 2 + "px", "opacity":"0.95"});
	});
	//Show the loading
	$("#loading").fadeIn("fast");
	//window.history.pushState({"s":$("#search").val()}, $("#search").val() + ' | Rossie Rotation Advice', '/fb/index.php?'+str);
	//Empty results table since this is a new query
	$("#content").html("<h2>Results</h2>");
	//Request the rows
	$.post("index.php",str,function(results){
		//Check for no results
		if ($.isEmptyObject(results[0]) && $.isEmptyObject(results[1])) {
			pastIntro = false;
			$("#header").stop(true,true).css({"position":"absolute","top":"50%"});
			$("#header").fadeIn(800);
			$("#loading").fadeOut(100, function(){update();});
			$("#fbsearch").after("<div id=\"error\">No results found</div>"); fadeerror();
			return;
		}
		if ($.isEmptyObject(results[0])) { $("#content").empty().hide(); }
		inject(results);
		fixbg_ie7();
		//Fade canvas back in
		$("#loading").fadeOut("fast");
		$("#content").fadeIn("fast");
		$("#header").fadeIn("fast");
		chkbor();
		//Change global variables to enable infinity scrolling and header automatic resizing
		killScroll = false;
		pastIntro = true;
//		nextOff = false;
	}, 'json');
};
/*
function getnext(){
	//If getnext is disabled return
	if (nextOff) return;
	//If were past the intro AND scrolling is enabled then continue
	if (pastIntro == true && killScroll == false) {
		//Prevent further triggering until we are done
		killScroll = true;
		//Fade the loading screen in
		$("#loading").fadeIn();
		//AJAX request for more rows
		_gaq.push(['_trackPageview', '/fb/?next=1&action=search']);
		$.post("index.php","next=1&action=search",function(results){
			//If the array is empty then prevent further getnext requests
			if ($.isEmptyObject(results[0]) && $.isEmptyObject(results[1])) { $("#loading").fadeOut("fast"); nextOff = true; killScroll = false; return; }
			inject(results);
			$("#loading").fadeOut("fast");
			chkbor("posts"); chkbor("comments");
			//Allow scrolling to fetch more rows again
			killScroll = false;
		}, 'json');
	}
};
*/

//Lots of hardcoded group numbers that need to be fixed...
function inject(results) {
	//Cycle through each new row
	var posts = '';
	$.each(results[0],function(i,l){
		posts += "<div class='posts'>" + l['post'].linkify() + "<br><br>" +
		"<span class='nobr'>Original post: <a target=\"_blank\" href=\"https://www.facebook.com/groups/grpid/permalink/" + l['id'] + "\">Here</a></span> " +
		"<span class='nobr'>Number of comments: " + l['comments'] + "</span> " +
		"<span class='nobr'>Likes: " + l['likes'] + "</span> " +
		"<span class='nobr'>Posted By: <a target=\"_blank\" href=\"https://www.facebook.com/" + l['user'] + "\">" + l['name'] + "</a></span> " +
		"<span class='nobr'>Created On: " + l['time'] +
		"</span></div><br>"
                if (!$.isEmptyObject(l['comment'])) {
                    posts += "<div class=\"comments\">"
                    $.each(l['comment'],function(a,b){
                        posts += "<div>" + b['post'].linkify() + "<br><br>" +
                        "<span class='nobr'>Original post: <a target=\"_blank\" href=\"https://www.facebook.com/groups/grpid/permalink/" + b['id'] + "/?comment_id=" + b['comment_id'] + "\">Here</a></span> " +
                        "<span class='nobr'>Likes: " + b['likes'] + "</span> " +
                        "<span class='nobr'>Posted By: <a target=\"_blank\" href=\"https://www.facebook.com/" + b['user'] + "\">" + b['name'] + "</a></span> " +
                        "<span class='nobr'>Created On: " + b['time'] +
                        "</span></div><br>"
                    });
                    posts += "</div>"
                }
	});
        if (!$.isEmptyObject(results[1])) {
                posts += "<h2>Comments</h2>"
                $.each(results[1],function(a,b){
                    posts += "<div class=\"comments2\">" + b['post'].linkify() + "<br><br>" +
		    "<span class='nobr'>Original post: <a target=\"_blank\" href=\"https://www.facebook.com/groups/grpid/permalink/" + b['id'] + "/?comment_id=" + b['comment_id'] + "\">Here</a></span> " +
                    "<span class='nobr'>Likes: " + b['likes'] + "</span> " +
                    "<span class='nobr'>Posted By: <a target=\"_blank\" href=\"https://www.facebook.com/" + b['user'] + "\">" + b['name'] + "</a></span> " +
                    "<span class='nobr'>Created On: " + b['time'] +
                    "</span></div><br>"
                });            
        }
	$("#content").append(posts);
}
function update() {
	if ($("#loading").is(":visible")) { return; }
	if (pastIntro) {
		fixbg_ie7();
		$("#header").css({"margin":"0 0 0 " + -$("#header").width() / 2 + "px"});
		chkbor();
	} else {
		$("#header").css({"margin-top":-$("#header").height() / 2 + "px","margin-left":-$("#header").width() / 2 + "px"});
	}
};
function about(){
	_gaq.push(['_trackPageview', '/fb/about.php']);
	$("#loading").fadeIn("fast");
	$.get("about.php",function(html){
		$("#header").before("<div id=\"about\" class=\"overlay\">" + html + "</div>");
		$("#loading").fadeOut("fast");
		$("#about").fadeIn("slow").bind("click touchstart", function(){ event.stopPropagation(); });
		$("#about").css({"margin-top": -($("#about").height() / 2), "margin-left":-($("#about").width() / 2)});
		$(document).one("click keydown touchstart", function() {
			$("#about").fadeOut("slow",function(){
				if (!pastIntro) $("#search").focus();
				$(this).remove();
			});
		});
	});
};
function feedback(){
	_gaq.push(['_trackPageview', '/fb/feedback.php']);
	$("#loading").fadeIn("fast");
	$.get("feedback.php",function(html){
		$("#header").before("<div id=\"feedback\" class=\"overlay\">" + html + "</div>");
		$("#loading").fadeOut("fast");
		$("#feedback").fadeIn("slow").bind("click touchstart", function(){ event.stopPropagation(); });
		$("#feedback").css({"margin-top": -($("#feedback").height() / 2), "margin-left":-($("#feedback").width() / 2)});
		$(document).one("keydown", function(e) {
                        if (e.keyCode == 27) {
                            $("#feedback").fadeOut("slow",function(){
                                if (!pastIntro) $("#search").focus();
				$(this).remove();
                            });
                        }
		});
	});
};
function submit_feedback(){
    var data = $("#submit_feedback").serialize() + "&action=feedback";
    $("#feedback").fadeOut("slow",function(){
        if (!pastIntro) $("#search").focus();
	$(this).remove();
    });
    $.post("index.php", data);
}
function fixbg_ie7() {
	if ($.browser.msie  && parseInt($.browser.version, 10) === 7) { $("#background").css({"margin-left":$("#header").width() / 2 + "px"}); }
};
/*
window.onpopstate = function(e){
	console.log(e.state);
  if (e.state == null) {
  	window.history.pushState('', 'Rossie Rotation Advice', '/fb/index.php');
	return;
  }
  if (e.state.s == null) {
	$("#search").val('');
	pastIntro = false;
	$("#header").css({"position":"absolute","top":"50%"});
	update();
	$("#content").hide();
	$("#search").focus();
	return;
  }
  $("#search").val(event.state.s); get();
};  
*/