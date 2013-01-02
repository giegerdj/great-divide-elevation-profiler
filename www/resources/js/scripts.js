$(document).ready(function(){
  
  if( $('body').width() <= 768 ) {
    $('#stat-container,#profile-container').addClass('toggle-closed');
    $('#stat-container .toggle').find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
    $('#profile-container .toggle').find('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
  }
  
  $('#stat-container .toggle, #profile-container .toggle').click(function(e){
    e.preventDefault();
    var parent = $(this).parent();
    
    if( $(this).find('i').hasClass('icon-chevron-down') ) {
      $(this).find('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
    } else {
      $(this).find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
    }
    
    if( parent.hasClass('toggle-closed') ) {
      parent.removeClass('toggle-closed');
    } else {
      parent.addClass('toggle-closed');
    }
  });
  
});