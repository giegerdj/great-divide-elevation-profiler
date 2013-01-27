
function ElevationMap( config ) {
  this.route_polyline = config.polyline;
  this.map = config.map;
  this.start_marker = null;
  this.start_cardinal = config.start_cardinal;
  this.end_marker = null;
  this.end_cardinal = config.end_cardinal;
  this.start_snap = null;
  this.end_snap = null;
  this.updateCallback = config.updateCallback;
  
  this.start_mile = config.start_mile;
  this.end_mile = config.end_mile;
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
  var me = this;
  $(this.direction_radio_selector).change(function(){
    me.toggleDirection();
  });
};

ElevationMap.prototype.toggleDirection = function() {
  /**
   * swap the A and B markers
   */
  var a_pos = this.start_marker.getPosition();
  var b_pos = this.end_marker.getPosition();
  
  this.start_marker.setPosition(b_pos);
  this.end_marker.setPosition(a_pos);
  
  this.dragEnd();
};

ElevationMap.prototype.placeMarkers = function() {
  
  if(this.start_mile && this.end_mile) {
    /**
     * @todo
     * send the mile markers to ajax.
     * block the ui
     *
     * after response, place markers, bind the dragend functions,
     * update graph, update stats, update direction
     */
    
    
  } else {
    /**
     * @todo
     * the start/end miles weren't specified.
     * figure out marker positions based on the route and start/end cardinality
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
      
      me.dragEnd();
    }, 500);
  }
  
};

ElevationMap.prototype.dragEnd = function() {
  var data = {};
  data['start_coord'] = [
    this.start_marker.getPosition().lat(),
    this.start_marker.getPosition().lng()
  ];
  data['end_coord'] = [
    this.end_marker.getPosition().lat(),
    this.end_marker.getPosition().lng()
  ];
  
  var me = this;
  $.ajax({
    url : '/segment-stats/',
    data : data,
    type : 'post',
    dataType: 'json',
    success : function(res){
      me.updateCallback(res);
    }
  });
};

ElevationMap.prototype.getStartMile = function() {
  return this.start_snap.getDistAlongRoute();
};

ElevationMap.prototype.getEndMile = function() {
  return this.end_snap.getDistAlongRoute();
};