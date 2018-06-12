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
  // Boton submit
  $("#crud-form").submit(function(event) {
    if (!$("#crud-save-btn").hasClass("edit")) {
      if ($("#logo-input").get(0).files.length === 0) {
        showError("La cervecería debe tener un logo");
        return false;
      } else {
        // Chequear propiedades del logo
      }
      if ($("#picture-input").get(0).files.length === 0) {
        showError("La cervecería debe tener una foto");
        return false;
      } else {
        // Chequear propiedades de la foto
      }
    }
    if ($("#crud-form").find("input[name='nombre']").val() == "") {
      showError("La cervecería debe tener un nombre");
      return false;
    }
    if ($("#crud-form").find("input[name='direccion']").val() == "") {
      showError("La cervecería debe tener una direccion");
      return false;
    }
    // Chequear horarios
    var valid = true;
    $(".daytime-box").each(function(i) {
      var weekdays = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];
      if ($(this).children().first().children(".day-check").is(":checked")) {
        var open = $(this).children(".daytime-input-container").children(".daytime-input:nth-child(1)").val();
        var close = $(this).children(".daytime-input-container").children(".daytime-input:nth-child(2)").val();
        if (open == "") {
          if (i != 7) {
            showError("El día " + weekdays[i] + " no tiene horario de apuertura");
          } else {
            showError("El happy hour no tiene horario de inicio");
          }
          valid = false;
          return false;
        }
        if (close == "") {
          if (i != 7) {
            showError("El día " + weekdays[i] + " no tiene horario de cierre");
          } else {
            showError("El happy hour no tiene horario de finalización");
          }
          valid = false;
          return false;
        }
        if (open.localeCompare(close) == 0) {
          if (i != 7) {
            showError("El día " + weekdays[i] + " tiene el mismo horario de apertura y cierre");
          } else {
            showError("El happy hour tiene el mismo horario de inicio y finalización");
          }
          valid = false;
          return false;
        }
      }
    });
    if (!valid) {
      return false;
    }
    // Deshabilitar botones luego de enviar el formulario
    $("#crud-save-btn").attr("disabled", "disabled");
    $("#crud-save-btn").css("cursor", "default");
    $("#crud-cancel-btn").attr("disabled", "disabled");
    $("#crud-cancel-btn").css("cursor", "default");
  });
  // Boton cancelar
  $("#crud-cancel-btn").click(function(event) {
    event.preventDefault();
    window.location.href = "/admin";
  });
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

function showError(text) {
  if ($(".crud-error-message")[0]) {
    $(".crud-error-message").html(text);
  } else {
    $("#main-container").prepend("<span class='crud-error-message'>" + text + "</span>");
  }
  $("html, body").animate({scrollTop: 0}, 1000);
}

function duplicateId(element) {
  var nombre = element;
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: '/checkid',
    type: "POST",
    async: false,
    cache: false,
    timeout: 30000,
    data: {nombre: nombre},
    dataType: "json",
    success: function(res) {
      if (res.exists) {
          alert("El nombre existe");
      } else {
          alert("El nombre no existe");
      }
    },
    error: function (jqXHR, exception) {
      alert("Error");
    }
  });
}
