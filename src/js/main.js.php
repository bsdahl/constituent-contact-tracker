// Initialize the datetimepicker
$(function () {
    $('#datetimepicker').datetimepicker({
        viewMode: 'years',
        format: 'YYYY-MM-DD'
    });
});

// Setup comment_textarea counter
$(document).ready(function() {
    var text_max = 256;
    //$('#comment_feedback').html(text_max + ' characters remaining');

    $('#comment_textarea').keyup(function() {
        var text_length = $('#comment_textarea').val().length;
        var text_remaining = text_max - text_length;

        if (text_remaining < 64 ) {
          $('#comment_feedback').html(text_remaining + ' characters remaining');
        } else {
          $('#comment_feedback').html('');
        }
    });
});

// Form Ajax
$('#addVisit').submit(function(e) {
	var data = $("#addVisit").serialize();
	$.post("ajax.php", data,
    
    function(data, status){
        $("#formSucess").html( data );
        $(".alert-dismissible").delay(2000).fadeOut(2000);
        markerclick($("#addVisit").find('input[name="LocationID"]').val());
    });

    e.preventDefault();
    this.reset();


 });

// Set fluid height of Map on load
$(window).resize(function () {
    var h = $(window).height(),
        offsetTop = 60; // Calculate the top offset

    $('#map').css('height', (h));
}).resize();

// Function for when the marker is clicked
function markerclick(locationID) {
	$("input[id=LocationID]").val(locationID);

	markerarray[locationID].setZIndex(4);
	$.post("ajax.php",
    {
        action: "getChurchInfo",
        locationID: locationID
    },
    function(data, status){
        $("#info").html( data );
        $('.record').hover(
			function () {
			    $(this).find('.glyphicon-remove-circle').show();
			},
			function () {
			    $(this).find('.glyphicon-remove-circle').hide();
			});
        $('.glyphicon-remove-circle').click(function () {
        	var id = $(this).attr("id");
        	var parent = $(this).parent().parent();
        	$.post("ajax.php",
        	{
        		action: "deleteVisit",
        		visitID: id
        	},
        	function(data, status) {
        		parent.fadeOut('slow', function() {$(this).remove();});
        	})
        })
        $(function () {
				$('[data-toggle="tooltip"]').tooltip()
		})
		$('#editForm').submit(function(e) {
			var data = $("#editForm").serialize()
			$.post("ajax.php", data,
		   
		   function(data, status){
		       $("#editFormSucess").html( data );
		       $(".alert-dismissible").delay(2000).fadeOut(2000);
		       $("#closeEditForm").click(function() {
		       	setTimeout(function(){ 
		       		markerclick($("#editForm").find('input[name="LocationID"]').val()); }
		       		, 1000);
		       	
		       })
		   });		
		   e.preventDefault();		
		   this.reset();		
		});
    });
    if ($("#info").is(":hidden")) {
		$("#info").toggle();
	}
	if ($("#home").is(":visible")) {
		$("#home").toggle();
	}
	if ($("#addDiv").is(":hidden"))
    	$("#addDiv").toggle();

	return;
}

// Click marker when selecting recent contact
$(".record").click(function () {
	var id = $(this).attr("id");
	map.panTo(markerarray[id].getPosition())
	new google.maps.event.trigger( markerarray[id], 'click' );
});

// Array of Locations
var locations = [
	<?php
		if ($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		    	$info = '<p style="font-size:1.2em;"><strong>'.htmlspecialchars($row["ChurchName"], ENT_QUOTES) . '</strong><br>' . $row["Address"] . ' ' . $row["Address2"] . '<br>' . $row["City"] . ', ' . $row["State"] . ' ' . $row["Zip"] .'<br>'.'</p>';
		    	echo '['. $row["LocationID"] .', \''. $info .'\', '.$row["Latitude"].', '.$row["Longitude"].', \''.$row["MaxVisitDate"].'\'],';
		    }
		}

	?>
];

// Setup Map
var map = new google.maps.Map(document.getElementById('map'), {
     zoom: 4,
     center: new google.maps.LatLng(39.0902,-95.7129),
     mapTypeId: google.maps.MapTypeId.ROADMAP
});

// Setup Legend
map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById('legend'));
$('#closeLegend').click(function(){
       $('#legend').toggle();
	  });

// Create the search box and link it to the UI element.
	var input = document.getElementById('pac-input');
	var searchBox = new google.maps.places.SearchBox(input);
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

// Bias the SearchBox results towards current map's viewport.
map.addListener('bounds_changed', function() {
	searchBox.setBounds(map.getBounds());
});

var markers = [];

// Listen for the event fired when the user selects a prediction and retrieve
// more details for that place.
searchBox.addListener('places_changed', function() {
  var places = searchBox.getPlaces();

  if (places.length == 0) {
    return;
  }

  // Clear out the old markers.
  markers.forEach(function(marker) {
    marker.setMap(null);
  });
  markers = [];

  // For each place, get the icon, name and location.
  var bounds = new google.maps.LatLngBounds();
  places.forEach(function(place) {
    var icon = {
      url: place.icon,
      size: new google.maps.Size(71, 71),
      origin: new google.maps.Point(0, 0),
      anchor: new google.maps.Point(17, 34),
      scaledSize: new google.maps.Size(25, 25)
    };

    // Create a marker for each place.
    /*markers.push(new google.maps.Marker({
      map: map,
      icon: icon,
      title: place.name,
      position: place.geometry.location
    }));*/

    if (place.geometry.viewport) {
      // Only geocodes have viewport.
      bounds.union(place.geometry.viewport);
    } else {
      bounds.extend(place.geometry.location);
    }
  });
  map.fitBounds(bounds);
});


// Setup Markers
var infowindow = new google.maps.InfoWindow;

var marker, i;
var markerarray = [];
for (i = 0; i < locations.length; i++) {  
	// Calculate Date & set image
	if (locations[i][4] != '') {
		var s = locations[i][4];
		var t = s.split(/[- :]/);
		var d = new Date(t[0], t[1]-1, t[2]);
		var oneyear = new Date();
		oneyear.setYear(oneyear.getFullYear() - 1);
		var twoyear = new Date();
		twoyear.setYear(twoyear.getFullYear() - 2);
		if (d > oneyear) {
			var image = 'church-1.png';
			var zindex = 3;
		} else if (d > twoyear) {
			var image = 'church-2.png'
			var zindex = 2;
		} else {
			var image = 'church-3.png';
			var zindex = 1;
		}

	} else {
		var image = 'church-4.png';
		var zindex = 0;
	}

	// Create the Marker
    marker = new google.maps.Marker({
         position: new google.maps.LatLng(locations[i][2], locations[i][3]),
         map: map,
         icon: image,
         zIndex: zindex,
         id: locations[i][0]
    });
	
	// Create click listener
    google.maps.event.addListener(marker, 'click', (function(marker, i) {
         return function() {
             infowindow.setContent(locations[i][1]);
             infowindow.open(map, marker);

             markerclick(locations[i][0]);
         }
    })(marker, i));

    markerarray[locations[i][0]] = marker;
}

// Create close infowindow listener
google.maps.event.addListener(infowindow,'closeclick', (function() {
		/*$.post("ajax.php",
    {
        action: "getRecentContacts"
    },
    function(data, status){
        $("#info").html( data );
        if ($("#addDiv").is(":visible"))
    	$("#addDiv").toggle();
    });*/
    if ($("#info").is(":visible")) {
		$("#info").toggle();
	}
	if ($("#home").is(":hidden")) {
		$("#home").toggle();
	}
	if ($("#addDiv").is(":visible")) {
		$("#addDiv").toggle();
	}
}));