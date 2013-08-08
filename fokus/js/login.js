$(function() {
    $("#index-intro").animate({
        'margin-top': '0px'
    }, 1200, 'swing');
    
    if(navigator.cookieEnabled == false){
        $("#anmeldung").before('<div class="box fehlerbox"><strong>Sind Cookies aktiviert?</strong>Es scheint so, als wären in Ihrem Browser keine Cookies aktiviert. <br />Um sich erfolgreich anzumelden, werden allerdings Cookies benötigt.</div>');    
    }
   
    $("table.fenster").css({
        'top': ($(window).height() / 2 - $("table.fenster").height() / 2 - 100) + 'px',
        'left': ($("#main").width() / 2 - $("table.fenster").width() / 2) + 'px'
    }).draggable({
        handle : 'h1'
    }); 
    
    setTimeout(function(){ 
        $("table.fenster").fadeIn(1000, function(){
            if(typeof(bilder_preload) != "undefined")
                start_preload();
        }); 
    }, 1000);
        
    var sb = $("div.box_save"); 
    $("input").on("change keyup focus", function(){
        if($(sb).css("display") == "none")
            $(sb).show("blind", 500);
    }); 
    
    $(sb).find("input[name=loginbutton]").off("click").on("click", function(e){
        e.preventDefault(); 
        var selfbutton = $(this);
        $(selfbutton).attr("disabled", "disabled");
        
        var loadi = $('<div class="box"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></div>');
        $("h1").after(loadi);
        
        $.post('enter.php', {
            login: 'true',
            ajax_hash: $("input[name=ajax_hash]").val(),
            name: $("input[name=name]").val(),
            mname: $("select[name=mname]").val(),
            mehrere: $("input[name=mehrere]").val(),
            pw: $("input[name=pw]").val(),
            captcha: $("input[name=captcha]").val(),
            cp1: $("input[name=cp1]").val(),
            cp2: $("input[name=cp2]").val(),
            cpt: $("input[name=cpt]").val(),
            ajaxcheck: 'true'
        }, function(data){ 
            $("div.fehlerbox").remove();
            
            if(data == 'ok'){
                $("form").submit();
            } else {
                $(selfbutton).removeAttr("disabled");
                $(loadi).remove();
                $("h1").after(data);
            }
        });
    });
    
    var name_name = $('#name-name');
    var password_name = $('#password-password');
    
    $.fn.image = function(src, f){ 
       return this.each(function(){ 
         var i = new Image(); 
         i.src = src; 
         i.onload = f; 
         this.appendChild(i);
      }); 
    } 
    
    function start_preload(){
        var preload = $("#preload");
        var prespan = $(preload).find("span.a");
        var preall = parseInt($(preload).find("span.b").text());
        var limg = $("#loadedimages");
        
        $(preload).fadeIn();
        pgeladen = 0;
        
        for(var x=0; x<bilder_preload.length; x++){ 
            $(limg).image(bilder_preload[x], function(){
                if(pgeladen < preall){
                    pgeladen ++;
                    $(prespan).html(pgeladen);
                }
            });
        }
    }
    
});