$(document).ready(function() {
  $('#form-gather').ajaxForm({
    beforeSubmit:function() {
      $('input').attr('readonly', 'readonly');
      $('#status-wrapper').fadeIn(1000);
      statusRefresh();
      launchTimer();
    },
    success:function() {
      $('input').attr('readonly', '');
      $('#status-wrapper').fadeOut(1000);
      document.location = "/services/endospider-head/";
    }
  });
  $('#ajax').attr('value', '1');
});

function statusRefresh() {
  var done;
  var remaining;
  var newDone;
  $.getJSON("/services/endospider-head/gather/status", function(json) {
    newDone = json.done;
    if (newDone != done) {
      $('#status-progress-done').animate({
      width:(newDone+'%')}, 250);
      done = newDone;
    }
    $('#status-time-remaining').html(remaining);
    if (done != 100) {
      setTimeout(statusRefresh, 500);
    }
  });
}

var startTime; 
function launchTimer() {
  startTime = new Date().getTime();
  $('#status-time-passed').html("00:00");
  setTimeout(refreshTimer, 500);
}

function refreshTimer() {
  var currentTime = Math.floor(((new Date()).getTime() - startTime) / 1000);
  minutes = Math.floor(currentTime / 60);
  seconds = Math.floor(currentTime % 60);
  if (seconds < 10) seconds = "0" + seconds;
  $('#status-time-passed').html(minutes + ":" + seconds);
  setTimeout(refreshTimer, 500);
}
