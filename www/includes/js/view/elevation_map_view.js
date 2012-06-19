
// I am a view helper for the Contact List. I bind the appropriate 
// event handlers and translate the UI events into actions within 
// the application.

// Add view to the application.
window.application.addView((function( $, application ){
	// I am the elevation map view class
	function ElevationMapView(){
		
		this.map = null;
		this.startMarker = null;
		this.startMarkerLocation = null;
		this.endMarker = null;
		this.endMarkerLocation = null;
		this.snapToRouteA = null;
		this.snapToRouteB = null;
		this.route = null;
		
		this.elevationImage = null;
		this.startMovedListener = null;
		this.endMovedListener = null;
		this.elevationStatsOut = null;
		this.directionRadio = null;
		
	};

	
	// I initialize the view. I get called once the application starts
	// running (or when the view is registered - if the application is 
	// already running). At that point, the DOM is available and all the other 
	// model and view classes will have been added to the system.
	ElevationMapView.prototype.init = function(){
		var self = this;
		this.elevationStatsOut = $( "#elevation-stats" );
		this.elevationImage = $( "#elevation-profile-img" );
		
		$(window).resize(function(){
			self.resizeElevationImage();
		});
		
		var baseIcon = new GIcon(G_DEFAULT_ICON);
		baseIcon.iconSize = new GSize(20, 34);
		
		var EndIcon = new GIcon(baseIcon);
		EndIcon.image = "http://www.google.com/mapfiles/markerB.png";

		var StartIcon = new GIcon(baseIcon);
		StartIcon.image = "http://www.google.com/mapfiles/markerA.png";
		
		
		$("input[name=direction]:radio").change( function(){
			self.radioChanged();
		});
		
		this.startMarkerLocation = new GLatLng(51.160930102318986,-115.5982984602451);
		this.endMarkerLocation = new GLatLng(31.334270220253053,-108.53035010397434);
		
		this.startMarker = new GMarker(this.startMarkerLocation,{draggable: true,icon:StartIcon});
		this.endMarker = new GMarker(this.endMarkerLocation,{draggable: true,icon:EndIcon});
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	ElevationMapView.prototype.resizeElevationImage = function(){
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
	
	ElevationMapView.prototype.radioChanged = function(){
		var dir = $("input[@name=direction]:checked").attr('id');

		var aDist = this.snapToRouteA.getDistAlongRoute(this.startMarker);
		var bDist = this.snapToRouteB.getDistAlongRoute(this.endMarker);
		aDist = (isNaN(aDist)) ? 0 : aDist;
		bDist = (isNaN(bDist)) ? 0 : bDist;
		
		if((dir == 'nobo' && aDist > bDist) || (dir == 'sobo' && aDist <= bDist))
		{
			var tmp = this.startMarker.getPoint();
			this.startMarker.setLatLng(this.endMarker.getPoint());
			this.endMarker.setLatLng(tmp);
			
			this.dragEnd();
		}
	};
	
	
	ElevationMapView.prototype.dragEnd = function(){
		var self = this;
		
		var src = application.getModel( "ElevationModel" ).getGraphSrc(
				this.startMarker.getLatLng(),
				this.endMarker.getLatLng());

		this.elevationImage.html("<img src=\"" + src + "\" />");
		this.resizeElevationImage();
		
		
		application.getModel( "ElevationModel" ).getStats(
			this.startMarker.getLatLng(),
			this.endMarker.getLatLng(),
			function( Elevation ){
				self.populateStats(
						Elevation.startmile,
						Elevation.endmile,
						Elevation.distance,
						Elevation.direction,
						Elevation.ascent,
						Elevation.descent,
						Elevation.net);
				self.updateRadio( Elevation.direction );
			},function( errorData ){
				alert( errorData );
			});
	};
	
	ElevationMapView.prototype.updateRadio = function( dir ){
		if(dir == 'N')
			$("#nobo").attr('checked',true);
		else
			$("#sobo").attr('checked',true);
	};
	
	ElevationMapView.prototype.populateStats = function( startMile, endMile, distance, 
			direction, ascent, descent, net){
		var self = this;
		self.elevationStatsOut.html("<h3>Stats for miles<br />" + startMile + 
				" to " + endMile + "</h3>Distance: " + 
			distance + " miles <br />Ascent: " + ascent + " feet<br />Descent: " + 
			descent + " feet<br />Net: " + net + " feet");
	};
	
	// I get called when the view needs to be hidden.
	ElevationMapView.prototype.hideView = function(){
		//remove markers from map
		this.elevationStatsOut.html("");
		this.elevationImage.addClass( "hide" );
		this.map.removeOverlay(this.startMarker);
		this.map.removeOverlay(this.endMarker);
		
		if(this.startMovedListener)
			this.startMovedListener = GEvent.removeListener(this.startMovedListener);
		
		if(this.endMovedListener)
			this.endMovedListener = GEvent.removeListener(this.endMovedListener);
	};
	
	
	// I get called when the view needs to be shown.
	ElevationMapView.prototype.showView = function( parameters ){
		var self = this;
		this.map = map;
		this.route = route;
		this.elevationImage.removeClass( "hide" );
		if(parameters.start && parameters.end)
		{
			this.snapToRouteA = new SnapToRoute(this.map, this.startMarker, this.route);
			this.snapToRouteB = new SnapToRoute(this.map, this.endMarker, this.route);
		}
		else if(parameters.start)
		{
			this.snapToRouteA = new SnapToRoute(this.map, this.startMarker, this.route);
			this.snapToRouteB = new SnapToRoute(this.map, this.endMarker, this.route);
		}
		else
		{
			this.snapToRouteA = new SnapToRoute(this.map, this.startMarker, this.route);
			this.snapToRouteB = new SnapToRoute(this.map, this.endMarker, this.route);
			
			this.map.addOverlay(this.startMarker);
			this.map.addOverlay(this.endMarker);
		}
		
		this.startMovedListener = GEvent.addListener(this.startMarker,"dragend",function( event ){
			self.dragEnd();
			});
		this.endMovedListener = GEvent.addListener(this.endMarker,"dragend",function( event ){
			self.dragEnd();
			});
		self.resizeElevationImage();
		this.dragEnd();
	};	
	
	

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new view class singleton instance.
	return( new ElevationMapView() );
	
})( jQuery, window.application ));
