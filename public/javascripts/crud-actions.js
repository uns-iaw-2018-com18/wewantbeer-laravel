$(function() {
  // Configuracion del timepicker
  $(".timepicker").timepicker({
    timeFormat: "HH:mm",
    dynamic: true,
    scrollbar: true
  }).keypress(function(event) {
    event.preventDefault();
  });
  // File input
  $(document).on('change', '.file-field input[type="file"]', function() {
    var file_field = $(this).closest('.file-field');
    var path_input = file_field.find('input.file-path');
    var files = $(this)[0].files;
    var file_names = [];
    for (var i = 0; i < files.length; i++) {
      file_names.push(files[i].name);
    }
    path_input.val(file_names.join(", "));
    path_input.trigger('change');
  });
  // Dropdown select del tipo de telefono
  $(".dropdown-item").click(function(event) {
    event.preventDefault();
    $("#phone-select").text($(this).text());
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
});
