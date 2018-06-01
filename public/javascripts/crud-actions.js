var logoSrc, pictureSrc;

$(function() {
  logoSrc = $("#logo-img").attr("src");
  pictureSrc = $("#picture-img").attr("src");
  // Configuracion del timepicker
  $(".timepicker").timepicker({
    timeFormat: "HH:mm",
    dynamic: true,
    scrollbar: true
  }).keypress(function(event) {
    event.preventDefault();
  });
  // File input
  $("#logo-input").change(function() {
    previewImage(this, $(this).parent().parent().siblings(".preview-img"), logoSrc);
  });
  $("#picture-input").change(function() {
    previewImage(this, $(this).parent().parent().siblings(".preview-img"), pictureSrc);
  });
  // Dropdown select del tipo de telefono
  $(".dropdown-item").click(function(event) {
    event.preventDefault();
    $("#phone-select").text($(this).text());
    $("#crud-phone-value").attr("value", $(this).attr("value"));
  });
  // Funcionamiento del check del horario de un dia
  $(".day-check").click(function() {
    $(this).parent().siblings().children().each(function() {
      if ($(this).attr("disabled") != undefined) {
        $(this).removeAttr("disabled");
      } else {
        $(this).attr("disabled", "disabled");
      }
    });
  });
  // Deshabilitar boton guardar luego de enviar el formulario
  $("#crud-form").submit(function() {
    $("#crud-save-btn").attr("disabled", "disabled");
    $("#crud-save-btn").css("cursor", "default");
  });
  // Boton cancelar
  $("#crud-cancel-btn").click(function(event) {
    event.preventDefault();
    window.location.href = "admin";
  })
});

function previewImage(input, img, src) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(event) {
      img.attr("src", event.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  } else {
    img.attr("src", src);
  }
}
