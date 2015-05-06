/* jshint
devel: true,
browser: true,
jquery: true
*/
var subpages = []; //globale array met URL's van de subpages

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
        var urlSubpage = $eventItem.attr('href');
        subpages.push(urlSubpage);
        
        
        htmlString += '<section class="event">'; //style="background: url(' + imgSrc +');">';
        htmlString += '<img src="' + imgSrc + '">';
        htmlString += '<h1>' + $eventItem.find('.title').text() + '</h1>';
        htmlString += '<h2>' + date + '</h2>';
        //htmlString += '<p>' + genre + '</p>'; //genre zit in een subpagina, dus kan hier niet worden toegevoegd
        htmlString += '</section>';
        
        loadSubPage(urlSubpage);
    });
    
    $(document.body).append(htmlString);
};

var loadSubPage = function(urlSubpage){
    var sendData = {url: urlSubpage};
    $.ajax({
        url: 'crosscall.php',
        data: sendData,
        type: 'POST',
        error: function(err) {console.dir(err);},
        success: successHandlerSubPage
    });
};

var successHandlerSubPage = function(data){
    var loadedUrl = this.data.substr(4); //url huidige ajax request
    loadedUrl = decodeURIComponent(loadedUrl).replace(/\++/g, ' '); //decoding special chars in url
    var index;
    
    $.each(subpages, function(i, urlSubpage){
        if(loadedUrl === urlSubpage) {
            index = i;
            return false; //indien gevonden, stop each loop
        }
    });
    
    var $html = $(data);
    var genre = $html.find(".info:contains('Genre')").html();
    $(".event").eq(index).append('<p>' + genre + '</p>');
};