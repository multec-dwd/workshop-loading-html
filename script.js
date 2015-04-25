/* jshint
devel: true,
browser: true,
jquery: true
*/

$(document).ready(function() {
    var sendData = {url: 'http://www.abconcerts.be/nl/agenda/'};
    $.ajax({
        url: 'crosscall.php',
        data: sendData,
        type: 'POST',
        error: function(err) {console.dir(err);},
        success: successHandler
    });

});

var successHandler = function(data) {
    var $html = $(data); 
    //window.$html = $html; //make $html global for testing
    
    var htmlString = '';
    
    $html.find('.item').each(function(i, eventItem){
        
        var $eventItem = $(eventItem);
        var imgSrc = $eventItem.find('img').attr('src')
                        .replace(/overview/, 'header')
                        .replace(/small/,'large');
        var date = $eventItem.parent().parent().prev().text();
        
        
        htmlString += '<section class="event">'; //style="background: url(' + imgSrc +');">';
        htmlString += '<img src="' + imgSrc + '">';
        htmlString += '<h1>' + $eventItem.find('.title').text() + '</h1>';
        htmlString += '<h2>' + date + '</h2>';
        htmlString += '</section>';
    });
    
    $(document.body).append(htmlString);
};