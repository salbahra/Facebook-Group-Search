//Set global variables (name describes them)
var killScroll = true;
var pastIntro = false;
//var nextOff = true;
var infoWindow = new google.maps.InfoWindow;
var allMarkers = [];
var justMarkers = [];
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
$(document).on("pageshow", "#start, #about, #mapview", function () {
        var page = '#' + $('.ui-page-active').attr('id');
        $(page + ' div[data-role="controlgroup"] a').button();
        $(page + ' div[data-role="controlgroup"]').controlgroup();
});
$(document).on("pageshow", "#mapview", function () {
	if (!window.map) {
		$.mobile.loading( 'show' );
		set_map();
		initialize();
	} else {
		google.maps.event.trigger(window.map, 'resize');
	}
});
$(document).bind("orientationchange", function(){ 
    set_map();
    google.maps.event.trigger(window.map, 'resize'); 
});
$(document).on("pageshow", "#showpost", function () {
	killScroll = true;
	//Refresh list each time comments or posts are loaded
});
$(document).on('input',"#map-search",function(){
    if (!$(this).val().length) {
        filter($(this).val());
        window.map.setOptions({center: new google.maps.LatLng(39.810646,-98.556976),
            zoom: get_zoom()
        });
    }
    if ($(this).val().length > 1) filter($(this).val());
});
$("#mapview").on('click', '.ui-input-clear', function(e){
    filter("");
    window.map.setOptions({center: new google.maps.LatLng(39.810646,-98.556976),
        zoom: get_zoom()
    });
});
function set_map() {
	$("#map_canvas").height($(window).height() - ($('[data-role=header]').last().height() + $('[data-role=footer]').last().height()));
	$("#map_canvas").width($(window).width());
}
function link(query) {
    $("#search").val(query);
    get();
}
//Random news
function new_item() {
    var news = [
        "Try the <a href=\"#mapview\">updated map view</a>!",
        "Advanced: Search by user, hospital, city and state ex: <a href=\"javascript:link('elective hospital:(union)');\">elective hospital:(union)</a>"
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
                pages = ["#about","#start","#mapview"];
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

function get_zoom() {
	if ((navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1)) {
		return 3;
	} else {
		return 4;
	}
};
function initialize() {
    var styles = [
        {
            featureType: 'road',
            elementType: 'geometry',
            stylers: [{'visibility': 'simplified'}]
        }, {
            featureType: 'road.arterial',
            stylers: [
            {hue: 149},
            {saturation: -78},
            {lightness: 0}
            ]
        }, {
            featureType: 'road.highway',
            stylers: [
            {hue: -31},
            {saturation: -40},
            {lightness: 2.8}
            ]
        }, {
            featureType: 'poi',
            elementType: 'label',
            stylers: [{'visibility': 'off'}]
        }, {
            featureType: 'landscape',
            stylers: [
            {hue: 163},
            {saturation: -26},
            {lightness: -1.1}
            ]
        }, {
            featureType: 'transit',
            stylers: [{'visibility': 'off'}]
        }, {
            featureType: 'water',
            stylers: [
            {hue: 3},
            {saturation: -24.24},
            {lightness: -38.57}
            ]
        }
    ];
    var myOptions = {
            center: new google.maps.LatLng(39.810646,-98.556976),
            zoom: get_zoom(),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: styles
    };
    //Intialize the map
    var map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
    //Global the map
    window.map = map;
    //If the map is clicked on then close any infowindows that might be open
    google.maps.event.addListener(map, 'click', function(){
            infoWindow.close();
    });
    //Get the marker data from server and show it on the map
    $.getJSON("/map/data.php",function(data){
        var coordinates_hash = new Array();
        var coordinates_str, actual_lat, actual_lon, adjusted_lat, adjusted_lon;
            $.each(data,function(i,l){
                actual_lat = adjusted_lat = l.lat;
                actual_lon = adjusted_lon = l.lng;
                coordinates_str = actual_lat + actual_lon;
                while (coordinates_hash[coordinates_str] != null) {
                    // adjust coord by 50m or so
                    adjusted_lat = parseFloat(actual_lat) + (Math.random() -.5) / 750;
                    adjusted_lon = parseFloat(actual_lon) + (Math.random() -.5) / 750;
                    coordinates_str = String(adjusted_lat) + String(adjusted_lon);
                }
                coordinates_hash[coordinates_str] = 1;
                l.lat = adjusted_lat;
                l.lng = adjusted_lon;
                createMarker(map,l);
            });
            newCluster();
    });
    //When the map is idle remove the loading message (since map is loaded via AJAX need to bind)
    google.maps.event.addListener(map, 'idle', function() {
            $.mobile.loading( 'hide' );
    });
};
function newCluster() {
    if (typeof window.mc != 'undefined') {
        window.mc.clearMarkers();
    }
    var mc = new MarkerClusterer(window.map, justMarkers, {
        maxZoom: 10
    });
    //Save handle to map globally
    window.mc = mc;
}
function createMarker(map,data) {
	var marker = new google.maps.Marker({
		position: new google.maps.LatLng(data.lat,data.lng),
		map: map
	});
        allMarkers.push({ point: marker, content: data.message});
        justMarkers.push(marker);
	//If a marker is clicked show the appropriate infowindow
	google.maps.event.addListener(marker, 'click', function() {
		infoWindow.close();
		_gaq.push(['_trackEvent', 'Markers', 'Open', data.id]);
		html = createInfoWindow(data);
		infoWindow = new google.maps.InfoWindow({content: html});
		infoWindow.open(map, marker);
	});
};
function createInfoWindow(data) {
	//Return the appropriate contents to show inside the infowindow
	return "<p>" + data.message + "<br><br>" +
		"<span>Original post: <a data-transition='slideup' data-rel='dialog' href='mobile.php?action=showpost&id=" + data.id + "'>Here</a></span><br>" +
		"<span>Created On: " + data.time +
		"</span></p>";
};
function filter(needle) {
    var bounds = new google.maps.LatLngBounds ();
    var pointCount = 0;
    var map = window.map;
    justMarkers = [];
    
    if (allMarkers) {
        for (var i = 0, marker; marker = allMarkers[++i]; ) {
            if(marker.content.toLowerCase().indexOf(needle.toLowerCase()) >= 0){
                marker.point.setVisible(true);
                bounds.extend(marker.point.position);
                justMarkers.push(marker.point);
                pointCount++;
            }else{
                marker.point.setVisible(false);
            }
        }
        if (pointCount > 1) {
            map.fitBounds(bounds);
            var listener = google.maps.event.addListener(map, "idle", function() { 
                if (map.getZoom() > 12) map.setZoom(12); 
                google.maps.event.removeListener(listener); 
            });
        }
        else if (pointCount == 1) {
            map.setCenter(bounds.getCenter());
            map.setZoom(12);
        } 
        newCluster();
    }
}