$(document).ready(function() {
  $('.flag').bind("mouseenter", function(e) {
    $('#flag-expand').attr('src', $(this).attr('src'));
    $('#flag-box').css('top', e.pageY+10).css('left', e.pageX+10).fadeIn(200);
  }).bind("mouseleave", function() {
    $('#flag-box').fadeOut(200);
  }).mousemove(function(e) {
    $('#flag-box').css('top', e.pageY+10).css('left', e.pageX+10);
  });
});
