
// I am the view helper for the Contact Add / Edit form. I bind the 
// appropriate event handlers and translate the UI events into actions
// within the application.

// Add view to the application.
window.application.addView((function( $, application ){

	// I am the contact form view class.
	function AddView(){
		this.view = null;
		this.contentWindow = null;
		this.pages = null;
	};
	
	AddView.prototype.init = function(){
		var self = this;
		
		this.contentWindow = $( "#content" );
		this.pages = $( "#primary-content-stages > li" );
		this.view = this.pages.filter( "[ rel = 'add-spot' ]" );
	};
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //

	
	// I get called when the view needs to be hidden.
	AddView.prototype.hideView = function(){
		this.view.addClass( "primary-content-stage" );
		this.view.removeClass( "current-primary-content-view" );
		this.contentWindow.addClass( "hide" );
	};
	
	// I get called when the view needs to be shown.
	AddView.prototype.showView = function( parameters ){
		var self = this;
		this.contentWindow.removeClass( "hide" );
		this.view.removeClass( "primary-content-stage" );
		this.view.addClass( "current-primary-content-view" );
	};
	

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new view class singleton instance.
	return( new AddView() );
	
})( jQuery, window.application ));
