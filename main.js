$ = jQuery;
$('document').ready(function() {
    function widthsToWidest(element, minWidth) {
      if (window.matchMedia("(min-width: "+minWidth+")").matches) {
          var maxWidth = 0;

          $(element).each(function(){
             if ($(this).outerWidth() > maxWidth) { maxWidth = $(this).outerWidth(); }
          });

          $(element).outerWidth(maxWidth+12);
      }
    }

    widthsToWidest('.cas-table__el--first', '0px');
    widthsToWidest('.cas-table__el--second', '0px');
});