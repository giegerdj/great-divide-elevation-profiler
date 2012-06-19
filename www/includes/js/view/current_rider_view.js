
// I am a view helper for the Contact List. I bind the appropriate 
// event handlers and translate the UI events into actions within 
// the application.

// Add view to the application.
window.application.addView((function( $, application ){

	// I am the elevation map view class
	function CurrentRiderView(){
		
		this.map = null;
		this.riderMarker = new Array();
		this.riderTrail = new Array();
		
		this.markerDataStage = null;
		
		this.elevationStatsOut = null;
		this.elevationImage = null;
		this.single = null;
		this.riderNav = null;
		this.noRider = null;
		
	};

	
	// I initialize the view. I get called once the application starts
	// running (or when the view is registered - if the application is 
	// already running). At that point, the DOM is available and all the other 
	// model and view classes will have been added to the system.
	CurrentRiderView.prototype.init = function(){
		this.single = false;
		var self = this;

		this.markerDataStage = $( "#marker-data-stage" );
		this.elevationStatsOut = $( "#elevation-stats" );
		this.elevationImage = $( "#elevation-profile-img" );
		this.riderNav = $( "#riders-nav" );
		
		
		var baseIcon = new GIcon(G_DEFAULT_ICON);
		baseIcon.iconSize = new GSize(20, 34);
		
		var StartIcon = new GIcon(baseIcon);
		StartIcon.image = "http://www.google.com/mapfiles/markerA.png";

		
		
		$(window).resize(function(){
			self.resizeElevationImage();
		});
		
		this.noRider = $( "#no-riders" );
		
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	
	// I get called when the view needs to be hidden.
	CurrentRiderView.prototype.hideView = function(){
		this.clearMap();
		this.elevationImage.addClass( "hide" );
		this.elevationStatsOut.empty();
		this.hideNoRidersAlert();
		
	};	
	
	
	
	// I get called when the view needs to be shown.
	CurrentRiderView.prototype.showView = function( parameters ){
		var self = this;
		
		application.getModel( "CurrentRider" ).getNavLinks(
				function( links ){
					self.riderNav.empty();
					for(var x=0; x < links.length; x++)
					{
						self.riderNav.append('<a href="' + links[x].url + '">' + 
								links[x].title + '</a><br />');
					}
				},
				function( error ){
					alert( error );
				}
			);
		this.single = false;
		this.map = map;
		// Clear the search form.
		if(parameters.id && parameters.days)
		{
			this.single = true;
			this.elevationStatsOut.empty();
			this.elevationStatsOut.removeClass( "hide" );
			this.showRider( parameters.id, parameters.days );
			this.resizeElevationImage();
		}
		else
		{	
			// Populate the contacts.
			this.populateRiders();
		}
	};
	
	CurrentRiderView.prototype.showRider = function( id, days ){
		var self = this;
		application.getModel( "CurrentRider" ).getSingleRider( id, days,
			function( rider ){
				// Loop over the riders and create markers.
				self.markerDataStage.append('<li>' 
							+ $.toJSON( rider ) + '</li>');
				
				self.placeRiderTrail( rider.coords );
				
				var startLat = rider.coords[rider.coords.length-1].lat;
				var startLng = rider.coords[rider.coords.length-1].lng;
				self.getRiderStats( startLat, startLng , rider.lat, rider.lng );
				
			},
			function( error ){
				alert( error );
			}
		);
		this.grabMarkerData();
	};
	
	
	CurrentRiderView.prototype.getRiderStats = function( startLat, startLng, endLat, endLng ){
		var self = this;
		var startLatLng = new GLatLng( startLat, startLng );
		var endLatLng = new GLatLng( endLat, endLng );
		var src = application.getModel( "ElevationModel" ).getGraphSrc(
				 startLatLng, endLatLng );
		application.getModel( "ElevationModel" ).getStats(
			startLatLng,
			endLatLng,
			function( Elevation ){
				
				self.elevationStatsOut.html("<h3>Stats for miles<br />" + 
					Elevation.startmile + " to " + Elevation.endmile + 
					"</h3>Distance: " + Elevation.distance + 
					" miles <br />Ascent: " + Elevation.ascent + 
					" feet<br />Descent: " + Elevation.descent + 
					" feet<br />Net: " + Elevation.net + " feet");
			}
		);
		this.resizeElevationImage();
		this.elevationImage.html("<img src=\"" + src + "\" />");
		this.elevationImage.removeClass( "hide" );
	};
	
	CurrentRiderView.prototype.placeRiderTrail = function( coords ){
		//TODO put markers on map
		
	};
	
	CurrentRiderView.prototype.resizeElevationImage = function(){
		var imgDim = application.getModel( "ElevationModel" )
			.getImageDimensions($(window).width(),$(window).height());

		this.elevationImage.find('img').css({
			"position": "absolute",
			"top":		imgDim[2] + "px",
			"left":		imgDim[3] + "px",
			"width":	imgDim[0] + "px",
			"height":	imgDim[1] + "px"
		});	
	};
	
	CurrentRiderView.prototype.populateRiders = function(){
		var self = this;
		// Clear the list.
		this.clearMap();
		
		// Get the riders
		application.getModel( "CurrentRider" ).getRiders(
			function( riders ){
				// Loop over the riders and create markers.
				for(var x=0; x < riders.length; x++)
				{
					self.markerDataStage.append('<li>' 
							+ $.toJSON( riders[x] ) + '</li>');
				}
				if(riders.length == 0)
				{
					self.showNoRidersAlert();
				}
			},
			function( error ){
				alert( error );
			}
		);
		this.grabMarkerData();
	};
	
	CurrentRiderView.prototype.hideNoRidersAlert = function(){
		this.noRider.addClass( "hide" );
	};
	
	CurrentRiderView.prototype.showNoRidersAlert = function(){
		this.noRider.removeClass( "hide" );
	};
	
	CurrentRiderView.prototype.getIcon = function( ridetype ){
		var icon;
		switch(ridetype)
	    {
		    case 'Tour Divide':
	    		icon = TourDivideIcon;
	    	break;
		    case 'Great Divide Race':
	    		icon = GreatDivideRaceIcon;
	    	break;
		    case 'Slow Tour':
		    	icon = SlowTourIcon;
		    break;
		    default:
	    		icon = baseIcon;
	    	break;
	    }
		return icon;
	};
	
	CurrentRiderView.prototype.grabMarkerData = function(){
		var markers = $( "#marker-data-stage > li" );
		var self = this;
		var windowHTML = '';
		markers.each(function(index){
			
		    var currentMarker = $.parseJSON( $(this).text() );

		    var loc = new GLatLng( currentMarker.lat, currentMarker.lng );
		    var icon = self.getIcon( currentMarker.ridetype );

		    var marker = new GMarker(loc,{
				icon: icon,
				title: currentMarker.title
			});
		   
		    var linkHTML = '';
		    for(var x=0; x < currentMarker.links.length; x++)
			{
				linkHTML += '<a href="' + currentMarker.links[x][0] + '">' 
					+ currentMarker.links[x][1] + '</a><br />';
			}
		    
		    windowHTML = '<div class="marker-window">' +
				'<h3>' + currentMarker.title + '</h3>' + 
				linkHTML +
				'<div class="marker-window-trail">' + 
				'	<h4>View Trail</h4>' + 
				'	<a href="#/riders/' + currentMarker.riderid + '/1">1-day</a> | ' + 
				'	<a href="#/riders/' + currentMarker.riderid + '/3">3-day</a> | ' + 
				'	<a href="#/riders/' + currentMarker.riderid + '/7">7-day</a> | ' + 
				'	<a href="#/riders/' + currentMarker.riderid + '/14">14-day</a>' + 
				'</div>' + 
				'</div>';
		    
		    
		    marker.bindInfoWindowHtml( windowHTML);
			self.riderMarker.push( marker );
			
			//if we're looking at a single rider, grab the trailing coordinates and push them to self.trailMarker
			if(self.single)
			{
				for(var x=1; x < currentMarker.coords.length; x++)
				{
					var icon = self.getIcon('');
					var loc = new GLatLng( currentMarker.coords[x].lat, currentMarker.coords[x].lng );
					var marker = new GMarker(loc,{
						icon: icon,
						title: currentMarker.coords[x].time
					});
					self.riderTrail.push( marker );
				}
			}
		  });
		
		this.riderMarker = self.riderMarker;

		for(var x=0; x < this.riderMarker.length; x++)
		{
			this.map.addOverlay( this.riderMarker[x] );
			if( this.single )
			{
				this.riderMarker[x].openInfoWindowHtml( windowHTML );
			}
		}
		for(var x=0; x < this.riderTrail.length; x++)
		{
			this.map.addOverlay( this.riderTrail[x] );
		}

	};
	
	CurrentRiderView.prototype.clearMap = function(){
		//remove rider overlays from map
		this.markerDataStage.empty();
		
		for(var x=0; x < this.riderMarker.length; x++)
		{
			this.riderMarker[x].closeInfoWindow();
			this.map.removeOverlay(this.riderMarker[x]);
		}
		
		for(var x=0; x < this.riderTrail.length; x++)
		{
			this.map.removeOverlay(this.riderTrail[x]);
		}
		
		this.riderMarker.length = 0;
		this.riderTrail.length = 0;
	};

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new view class singleton instance.
	return( new CurrentRiderView() );
	
})( jQuery, window.application ));
