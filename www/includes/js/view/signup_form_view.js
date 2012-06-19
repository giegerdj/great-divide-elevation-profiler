
// I am the view helper for the Contact Add / Edit form. I bind the 
// appropriate event handlers and translate the UI events into actions
// within the application.

// Add view to the application.
window.application.addView((function( $, application ){

	// I am the contact form view class.
	function SignupForm(){
		this.view = null;
		this.backLink = null;
		this.form = null;
		this.errors = null;
		this.fields = {
			email: null,
			password: null,
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
	SignupForm.prototype.init = function(){
		var self = this;
		
		// Initialize properties.
		this.pages = $( "#primary-content-stages > li" );
		this.view = this.pages.filter( "[ rel = 'signup' ]" );
		
		// Initialize properties.
		this.form = $( "#signup-holder" );
		
		this.errors = this.view.find( "div.form-errors" );
		this.fields.email = this.form.find( ":input[ name = 'email' ]" );
		this.fields.password = this.form.find( ":input[ name = 'spassword' ]" );
		this.fields.button = this.form.find( ":button[ name = 'signup-button' ]" );
		
		this.verifier = $( "#verify-pw" );
		this.contentWindow = $( "#content" );
		// Bind the submit handler.
		
		this.verifier.change(function(){
			if(self.verifier.attr('checked'))
			{
				document.getElementById('spassword').setAttribute('type','text');
			}
			else
			{
				document.getElementById('spassword').setAttribute('type','password');
			}
		});
		
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
	SignupForm.prototype.applyErrors = function( error ){
		// Clear any existing errors.
		this.clearErrors();
		this.clearPassword();
		// Show the errors.
		this.errors.html(error);
		this.errors.removeClass( "hide" );
	};
	
	SignupForm.prototype.clearPassword = function(){
		this.fields.password.attr("value","");
	};
	
	SignupForm.prototype.clearEmail = function(){
		this.fields.email.attr("value","");
	};
	
	
	// I clear the errors from the field.
	SignupForm.prototype.clearErrors = function(){
		this.errors.addClass( "hide" );
		this.errors.html("");
		
	};
	
	
	// I diable the form.
	SignupForm.prototype.disableForm = function(){
		// Disable the fields.
		this.fields.email.attr( "disabled", true );
		this.fields.password.attr( "disabled", true );
		this.fields.button.attr( "disabled", true);
	};
	
	
	// I enable the form.
	SignupForm.prototype.enableForm = function(){
		// Enable the fields.
		this.fields.email.removeAttr( "disabled" );
		this.fields.password.removeAttr( "disabled" );
		this.fields.button.removeAttr( "disabled" );
	};
	
	
	// I get called when the view needs to be hidden.
	SignupForm.prototype.hideView = function(){
		this.clearErrors();
		this.clearPassword();
		this.clearEmail();
		this.view.addClass( "primary-content-stage" );
		this.view.removeClass( "current-primary-content-view" );
		this.contentWindow.addClass( "hide" );
		
	};
	
	
	// I get called when the view needs to be shown.
	SignupForm.prototype.showView = function( parameters ){
		// Show the view.
		this.contentWindow.removeClass( "hide" );
		this.view.removeClass( "primary-content-stage" );
		this.view.addClass( "current-primary-content-view" );
				
		// Focus the first field.
		this.fields.email.focus();
	};	
	
	
	// I submit the form.
	SignupForm.prototype.submitForm = function(){
		var self = this;
		// Try to save the contact using the contact service.
		application.getModel( "CurrentRider" ).signup(
			this.fields.email.val(),
			this.fields.password.val(),
			
			// Success callback.
			function( ){
				application.relocateTo( "riders/edit" );
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
	return( new SignupForm() );
	
})( jQuery, window.application ));
