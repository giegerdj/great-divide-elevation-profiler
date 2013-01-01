$(document).ready(function(){
  
  $('#stat-container .toggle, #profile-container .toggle').click(function(e){
    e.preventDefault();
    var parent = $(this).parent();
    
    if( parent.hasClass('closed') ) {
      parent.removeClass('closed');
    } else {
      parent.addClass('closed');
    }
  });
  
});