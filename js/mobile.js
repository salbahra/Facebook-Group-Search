//Set global variables (name describes them)
var killScroll = true;
var pastIntro = false;
//var nextOff = true;
/*
//Detect scrolling towards the bottom of the page (infinity rows)
$(window).scroll(function(){
	//Get the current page
	c = $('.ui-page-active').attr('id');
	//Continue only if current page is post or comment
	if (c == "post" || c == "comment") {
		//If the last element in listview is visible then load the next set of results
		if (isScrolledIntoView($("#" + c + "_results").children(':last'))) getnext();
	}
});
//	if (($(document).height() - ($(window).height())) == 0) getnext();
function isScrolledIntoView(elem) {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = elem.offset().top;
    var elemBottom = elemTop + elem.height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}
*/
function showerror(msg) {
	// show error message
        $.mobile.loading( 'show', {
            text: msg,
            textVisible: true,
            textonly: true,
            theme: 'a'
            });
	// hide after delay
	setTimeout( function(){$.mobile.loading('hide')}, 1500);
};
$(document).on("pageshow", "#start", function () {
	//Reset variables on starter page being loaded
	//killScroll = true; pastIntro = false; //nextOff = true;
        new_item();
});
$(document).on("pageshow", "#start, #about", function () {
        var page = '#' + $('.ui-page-active').attr('id');
        $(page + ' div[data-role="controlgroup"] a').button();
        $(page + ' div[data-role="controlgroup"]').controlgroup();
});
$(document).on("pageshow", "#showpost", function () {
	killScroll = true;
	//Refresh list each time comments or posts are loaded
});
function link(query) {
    $("#search").val(query);
    get();
}
//Random news
function new_item() {
    var news = [
        "Advanced: Search by user ex: <a href=\"javascript:link('funny user:(louis)');\">funny user:(louis)</a>"
    ];
    var i = Math.floor((Math.random()*news.length));
    $("#new").html(news[i]);
}
function get() {
	//Check if query is missing
	if ($("#search").val() == "") {
		showerror("Search Query Required");
		return;
	}
	$("#search").blur();
	//Since this is a new request remove old results, if any
	$("#post_results").empty();
	$.mobile.loading( 'show' );
	//Grabs the values of the inputs, removes any empty values, removes trailing &, and appends the chunk number and search action
	var str = $("#fbsearch").serialize().replace(/[^&]+=\.?(&|$)/g, '').replace(/&$/, '') + "&action=search";
	//Send search query to the server
	$.post("index.php",str,function(results){
		//If no results notify the user
		if ($.isEmptyObject(results[0]) && $.isEmptyObject(results[1])) {
			showerror("No Results Found");
			return;
		}
		//Inject results into the DOM
		inject(results);
                //Show results tab on other pages
                pages = ["#about","#start"];
                $.each(pages,function(a,b){
                    if (!pastIntro) {
                        $(b + ' a[href="#start"]').after('<a data-role="button" data-theme="a" data-transition="fade" href="#results">Results</a>').trigger('create');
                        $(b + ' div[data-role="controlgroup"] a').button();
                        $(b + ' div[data-role="controlgroup"]').controlgroup();
                    }
                });
		//Change globals
		pastIntro = true; //nextOff = false;
		$.mobile.loading('hide');
		//Finally change the page for the user
		$.mobile.changePage("#results", { transition: "fade"} );
                //Refresh list view
                $("#post_results > ul").listview().listview('refresh');
	}, 'json');
};
/*
function getnext(){
	//If getnext is disabled return
	if (nextOff) return;
	//If were past the intro AND scrolling is enabled then continue
	if (pastIntro && !killScroll) {
		//Prevent further triggering until we are done
		killScroll = true;
		$.mobile.showPageLoadingMsg();
		//AJAX request for more rows
		$.post("index.php","next=1&action=search",function(results){
			//If the array is empty then prevent further getnext requests
			if ($.isEmptyObject(results[0]) && $.isEmptyObject(results[1])) {
				$.mobile.hidePageLoadingMsg();
				nextOff = true; killScroll = false;
				return;
			}
			//Inject results into the DOM
			inject(results);
			//Select active page and update the list
			$("#" + $('.ui-page-active').attr('id') + "_results").listview('refresh');
			//Allow scrolling to fetch more rows again
			killScroll = false;
			$.mobile.hidePageLoadingMsg();
		}, 'json');
	}
};
*/
function inject(results) {
        var posts = '<ul data-role="listview" data-divider-theme="a" data-filter="true">';
        if (results[0].length > 0) {
            posts += '<li data-role="list-divider" style="text-align:center">Posts</li>';
            //Iterate through each post/comment and create list element then append it to posts
            $.each(results[0],function(i,l){
                    posts += list_item(l,1);
            });
        }
        if (results[1].length > 0) {
            posts += '<li data-role="list-divider" style="text-align:center">Comments</li>'
            $.each(results[1],function(i,l){
                    posts += list_item(l,0);
            });
        }
	if (posts) $(posts + "</ul>").appendTo("#post_results").trigger('create');
};
function list_item(l,isPost) {
	//Generate the link for popup dialog
	var item = "<li>";
        if (!isPost || l.comments > 0) {
            item += "<a data-transition='fade' data-rel='dialog' href='mobile.php?action=showpost&id=" + l.id + "'><p class='wrap'>" + l.post + "</p></a>";
        } else {
            item += "<p class='wrap'>" + l.post + "</p>";
        }
        if (l.comments > 0) item += "<span class='ui-li-count'>" + l.comments + "</span>";
        if (isPost && l.comment.length) {
            item += "</li><li><div class='ui-grid-solo'>"
            $.each(l.comment, function(x,y){
                item += "<div data-role='button' data-theme='b' data-corners='false' class='ui-block-a'><p class='wrap'>" + y.post + "</p></div>";
            });
            item += "</div></li>";
        } else {
            item += "</li>";
        }
        return item;
};
function submit_feedback(){
    var data = $("#feedbacka").serialize() + "&action=feedback";
    $("#feedbacka").fadeOut("fast",function(){
        showerror("Thank you!");
        $("#feedbacki").val('');
        setTimeout(function(){$("#feedbacka").fadeIn();}, 1500);
    });
    $.post("index.php", data);
};