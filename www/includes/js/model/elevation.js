
// I represent a contact object.

// Add model to the application.
window.application.addModel((function( $, application ){

	// I am the contact class.
	function Elevation()
	{
		this.startmile = 0;
		this.endmile = 0;
		this.distance = 0;	//TODO figure out by abs(start-end)
		this.direction = "N";	//TODO figure out by start,end
		this.ascent = 0;
		this.descent = 0;
		this.net = 0;
	}
	function Elevation( start, end, distance, direction, ascent, descent, net ){
		this.startmile = (start || 0);
		this.endmile = (end || 0);
		this.distance = (distance || 0);	//TODO figure out by abs(start-end)
		this.direction = (direction || "N");	//TODO figure out by start,end
		this.ascent = (ascent || 0);
		this.descent = (descent || 0);
		this.net = (net || 0);
	};

	
	

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new model class.
	return( Elevation );
	
})( jQuery, window.application ));
