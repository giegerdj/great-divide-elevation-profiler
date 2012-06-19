
// I am the view helper for the Contact Add / Edit form. I bind the 
// appropriate event handlers and translate the UI events into actions
// within the application.

// Add view to the application.
window.application.addView((function( $, application ){

	// I am the contact form view class.
	function EditSpotForm(){
		this.view = null;
		this.backLink = null;
		this.form = null;
		this.errors = null;
		this.success = null;
		this.fields = {
			title: null,
			spotUrl: null,
			direction: null,
			type: null,
			date: null,
			button: null
		};
		this.contentWindow = null;
		this.pages = null;
		this.linkContainer = null;
		this.addLink = null;
	};
	

	// I initialize the view. I get called once the application starts
	// running (or when the view is registered - if the application is 
	// already running). At that point, the DOM is available and all the other 
	// model and view classes will have been added to the system.
	EditSpotForm.prototype.init = function(){
		var self = this;
		
		// Initialize properties.
		this.linkContainer = $( "#links" );
		
		this.deleteLink = $( "#delete-link" );
		this.addLink = $( "#add-link" );
		// Initialize properties.
		this.pages = $( "#primary-content-stages > li" );
		this.view = this.pages.filter( "[ rel = 'edit-spot' ]" );
		
		// Initialize properties.
		this.form = $( "#spot-edit-holder" );
		
		this.errors = this.view.find( "div.form-errors" );
		this.success = this.view.find( "div.form-success" );
		this.fields.title = this.form.find( ":input[ name = 'title' ]" );
		this.fields.spotUrl = this.form.find( ":input[ name = 'spot-url' ]" );
		this.fields.direction = $( "#ride-dir" );
		this.fields.type = $( "#ride-type" );
		this.fields.date = this.form.find( ":input[ name = 'date' ]" );
		this.fields.button = this.form.find( ":button[ name = 'signup-button' ]" );
		
		this.fields.date.datepicker();
		
		this.contentWindow = $( "#content" );
		
		// Bind the submit handler.
		this.form.submit(
			function( event ){
				// Submit the form.
				self.disableForm();
				self.clearErrors();
				self.submitForm();
				self.enableForm();
				// Cancel default event.
				return( false );
			}
		);
		
		this.addLink.click(
				function( event ){
					self.linkContainer.append('<input type="text" name="link-url[]" value="URL" /> ' + 
						'<input type="text" name="link-title[]" value="Title" /><br />');
					return false;
				}
		);
		
		this.deleteLink.click(
				function( event ){
					if (confirm( "Are you sure you want to remove your SPOT info?" )){
						application.setLocation( "riders/delete" );
					}
					return( false );
				}
		);
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	EditSpotForm.prototype.submitForm = function(){
		var self = this;
		
		var title = this.fields.title.val();
		var spot = this.fields.spotUrl.val();
		var dir = this.fields.direction.val();
		var type= this.fields.type.val();
		var date = this.fields.date.val();
		var links ='';
		var urls = document.getElementsByName('link-url[]');
		var titles = document.getElementsByName('link-title[]');
		for(var x=0; x < urls.length; x++)
		{
			links += urls[x].value + '~' + titles[x].value  + '|';
		}
		
		links = $.base64Encode(links);
		// Try to save the contact using the contact service.
		application.getModel( "CurrentRider" ).saveSpot(
			title, spot, dir, type, links, date,
			
			// Success callback.
			function( success ){
				self.applySuccess( success );
			},
			
			// Error callback.
			function( errors ){
				// Apply the errors to the form.
				self.applyErrors( errors );
				
				// Focus the name field.
				self.fields.title.focus();
			}
		);
	};
	
	// I apply the given submission errors to the form. This involves translating the 
	// paramters-based errors into user-friendly errors messages.
	EditSpotForm.prototype.applyErrors = function( error ){
		// Clear any existing errors.
		this.clearErrors();
		// Show the errors.
		this.errors.html(error);
		this.errors.removeClass( "hide" );
	};
	
	
	EditSpotForm.prototype.clearErrors = function(){
		this.success.addClass( "hide" );
		this.success.html("");
		
		this.errors.addClass( "hide" );
		this.errors.html("");
		
	};
	
	
	// I diable the form.
	EditSpotForm.prototype.disableForm = function(){
		// Disable the fields.
		this.fields.title.attr( "disabled", true );
		this.fields.spotUrl.attr( "disabled", true );
		this.fields.direction.attr( "disabled", true );
		this.fields.type.attr( "disabled", true );
		this.fields.button.attr( "disabled", true );
	};
	
	
	// I enable the form.
	EditSpotForm.prototype.enableForm = function(){
		// Enable the fields.
		this.fields.title.removeAttr( "disabled" );
		this.fields.spotUrl.removeAttr( "disabled" );
		this.fields.direction.removeAttr( "disabled" );
		this.fields.type.removeAttr( "disabled" );
		this.fields.button.removeAttr( "disabled" );
	};
	
	
	// I get called when the view needs to be hidden.
	EditSpotForm.prototype.hideView = function(){
		this.view.addClass( "primary-content-stage" );
		this.view.removeClass( "current-primary-content-view" );
		this.contentWindow.addClass( "hide" );
		this.clearForm();
		this.clearErrors();
	};
	
	
	EditSpotForm.prototype.clearForm = function( ){
		this.fields.title.val( "" );
		this.fields.spotUrl.val( "" );
		this.fields.direction.val( "" );
		this.fields.type.val( "" );
		this.linkContainer.empty();
		this.fields.date.val( "" );
	};
	
	// I get called when the view needs to be shown.
	EditSpotForm.prototype.showView = function( parameters ){
		// Show the view.
		this.contentWindow.removeClass( "hide" );
		this.view.removeClass( "primary-content-stage" );
		this.view.addClass( "current-primary-content-view" );
		
		this.populateForm();
		
		// Focus the first field.
		this.fields.title.focus();
	};
	
	EditSpotForm.prototype.populateForm = function(){
		var self = this;
		application.getModel( "CurrentRider" ).getSpotInfo(
				// Success callback.
				function( info ){
					self.fillForm( info );
				},
				// Error callback.
				function( errors ){
					// Apply the errors to the form.
					self.applyErrors( errors );
					
					// Focus the name field.
					self.fields.title.focus();
				}
			);
	};
	
	
	EditSpotForm.prototype.fillForm = function( info ){
		var self = this;
		this.linkContainer.empty();
		if(info == false)
		{
			this.linkContainer.html('<input type="text" name="link-url[]" value="URL" /> ' + 
				'<input type="text" name="link-title[]" value="Title" /><br />');
		}
		else
		{
			this.fields.title.attr( "value", info.title );
			this.fields.spotUrl.attr( "value", info.url );
			this.fields.direction.val( info.direction );
			this.fields.type.val( info.ridetype );
			this.fields.date.val( info.date );
			
			
			$.each(info.links, function(index, link) {
				self.linkContainer.append('<input type="text" name="link-url[]" value="' + link[0] + '" /> ' + 
				'<input type="text" name="link-title[]" value="' + link[1] + '" /><br />');
			});
			this.linkContainer.append('<input type="text" name="link-url[]" value="URL" /> ' + 
				'<input type="text" name="link-title[]" value="Title" /><br />');
		}
	};
	
	EditSpotForm.prototype.applyErrors = function( error ){
		// Clear any existing errors.
		this.clearErrors();
		// Show the errors.
		this.errors.html(error);
		this.errors.removeClass( "hide" );
	};
	
	EditSpotForm.prototype.applySuccess = function( error ){
		// Clear any existing errors.
		this.clearErrors();
		// Show the errors.
		this.success.html(error);
		this.success.removeClass( "hide" );
	};
	

	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new view class singleton instance.
	return( new EditSpotForm() );
	
})( jQuery, window.application ));
