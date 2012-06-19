
// I represent an elevation object.

// Add model to the application.
window.application.addModel((function( $, application ){

	
	// overloaded elevation constructor
	function ElevationModel( ){
		
	};

	ElevationModel.prototype.init = function(){
		//nothing here
	};
	
	ElevationModel.prototype.getImageDimensions = function( width, height ){
		var retVal = Array(4);
		retVal[0] = Math.round(width*.8);
		retVal[1] = (Math.round(height*0.25) < 100) ? 100 : Math.round(height*0.25);
		retVal[2] = (height-retVal[1]);
		retVal[3] = Math.round((width - retVal[0])/2);
		return retVal;
	};
	
	ElevationModel.prototype.getGraphSrc = function( start, end ){
		var coords = start.lat() + "," + start.lng() + 
		"," + end.lat() + "," + end.lng();
		var imgDim = this.getImageDimensions($(window).width(), $(window).height());
		
		return "graph.php?c=" + coords + "&h=" + imgDim[1] + "&w=" + imgDim[0];
	};
	
	ElevationModel.prototype.getStats = function( start, end, onSuccess, onError ){
		var self = this;
		var coords = start.lat() + "," + start.lng() + 
			"," + end.lat() + "," + end.lng();
		
		// Get the contacts from the server.
		
		application.ajax({
			url: "includes/php/ajax.php",//TODO url of ajax handler php file
			type: 'POST',
			data: {
				method: "getElevationStats",
				coords: coords
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( self.populateElevationFromResponse( response.data ) );
				} else{
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	ElevationModel.prototype.populateElevationFromResponse = function( responseData ){
		
		// populate a single elevation object
		var returnElevation = application.getModel( "Elevation", [ responseData.startmile, 
                     responseData.endmile,
                     responseData.distance,
                     responseData.direction,
                     responseData.ascent,
                     responseData.descent,
                     responseData.net] );
		return returnElevation;
	};

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new model class.
	return( new ElevationModel() );
	
})( jQuery, window.application ));
