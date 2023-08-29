jQuery(document).ready(function ($) {
  $("#manual-update-button").on("click", function () {
    var data = {
      action: "manual_update_reviews",
    };
    $.post(ajaxurl, data, function (response) {
      alert("Reviews updated!");
    });
  });
});
