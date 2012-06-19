
// I am the view helper for the Contact Add / Edit form. I bind the 
// appropriate event handlers and translate the UI events into actions
// within the application.

// Add view to the application.
window.application.addView((function( $, application ){

	// I am the contact form view class.
	function ResetPasswordForm(){
		this.view = null;
		this.backLink = null;
		this.form = null;
		this.success = null;
		this.errors = null;
		this.fields = {
			email: null,
			button: null
		};
		this.contentWindow = null;
		this.pages = null;
		
		this.verifier = null;
	};
	

	// I initialize the view. I get called once the application starts
	// running (or when the view is registered - if the application is 
	// already running). At that point, the DOM is available and all the other 
	// model and view classes will have been added to the system.
	ResetPasswordForm.prototype.init = function(){
		var self = this;
		
		// Initialize properties.
		this.pages = $( "#primary-content-stages > li" );
		this.view = this.pages.filter( "[ rel = 'reset' ]" );
		
		// Initialize properties.
		this.form = $( "#recover-holder" );
		
		this.errors = this.view.find( "div.form-errors" );
		this.success = this.view.find( "div.form-success" );
		this.fields.email = this.form.find( ":input[ name = 'email' ]" );
		this.fields.button = this.form.find( ":button[ name = 'reset-button' ]" );
		
		this.contentWindow = $( "#content" );
		// Bind the submit handler.
		
		this.form.submit(
			function( event ){
				// Submit the form.
				self.disableForm();
				self.submitForm();
				self.enableForm();
				// Cancel default event.
				return( false );
			}
		);
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	
	// I apply the given submission errors to the form. This involves translating the 
	// paramters-based errors into user-friendly errors messages.
	ResetPasswordForm.prototype.applyErrors = function( error ){
		// Clear any existing errors.
		this.clearErrors();
		// Show the errors.
		this.errors.html(error);
		this.errors.removeClass( "hide" );
	};
	
	ResetPasswordForm.prototype.applySuccess = function( success ){
		// Clear any existing errors.
		this.clearErrors();
		// Show the errors.
		this.success.html(success);
		this.success.removeClass( "hide" );
	};
	
	ResetPasswordForm.prototype.clearEmail = function(){
		this.fields.email.attr("value","");
	};
	
	
	// I clear the errors from the field.
	ResetPasswordForm.prototype.clearErrors = function(){
		this.errors.addClass( "hide" );
		this.errors.html("");
		
		this.success.addClass( "hide" );
		this.success.html("");
		
	};
	
	
	// I diable the form.
	ResetPasswordForm.prototype.disableForm = function(){
		// Disable the fields.
		this.fields.email.attr( "disabled", true );
		this.fields.button.attr( "disabled", true);
	};
	
	
	// I enable the form.
	ResetPasswordForm.prototype.enableForm = function(){
		// Enable the fields.
		this.fields.email.removeAttr( "disabled" );
		this.fields.button.removeAttr( "disabled" );
	};
	
	
	// I get called when the view needs to be hidden.
	ResetPasswordForm.prototype.hideView = function(){
		this.clearErrors();
		this.clearEmail();
		this.view.addClass( "primary-content-stage" );
		this.view.removeClass( "current-primary-content-view" );
		this.contentWindow.addClass( "hide" );
		
	};
	
	
	// I get called when the view needs to be shown.
	ResetPasswordForm.prototype.showView = function( parameters ){
		
		// Show the view.
		this.contentWindow.removeClass( "hide" );
		this.view.removeClass( "primary-content-stage" );
		this.view.addClass( "current-primary-content-view" );
				
		// Focus the first field.
		this.fields.email.focus();
	};	
	
	
	// I submit the form.
	ResetPasswordForm.prototype.submitForm = function(){
		var self = this;
		// Try to save the contact using the contact service.
		application.getModel( "CurrentRider" ).getTempPassword(
			this.fields.email.val(),
			// Success callback.
			function( success ){
				self.applySuccess( success );
				// Focus the name field.
				self.fields.email.focus();
			},
			
			// Error callback.
			function( errors ){
				// Apply the errors to the form.
				self.applyErrors( errors );
				
				// Focus the name field.
				self.fields.email.focus();
			}
		);
	};
	

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new view class singleton instance.
	return( new ResetPasswordForm() );
	
})( jQuery, window.application ));
