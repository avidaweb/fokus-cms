if($("#fs500")[0] && lastindex == 's500'){
    var fs500 = $("#fs500 div.inhalt"); 
    var sb = $("#fs500 div.box_save");
    
    var bild = $(fs500).find("td.bild");
    $(bild).find("select.bild_wt").off("change").on("change", function(){
        var elt = $(this).parent("td");
        
        if($(this).val() == '0'){
            $(elt).find("input.bild_w").show().attr("disabled", false);
            $(elt).find("input.bild_h").show().attr("disabled", false);
            $(elt).find("span").show();
        } else if($(this).val() == '1'){
            $(elt).find("input.bild_w").show().attr("disabled", false);
            $(elt).find("input.bild_h").hide().attr("disabled", true);
            $(elt).find("span").show();
        } else {
            $(elt).find("input.bild_w").hide().attr("disabled", true);
            $(elt).find("input.bild_h").hide().attr("disabled", true);
            $(elt).find("span").hide();
        }
    });
    
    $(fs500).find("input, select, textarea").off("keyup").on("keyup change", function(){ 
        if($(sb).css("display") == "none") 
            $(sb).show("blind", 400);
    });
    
    $(sb).find("input:last").off().on("click", function(){
        $(sb).hide();
        
        neues_fenster_task = $(fs500).find("select[name=fenster_neu]").val();
        subnavi_click = $(fs500).find("select[name=subnavi]").val();
        
        $.post('sub_indiv.php', {
            index: 's505',
            f: $(fs500).find("#workflowo").serialize()
        }, function(data){ 
            logincheck(data);
            
            getWidgetMenu(false);
        
            // Menü neu laden
            $.get('nav.php', {
                only_navi: true
            }, function(data){
                $("nav").replaceWith(data);
                init();
            });  
            
            $("#fs500 p.close").trigger("click");
        }); 
    }); 
}

if($("#fs510")[0] && lastindex == 's510'){
    var fs510 = $("#fs510 div.inhalt"); 
    var sb = $("#fs510 div.box_save");
    
    $(fs510).find("#new_pw_be").off().on("click", function(){
        if($(this).is(":checked"))
            $(this).nextAll("div.getnewpw").slideDown();
        else
            $(this).nextAll("div.getnewpw").slideUp();
    });
    
    $(fs510).find("#pw_klartext").off().on("click", function(){
        var elt = $(this).parents("div.getnewpw")[0];
        
        if($(this).is(":checked")){
            $(elt).find("input.pw").hide().val('');
            $(elt).find("input.pw_t").show().focus();
        } else {
            $(elt).find("input.pw").show().focus();
            $(elt).find("input.pw_t").hide().val('');
        }
    });
    
    $(fs510).find("a#n535").off().on("click", function(){
        neu($(this));
    });
    
    $(fs510).find("input, select, textarea").off("keyup change").on("keyup change", function(){ 
        if($(sb).css("display") == "none") 
            $(sb).show("blind", 400);
    });
    
    $(sb).find("input:last").off().on("click", function(){
        $(sb).hide();
        
        $.post('sub_indiv.php', {
            index: 's515',
            f: $(fs510).find("#persoein").serialize()
        }, function(data){ logincheck(data);
            $("#fs510 p.close").trigger("click");  
        }); 
    }); 
}