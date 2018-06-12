$(function() {
  $("#login-btn").click(function(event) {
    event.preventDefault();
    $(this).parent().submit();
  });
});
