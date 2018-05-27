$(function() {
  // Cuando se ingresa un caracter en el campo
  $(".login-signup-input").keydown(function() {
    $(this).next("a").hide();
    $(this).css("background-color", "white");
  });
  // Cuando se quiere enviar el formulario de login
  $("#login-form").submit(function() {
    var emptyFields = 0;
    $(".login-signup-input").each(function() {
      if ($(this).val() == "") {
        $(this).next("a").show();
        $(this).css("background-color", "#E6E6E6");
        emptyFields++;
      }
    });
    if (emptyFields > 0) {
      if ($(".login-signup-error-message")[0]) {
        $(".login-signup-error-message").remove();
      }
      return false;
    }
  });
  // Desactivar evento del boton de aviso
  $(".input-warning-icon").click(function() {
    return false;
  });
  // Mostrar tooltip
  $("[data-toggle='tooltip']").tooltip();
});
