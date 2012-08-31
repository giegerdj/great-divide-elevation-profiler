
// I control the primary navigation and the corresponding view of content that 
// is displayed on the page. I do not control the content that is displayed within 
// the primary content view (that is delegated to the other controllers).

// Add a controller to the application.
window.application.addController((function( $, application ){

	// I am the controller class.
	function Controller(){
		// Route URL events to the controller's event handlers.
		this.route( "/", this.elevation );
		this.route( "/elevation", this.elevation );
		this.route( "/riders.*", this.elevation );
		this.route( "/about.*", this.about );

		// Set default properties.
		this.contentWindow = null;
		this.elevationView = null;
		this.ridersView = null;
		this.elevationStatsView = null;
		this.elevationImage = null;
		this.aboutView = null;
	};

	// Extend the core application controller (REQUIRED).
	Controller.prototype = new application.Controller();
	
	
	Controller.prototype.init = function(){

		this.contentWindow = $("#content");
		this.elevationView = $("#elevation-nav");
		this.ridersView = $("#riders-nav");
		this.elevationStatsView = $("#elevation-stats");
		this.elevationImage = $("#elevation-profile-img");
		this.aboutView = $( "#about-nav" );
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	Controller.prototype.about = function( event ){
		this.showView( this.aboutView );
	};
	
	// I show the contacts view.
	Controller.prototype.riders = function( event ){
		this.showView( this.ridersView );
	};
	
	// I show the elevation view.
	Controller.prototype.elevation = function( event ){
		this.showView( this.elevationView );
	};
	
	// I show the given view; but first, I hide any existing view.
	Controller.prototype.showView = function( view ){
		// Remove the current view class.
		this.elevationView.addClass( "hide" );
		this.ridersView.addClass( "hide" );
		this.aboutView.addClass( "hide" );
		this.contentWindow.addClass( "hide" );
		//this.elevationImage.addClass( "hide" );
		this.elevationStatsView.addClass("hide");

		if(view == this.elevationView)
		{
			//this.elevationImage.removeClass( "hide" );
			this.elevationStatsView.removeClass( "hide" );
		}
		
		view.removeClass( "hide" );
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new contoller singleton instance.
	return( new Controller() );
	
})( jQuery, window.application ));
