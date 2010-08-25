var venn = "";
function i2d(name) {
  return ucwords(name.replace('_', ' '));
}

function ucwords(str) {
  return (str + '').replace(/^(.)|\s(.)/g, function ($1) {
    return $1.toUpperCase();
  });
}

$(document).ready(function() {
  $('a[rel=nation-link]').click(function(e) {
    var nation = $(this).attr('class').substr(5);
    $('#nation-box').css('top', e.pageY).css('left', e.pageX)
    .html(
'<strong>' + $(this).html() + '</strong><br />' +
'<ul>'+
'  <li><a class="nation-link-type" href="http://www.nationstates.net/' + nation + '">NationStates Spotlight</a></li>'+
'  <li><a class="nation-link-type" href="relations/' + nation + '">All Relations</a></li>'+
'  <li><a class="nation-link-type" href="javascript:" id="vennlink">Add to Venn Diagram</a></li>'+
'  <li><a class="nation-link-type" href="tart/new/' + nation + '">Tart for this nation</a></li>'+
'</ul>');
    $('.nation-link-type').click(function() {
      $('#nation-box').fadeOut(500);
    });
    if (venn) {
      $('#vennlink').click(function(e){
      document.location = $('base').attr('href') + '/venn/' + venn + '/' + nation;
      });
      $('#vennlink').html("Venn diagram with <strong><em>" + i2d(venn) + "</em></strong>");
    }
    else {
      $('#vennlink').click(function(e){
        venn = nation;
      });
    }
    $('#nation-box').fadeIn(500);
    setTimeout(hideBox, 5000);
    return false;
  });
  $('a[rel=nation-link]').dblclick(function(e) {
     document.location = $(this).attr('href'); 
  });
});

function hideBox() {
  $('#nation-box').fadeOut(500);
}
