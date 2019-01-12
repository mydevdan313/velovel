$(document).ready(function() {


  // Обработка ввода поисковой фразы в поле поиска
  $('body').on('keyup', 'input[name=search]', function() {

    var text = $(this).val();
    if (text.length >= 2) {
      $.ajax({
        type: "POST",
        url:  mgBaseDir + "/catalog",
        data: {
          fastsearch: "true",
          text: text
        },
        dataType: "json",
        cache: false,
        success: function(data) {
          if ('success' == data.status && data.item.items.catalogItems.length > 0) {
            $('.fastResult').html(data.html);
            $('.fastResult').show();
            $('.wraper-fast-result').show();
          } 
          else {
            $('.fastResult').hide();
          }
        }
      });
    } else {
      $('.fastResult').hide();
    }
  });

  // клик вне поиска
  $(document).mousedown(function(e) {
    var container = $(".wraper-fast-result");
    if (container.has(e.target).length === 0 && $(".search-block").has(e.target).length === 0) {
      container.hide();
    }
  });

});  