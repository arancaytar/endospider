var venn = "";

$(document).ready(function() {
  $.html('<div id="nation-box"></div>');
  $('a[rel=nation-link]').click(function(e) {
    var nation = $(this).attr('class').substr(4);
    $('#nation-box').css('top', e.pageY).css('left', e.pageX)
    .html(
'<ul>'+
'  <li><a href="http://www.nationstates.net/' + nation + '">View on NationStates</a></li>'+
'  <li><a href="relations/out/' + nation + '">Endorsements given out</a></li>'+
'  <li><a href="relations/in/' + nation + '">Endorsements received</a></li>'+
'  <li><a href="javascript:" id="vennlink">Add to Venn Diagram</a>'+
'</ul>');
    if (venn) {
      $('#vennlink').click(function(e){
      document.location = $('base').attr('href') + '/venn/' + venn + '/' + nation;
      });
      $('#vennlink').html("Venn diagram with " + venn);
    }
    else {
      $('#vennlink').click(function(e){
        venn = nation;
      });
    }
    $('#nation-box').fadeIn(500);
  });
});
