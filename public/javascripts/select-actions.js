$(function() {
  $(".delete").click(function(event) {
    event.preventDefault();
    var action = $(this).attr("href");
    getConfirmation();
    $("#confirmation-yes").click(function(event) {
      event.preventDefault();
      $("#confirmation-yes").attr("disabled", true);
      $("#confirmation-no").attr("disabled", true);
      $("#confirmation-modal").addClass("animated bounceOut").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function() {
        $("#modal-bg").fadeOut();
      });
      window.location.href = action;
    });
    $("#confirmation-no").click(function(event) {
      event.preventDefault();
      $("#confirmation-yes").attr("disabled", true);
      $("#confirmation-no").attr("disabled", true);
      $("#confirmation-modal").addClass("animated bounceOut").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function() {
        $("#modal-bg").fadeOut();
      });
    });
  });
});

function getConfirmation() {
  $("body").prepend("<div id='modal-bg'><div id='confirmation-modal'><span id='confirmation-top-text'>¿Estás seguro?</span><button id='confirmation-no' class='confirm-btn'>NO</button><button id='confirmation-yes' class='confirm-btn'>SI</button><span id='confirmation-bottom-text'>Esta acción no puede ser deshecha</span><span id='modal-logo'></span></div></div>");
	$("#modal-bg").show();
	$("#confirmation-modal").addClass("animated bounceInDown").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function() {
	  $(this).removeClass();
	});
}
