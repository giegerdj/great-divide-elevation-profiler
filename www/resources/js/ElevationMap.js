
function ElevationMap(map, polyline, start_cardinal, end_cardinal) {
  this.route_polyline = polyline;
  this.map = map;
  this.start_marker = null;
  this.start_cardinal = start_cardinal;
  this.end_marker = null;
  this.end_cardinal = end_cardinal;
  this.start_snap = null;
  this.end_snap = null;
  
  this.init();
}

ElevationMap.prototype.init = function() {
  /**
   * draw route on map
   * set proper bounds and zoom
   * check for hash params for start/end mileage
   * put markers on ends or supplied start/end
   */
  this.route_polyline.setMap(this.map);
  this.placeMarkers(); 
  
  
};

ElevationMap.prototype.toggleDirection = function() {
  /**
   * swap the A and B markers
   */
};

ElevationMap.prototype.placeMarkers = function() {
  /**
   * @todo figure out marker positions
   */
  this.start_marker = new google.maps.Marker({
    position: new google.maps.LatLng(51.160930102318986,-115.5982984602451),
    draggable: true,
    icon: new google.maps.MarkerImage('http://www.google.com/mapfiles/markerA.png')
  });
  
		this.end_marker = new google.maps.Marker({
    position: new google.maps.LatLng(31.334270220253053,-108.53035010397434),
    draggable: true,
    icon: new google.maps.MarkerImage('http://www.google.com/mapfiles/markerB.png')
  });
  
  var me = this;
  window.setTimeout(function(){
    me.start_marker.setMap(me.map);
    me.end_marker.setMap(me.map);
    
    me.start_snap = new SnapToRoute(me.map, me.start_marker, me.route_polyline);
    me.end_snap = new SnapToRoute(me.map, me.end_marker, me.route_polyline);
    
    google.maps.event.addListener(me.end_marker, "dragend", function(e){
      me.dragEnd();
    });
    
    google.maps.event.addListener(me.start_marker, "dragend", function(e){
      me.dragEnd();
    });
    
    
  }, 500);
  
};

ElevationMap.prototype.dragEnd = function() {
  console.log(this.start_snap.getDistAlongRoute() + ' to ' + this.end_snap.getDistAlongRoute());

};







/*
ElevationMapView.prototype.dragEnd = function(){
		var me = this;
		
		var src = application.getModel( "ElevationModel" ).getGraphSrc(
    this.startMarker.getLatLng(),
    this.endMarker.getLatLng());

		this.elevationImage.html("<img src=\"" + src + "\" />");
		this.resizeElevationImage();
		
		
		application.getModel( "ElevationModel" ).getStats(
    this.startMarker.getLatLng(),
    this.endMarker.getLatLng(),
    function( Elevation ){
      me.populateStats(
        Elevation.startmile,
        Elevation.endmile,
        Elevation.distance,
        Elevation.direction,
        Elevation.ascent,
        Elevation.descent,
        Elevation.net);
      me.updateRadio( Elevation.direction );
    },function( errorData ){
      alert( errorData );
    });
	};
*/