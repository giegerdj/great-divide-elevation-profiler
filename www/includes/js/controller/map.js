
// I control the events within the Contacts section of the application.

// Add a controller to the application.
window.application.addController((function( $, application ){

	// I am the controller class.
	function Controller(){
		// Route URL events to the controller's event handlers.
		this.route( "/", this.currentRider );
		this.route( "/elevation", this.elevationMapView );
		this.route( "/riders/", this.currentRider );
		this.route( "/riders/:id/:days", this.currentRider );
		this.route( "/riders/edit", this.editspot);
		this.route( "/riders/add", this.addspot);
		this.route( "/riders/login", this.login );
		this.route( "/riders/signup", this.signup );
		this.route( "/riders/logout", this.logout );
		this.route( "/riders/delete", this.deleteSpot );
		this.route( "/riders/settings", this.settings );
		this.route( "/riders/reset", this.resetPassword );
		this.route( "/about", this.about );
		
		// Set default properties.
		this.currentView = null;
		this.elevationMap = null;
		
		this.contentWindow = null;
		this.currentRiderView = null;
		this.loginView = null;
		this.editView = null;
		this.addView = null;
		this.signupView = null;
		this.settingsView = null;
		this.resetPasswordView = null;
		this.aboutView = null;
		
	};

	// Extend the core application controller (REQUIRED).
	Controller.prototype = new application.Controller();
	
	
	// I initialize the controller. I get called once the application starts
	// running (or when the controller is registered - if the application is 
	// already running). At that point, the DOM is available and all the other 
	// model and view classes will have been added to the system.
	Controller.prototype.init = function(){
		
		this.contentWindow = $( "#content" );
		this.addView = application.getView( "AddView" );
		this.editView = application.getView( "EditSpotForm" );
		this.elevationMap = application.getView( "ElevationMapView" );
		this.signupView = application.getView( "SignupForm" );
		this.loginView = application.getView( "LoginForm" );
		this.currentRiderView = application.getView( "CurrentRiderView" );
		this.settingsView = application.getView( "SettingsForm" );
		this.resetPasswordView = application.getView( "ResetPasswordForm" );
		this.aboutView = application.getView( "AboutView" );
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	

	Controller.prototype.about = function( event ){
		this.showView( this.aboutView, event );
	};
	
	
	Controller.prototype.currentRider = function( event ){
		this.showView( this.currentRiderView, event );
	};
	
	Controller.prototype.editspot = function( event ){
		this.showView( this.editView, event );
	};

	Controller.prototype.resetPassword = function( event ){
		this.showView( this.resetPasswordView, event );
	};
	
	Controller.prototype.logout = function( event ){
		application.getModel( "CurrentRider" ).logout(
			function(){
				application.relocateTo( "riders" );
			}
		);
	};
	
	Controller.prototype.deleteSpot = function( event ){
		application.getModel( "CurrentRider" ).deleteSpot(
			function(){
				application.relocateTo( "riders" );
			}
		);
	};
	
	Controller.prototype.addspot = function( event ){
		this.showView( this.addView, event );
	};
	
	Controller.prototype.login = function( event ){
		this.showView( this.loginView, event );
	};
	
	Controller.prototype.signup = function( event ){
		this.showView( this.signupView, event );
	};
	
	Controller.prototype.settings = function( event ){
		this.showView( this.settingsView, event );
	};

	Controller.prototype.elevationMapView = function( event ){
		this.showView( this.elevationMap, event );
	};
	
	
	// I show the given view; but first, I hide any existing view.
	Controller.prototype.showView = function( view, event ){
		
		if(view != this.elevationMap && view != this.aboutView)
		{
			application.getModel( "CurrentRider" ).getNavLinks(
				function( links ){
					$( "#riders-nav" ).empty();
					for(var x=0; x < links.length; x++)
					{
						$( "#riders-nav" ).append('<a href="' + links[x].url + '">' + 
								links[x].title + '</a><br />');
					}
				},
				function( error ){
					alert( error );
				}
			);
		}
		// Remove the current view class.
		this.contentWindow.addClass("hide");
		
		// Check to see if there is a current view. If so, then hide it.
		if (this.currentView && this.currentView.hideView){
			this.currentView.hideView();
		}

		// Show the given view.
		if( view && view.showView )
		{
			view.showView( event.parameters );
		}
		
		// Store the given view as the current view.
		this.currentView = view;
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new contoller singleton instance.
	return( new Controller() );
	
})( jQuery, window.application ));
