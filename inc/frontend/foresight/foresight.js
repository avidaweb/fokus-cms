$(function(){
   
    $("body").append('<div id="fks_foresight"></div>');    
    
    $.get(foresight_ordner+'inc/frontend/foresight/foresight.php', {
        url: foresight_url
    }, function(data){
        $("#fks_foresight").html(data);
        
        $("#fks_foresight .fks_datum").datepicker({
    		showOn: 'button',
    		buttonImage: foresight_ordner+'fokus/images/kalender.jpg',
    		buttonImageOnly: true,
            dateFormat: 'dd.mm.yy'
    	});
    });
});