
// I am the view helper for the Contact Add / Edit form. I bind the 
// appropriate event handlers and translate the UI events into actions
// within the application.

// Add view to the application.
window.application.addView((function( $, application ){

	// I am the contact form view class.
	function SettingsForm(){
		this.view = null;
		this.backLink = null;
		this.form = null;
		this.errors = null;
		this.fields = {
			email: null,
			newPassword: null,
			confirmPassword: null,
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
	SettingsForm.prototype.init = function(){
		var self = this;
		
		// Initialize properties.
		this.pages = $( "#primary-content-stages > li" );
		this.view = this.pages.filter( "[ rel = 'settings' ]" );
		
		// Initialize properties.
		this.form = $( "#settings-holder" );
		
		this.success = this.view.find( "div.form-success" );
		this.errors = this.view.find( "div.form-errors" );
		this.fields.email = this.form.find( ":input[ name = 'email' ]" );
		this.fields.newPassword = this.form.find( ":input[ name = 'sspassword' ]" );
		this.fields.confirmPassword = this.form.find( ":input[ name = 'oldpassword' ]" );
		this.fields.button = this.form.find( ":button[ name = 'save-button' ]" );
		
		this.verifier = $( "#sverify-pw" );
		this.contentWindow = $( "#content" );
		// Bind the submit handler.
		
		this.verifier.change(function(){
			if(self.verifier.attr('checked'))
			{
				document.getElementById('sspassword').setAttribute('type','text');
			}
			else
			{
				document.getElementById('sspassword').setAttribute('type','password');
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
	SettingsForm.prototype.applyErrors = function( error ){
		// Clear any existing errors.
		this.clearErrors();
		this.clearPassword();
		// Show the errors.
		this.errors.html( error );
		this.errors.removeClass( "hide" );
	};
	SettingsForm.prototype.applySuccess = function( success ){
		// Clear any existing errors.
		this.clearErrors();
		this.clearPassword();
		// Show the errors.
		this.success.html( success );
		this.success.removeClass( "hide" );
	};
	
	SettingsForm.prototype.clearPassword = function(){
		this.fields.confirmPassword.attr("value","");
		this.fields.newPassword.attr("value","");
	};
	
	SettingsForm.prototype.clearEmail = function(){
		this.fields.email.attr("value","");
	};
	
	
	// I clear the errors from the field.
	SettingsForm.prototype.clearErrors = function(){
		this.errors.addClass( "hide" );
		this.errors.html("");
		
		this.success.addClass( "hide" );
		this.success.html("");
		
	};
	
	
	// I diable the form.
	SettingsForm.prototype.disableForm = function(){
		// Disable the fields.
		this.fields.email.attr( "disabled", true );
		this.fields.newPassword.attr( "disabled", true );
		this.fields.confirmPassword.attr( "disabled", true );
		this.fields.button.attr( "disabled", true);
	};
	
	
	// I enable the form.
	SettingsForm.prototype.enableForm = function(){
		// Enable the fields.
		this.fields.email.removeAttr( "disabled" );
		this.fields.newPassword.removeAttr( "disabled" );
		this.fields.confirmPassword.removeAttr( "disabled" );
		this.fields.button.removeAttr( "disabled" );
	};
	
	
	// I get called when the view needs to be hidden.
	SettingsForm.prototype.hideView = function(){
		this.clearErrors();
		this.clearPassword();
		this.clearEmail();
		this.view.addClass( "primary-content-stage" );
		this.view.removeClass( "current-primary-content-view" );
		this.contentWindow.addClass( "hide" );
		
	};
	
	
	// I get called when the view needs to be shown.
	SettingsForm.prototype.showView = function( parameters ){
		
		// Reset the form.
		
		// Show the view.
		this.contentWindow.removeClass( "hide" );
		this.view.removeClass( "primary-content-stage" );
		this.view.addClass( "current-primary-content-view" );
		
		this.populateForm();
		
		// Focus the first field.
		this.fields.email.focus();
	};	
	
	
	SettingsForm.prototype.populateForm = function(){
		var self = this;
		application.getModel( "CurrentRider" ).getSettings(
				// Success callback.
				function( info ){
					self.fillForm( info );
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
	
	SettingsForm.prototype.fillForm = function( info ){
		this.fields.email.attr( "value", info.email );
	};
	
	// I submit the form.
	SettingsForm.prototype.submitForm = function(){
		var self = this;
		// Try to save the contact using the contact service.
		application.getModel( "CurrentRider" ).saveSettings(
			this.fields.email.val(),
			this.fields.newPassword.val(),
			this.fields.confirmPassword.val(),
			// Success callback.
			function( success ){
				self.applySuccess( success );
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
	return( new SettingsForm() );
	
})( jQuery, window.application ));
