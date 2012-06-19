
// I am the gateway to the contacts collection within the system. For this
// demo, there is no communication with the server - all contacts are stored
// locally and internally to this service object.

// Add model to the application.
window.application.addModel((function( $, application ){

	// I am the contacts service class.
	function CurrentRider(){
		this.riders = [];
	};

	
	// I initialize the model. I get called once the application starts
	// running (or when the model is registered - if the application is 
	// already running). At that point, the DOM is available and all the other 
	// model and view classes will have been added to the system.
	CurrentRider.prototype.init = function(){
		// ... nothing needed here ...
	};
	
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	CurrentRider.prototype.login = function( email, password, onSuccess, onError){
		
		email = $.base64Encode(email);
		password = $.base64Encode(password);

		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			async: false,
			data: {
				method: "login",
				email: email,
				password: password
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					onSuccess( );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.saveSpot = function( title, spot, dir, type, links, date, onSuccess, onError){
		
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "saveSpot",
				title: title,
				spot: spot,
				dir: dir,
				type: type,
				links: links,
				date: date
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.signup = function ( email, password, onSuccess, onError ){
		
		email = $.base64Encode(email);
		password = $.base64Encode(password);
		
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "signup",
				email: email,
				password: password
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.logout = function( onSuccess ){
		
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			async: false,
			data: {
				method: "logout"
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				onSuccess();
			}
		});
	};
	
	CurrentRider.prototype.getNavLinks = function( onSuccess, onError ){

		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "getNavLinks"
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.getSingleRider = function( id, days, onSuccess, onError){
		
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			async: false,
			data: {
				method: "getSingleRiderHistory",
				id: id,
				days: days
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	
	// I get the contacts.
	CurrentRider.prototype.getRiders = function( onSuccess, onError ){

		// Return the rider collection.
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			async: false,
			data: {
				method: "getCurrentRiders"
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.deleteSpot = function( onSuccess ){
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			async: false,
			data: {
				method: "deleteSpot"
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				onSuccess( );
			}
		});
	};
	
	CurrentRider.prototype.getSpotInfo = function( onSuccess, onError ){
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "getSpotInfo"
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.getSettings = function( onSuccess, onError ){
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "getSettings"
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.saveSettings = function ( email, password, oldpassword, onSuccess, onError ){
		
		email = $.base64Encode(email);
		password = $.base64Encode(password);
		oldpassword = $.base64Encode(oldpassword);
		
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "saveSettings",
				email: email,
				password: password,
				oldpassword: oldpassword
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	CurrentRider.prototype.getTempPassword = function ( email, onSuccess, onError ){

		email = $.base64Encode(email);
		
		application.ajax({
			url: "includes/php/ajax.php",
			type: 'POST',
			data: {
				method: "getTempPassword",
				email: email
			},
			dataType: 'json',
			normalizeJSON: true,
			success: function( response ){
				// Check to see if the request was successful.
				if (response.success){
					// Create contacts based on the response data and pass the contact 
					// off to the callback.
					
					onSuccess( response.data );
				} else {
					// The call was not successful - call the error function.
					onError( response.data );
				}
			}
		});
	};
	
	// ----------------------------------------------------------------------- //
	// ----------------------------------------------------------------------- //
	
	// Return a new model class singleton instance.
	return( new CurrentRider() );
	
})( jQuery, window.application ));
