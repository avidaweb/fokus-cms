$(function() {
   
    $("table.fenster").css({
        'left': ($("#main").width() / 2 - $(".fenster").width() / 2) + 'px'
    }).draggable({
        handle : 'h1',
        opacity: 0.9
    });
    
    $("#step1 button").off().on("click", function(e){
        e.preventDefault();
        
        $.post('ajax.php', {
            a: '1',
            fa: $("#i_form").serialize()
        }, function(data){
            if(data == 'ok'){
                $("#step1 table").fadeOut();
                $("#step1_error").remove(); 
                
                $("#step1").append('<div id="step1_ok" class="ok"></div>');     
                $("#step1_ok").html('<strong>Zugangsdaten korrekt</strong>');
                
                $("#step2").slideDown();
            } else {
                $("#step1_error").remove(); 
                $("#step1").append('<div id="step1_error" class="warnung"></div>');     
                $("#step1_error").html('<strong>Zugangsdaten falsch</strong>'+(data == 'fehler1'?'Der Benutzername oder das Passwort sind nicht korrekt':'Die eingegebene Datenbank existiert nicht'));
            }
        });
    });
    
    $("#step2 button").off().on("click", function(e){
        e.preventDefault();
        
        $.post('ajax.php', {
            a: '2',
            fa: $("#i_form").serialize()
        }, function(data){
            if(data == 'ok'){
                $("#step2 table").fadeOut();
                $("#step2_error").remove(); 
                
                $("#step2").append('<div id="step2_ok" class="ok"></div>');     
                $("#step2_ok").html('<strong>Benutzerdaten korrekt</strong>');
                
                $("#step3").slideDown();
            } else {
                $("#step2_error").remove(); 
                $("#step2").append('<div id="step2_error" class="warnung"></div>');     
                $("#step2_error").html('<strong>Benutzerdaten falsch</strong>'+data);
            }
        });
    });
    
    $("#step3 button").off().on("click", function(e){
        e.preventDefault();
        
        $.post('ajax.php', {
            a: '3',
            fa: $("#i_form").serialize()
        }, function(data){
            if(data == 'ok'){
                $("#step3 table").fadeOut();
                $("#step3_error").remove(); 
                
                $("#step3").append('<div id="step3_ok" class="ok"></div>');     
                $("#step3_ok").html('<strong>Optionale Einstellungen korrekt</strong>');
                
                $("#step4").slideDown();
            } else {
                $("#step3_error").remove(); 
                $("#step3").append('<div id="step3_error" class="warnung"></div>');     
                $("#step3_error").html('<strong>Optionale Einstellungen falsch</strong>'+data);
            }
        });
    });
    
    $("#step4 button").off().on("click", function(e){
        e.preventDefault();
        $("#step4 button").replaceWith('<img src="../fokus/images/loading_white.gif" alt="Wird geladen" />');
        
        $.post('ajax.php', {
            a: '4',
            fa: $("#i_form").serialize()
        }, function(data){
            $("#step4 img").hide();
            $("#step4").append(data);
        });
    }); 
    
    
    $("input[type=text], input[type=password], input[type=email]").off("keypress").on("keypress", function(e){
        if(e.keyCode == 13) {
            $(this).parents("fieldset:first").find("button").trigger("click");
            e.preventDefault();
        }
    });
    
});