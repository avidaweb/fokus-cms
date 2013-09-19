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

    // avatar start
    var sbutton = $(fs510).find("button.avatar_select");
    if($(sbutton)[0]){
        $(sbutton).off().on("click", function(e){
            e.preventDefault();

            startImageSelect({
                blackscreen: '',
                selected: function(file){
                    if(!file)
                        return false;

                    $(fs510).find("input[name=avatar]").val(file.id);

                    $(fs510).find("img.avatar").attr("src", file.thumb100h).removeClass("hidden");
                    $(fs510).find("button.avatar_edit").show().data('file', file.id);

                    $(fs510).find("div.box_save").show();
                }
            });
        });
    }

    var nbutton = $(fs510).find("button.avatar_new");
    if($(nbutton)[0]){
        $(nbutton).off().on("click", function(e){
            e.preventDefault();

            startUpload({
                dir: 0,
                images: true,
                blackscreen: '',
                hide_edit: true,
                limit: 1,
                refresh: function(newwp, data){
                    var file = data[0];
                    if(!file)
                        return false;

                    $(fs510).find("input[name=avatar]").val(file.id);

                    $(fs510).find("img.avatar").attr("src", file.thumbnail_url_h100).removeClass("hidden");
                    $(fs510).find("button.avatar_edit").show().data('file', file.id);

                    $(newwp).find("p.close").trigger("click");

                    $(fs510).find("div.box_save").show();
                }
            });
        });
    }

    var ebutton = $(fs510).find("button.avatar_edit");
    if($(ebutton)[0]){
        $(ebutton).off("click").on("click", function(e){
            e.preventDefault();

            var the_file = $(this).data('file');

            startImageEdit({
                blackscreen: '',
                file: the_file,
                file_version: 0,
                callback: function(){
                    var old_src = $(fs510).find("img.avatar").attr("src")+'?random='+Math.random();
                    $(fs510).find("img.avatar").attr("src", old_src);

                    $(sb).show();
                }
            });
        });
    }
    // avatar finish
    
    $(fs510).find("input, select, textarea").off("keyup change").on("keyup change", function(){
        $(fs510).find("div.box_save").show();
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