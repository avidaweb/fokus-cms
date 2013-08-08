
function etabs(auswahl, tabed){
    $.get('sub_settings.php', {
        index : 's430',
        a : auswahl
    }, function(data){ logincheck(data); 
        if(!tabed){
            eltern = $('#e_set_'+auswahl); 
        } else {
            eltern = $(tabed);
        }
        
        $("#einstellungen div.etab").html('');
        $(eltern).html(data);  
         
        $(eltern).find("input, select, textarea").off().on("keyup change", function(){ 
            if($(sb).css("display") == "none") 
                $(sb).show("blind", 400);
        });
        
        $(sb).find("input:first").off().on("click", function(){
            if($(sb).css("display") == "block")
                $(sb).hide("blind", 400);
        });
            
        if($(sb).css("display") == "block") 
            $(sb).hide("blind", 200);
        
        if(auswahl == 'allgemein') e_allgemein();
        else if(auswahl == 'system') e_system();
        else if(auswahl == 'templates') e_templates();
        else if(auswahl == 'sprachen') e_sprachen();
        else if(auswahl == 'dk') e_dk();
        else if(auswahl == 'backup') e_backup();
        else if(auswahl == 'fehler') e_fehler();
    });
}


if($("#fs420")[0] && lastindex == 's420'){
    var fs420 = $("#fs420 div.inhalt"); 
    var sb = $("#fs420 div.box_save");
    
    $("#einstellungen").tabs({ 
        tabTemplate: '<li><a href="#{href}">#{label}</a></li>',
        selected: (rel > 0?(rel - 1):0),
        show: function(event, ui){ 
            var tabed = ui.panel; 
            var auswahl = str_replace('e_set_', '', $(tabed).attr("id")); 
            
            laden($(tabed), true, false, '_white');
            
            etabs(auswahl, tabed);
        },
        create: function(){
            $("#einstellungen #etabs").css({
                "height": "auto",
                "overflow": "inherit"
            });
        }
    });       
}

function e_allgemein(){
    var fs420 = $("#fs420 #e_set_allgemein"); 
    var sb = $("#fs420 div.box_save");
    
    $(fs420).find("td.key a").off("click").on("click", function(){
        var pare = $(this).parent("td");
        $(pare).find("input").show();
        $(pare).find("strong, a").hide();
    });
    
    $(sb).find("input:last").off().on("click", function(){
        var mebutton = $(this);
        $(mebutton).attr("disabled", true);
        
        $.post('sub_settings.php', {
            index: 's431',
            a: 'allgemein',
            email: $("#e_email").val(),
            key: $("#e_key").val()
        }, function(data){ logincheck(data);
            $(mebutton).removeAttr("disabled");
        
            etabs('allgemein');
        }); 
    }); 
}

function e_system(){
    var fs420 = $("#fs420 #e_set_system"); 
    var sb = $("#fs420 div.box_save");
    
    var slider_thumb = $(fs420).find("#slider_thumb");
    var thumb_quality = $(fs420).find("#thumb_quality");
    var warning_no_seo = $("#fs420 div.warning_no_seo");
    
    $(slider_thumb).slider({
        value: $(thumb_quality).val(),
        min: 1,
        slide: function(event, ui) {
            $(thumb_quality).val(ui.value);
        },
        stop: function(event, ui) {
            $(thumb_quality).val(ui.value);
            $(sb).show();
        }
    });
    
    $(sb).find("input:last").off().on("click", function(){
        var mebutton = $(this);
        $(mebutton).attr("disabled", true);
    
        if($(fs420).find("#noseo").val() == 1)
            $(warning_no_seo).show();
        else
            $(warning_no_seo).hide();
    
        $.post('sub_settings.php', {
            index: 's431',
            a: 'system',
            e_h: $(fs420).find("#e_h").val(),
            e_m: $(fs420).find("#e_m").val(),
            login_captcha: $(fs420).find("#login_captcha").is(":checked"),
            noseo: $(fs420).find("#noseo").val(),
            www: $(fs420).find("#www").val(),
            q_template: $(fs420).find("#q_template").val(),
            q_template_mobile: $(fs420).find("#q_template_mobile").val(),
            thumb_quality: $(thumb_quality).val(),
            rewritebase: $(fs420).find("#rewritebase").val(),
            gzip: $(fs420).find("#gzip").val(),
            merge_css: $(fs420).find("#merge_css").val(),
            merge_js: $(fs420).find("#merge_js").val(),
            atitel: $(fs420).find("#atitel").serialize()
        }, function(data){ logincheck(data);
            $(mebutton).removeAttr("disabled");
            
            etabs('system');
        }); 
    }); 
}

function e_templates(){
    var inhalt = $("#fs420 #e_set_templates"); 
    var sb = $("#fs420 div.box_save");
    
    $(inhalt).find("a.changetemplate").off().on("click", function(){
        var template = $(this).attr("rel");
        
        sfrage_show('Wollen Sie dieses Template wirklich aktivieren?');
        $("#sfrage button:last").on("click", function(){
            $.post('sub_settings.php', {
                index: 's431',
                a: 'templates',
                t: template
            }, function(data){ logincheck(data);
                etabs('templates');
                reloadNavigation();
            }); 
        });
    });
}

function e_sprachen(){
    var fs420 = $("#fs420 #e_set_sprachen"); 
    var sb = $("#fs420 div.box_save");
    
    $(fs420).find(".sprachen a").off().on("click", function(){
        var so = $(fs420).find(".sprachen table:last");
        if($(so).css("display") == "none") {
            $(so).show();
            $(this).html("Sprachen&uuml;bersicht schlie&szlig;en").css('background', '#fff url(images/rpfeil_oben.png) no-repeat 2px center');
        } else {
            $(so).hide();
            $(this).html("Sprachen&uuml;bersicht &ouml;ffnen").css('background', '#fff url(images/rpfeil_unten.png) no-repeat 2px center');
        }
    });
    
    $(sb).find("input:last").off().on("click", function(){
        $.post('sub_settings.php', {
            index: 's431',
            a: 'sprachen',
            sprachen: $("#sprachenform").serialize()
        }, function(data){ logincheck(data);
            $(sb).hide("blind", 500); 
            start();
        }); 
    }); 
}

function e_dk(){
    var inhalt = $("#fs420 #e_set_dk"); 
    var sb = $("#fs420 div.box_save");
    
    $(inhalt).find("input, select").on("click", function(){
        $(sb).show();
    });
    
    $(inhalt).find("div.colorSelector").each(function(){
        var aktivcp = $(this);
        var tcolor = $(aktivcp).siblings("input");
        
        $(aktivcp).ColorPicker({
        	color: $(tcolor).val(),
        	onShow: function (colpkr) { 
        		$(colpkr).show();
        		return false;
        	},
        	onHide: function (colpkr) {
        		$(colpkr).hide();
        		return false;
        	},
        	onChange: function (hsb, hex, rgb) {
        		$(aktivcp).css('backgroundColor', '#' + hex);
                $(tcolor).val(hex);
                $(sb).show();
        	}
        });
    });
                    
    $(inhalt).find("input.n_uebersicht").off("click").on("click", function(){
        var spar = $(this).parent("p").siblings("div.show_color");
        
        if($(this).is(":checked")){
            $(spar).hide();
        } else {
            $(spar).show();
        }
        
        $(sb).show();
    });
    
    $(inhalt).find("a.rbutton").each(function(){
        var showmore = $(this).siblings("div.showmore");
        rbutton($(this), $(showmore), 'öffnen', 'schließen');
    });
    
    $(inhalt).find("input.auto_titel").off("click").on("click", function(e){
        var tshow = $(this).parents("div.auto").children("table.select");
        
        if($(tshow).hasClass("is_shown")) {
            $(tshow).removeClass("is_shown").fadeOut();
        } else {
            $(tshow).addClass("is_shown").fadeIn();
        }
    });
    
    $(inhalt).find("select.at").off("change").on("change", function(){
        var related = $(this).data('zeichen'); 
        var rsel = $(inhalt).find('table.select td.'+related);
        
        if($(this).val() == '') {
            $(rsel).addClass("nobg").children("select").slideUp();
        } else {
            $(rsel).removeClass("nobg").children("select").slideDown();
        }
    });
    
    $(inhalt).find("input.show").off("click").on("click", function(e){
        var mytable = $(this).siblings("table.more");
        
        $(sb).show();
        
        if($(mytable).hasClass("rmore")) {
            $(mytable).removeClass("rmore");
        } else {
            var restableP = $(mytable).parents("div.greybox")[0];
            var restable = $(restableP).find("table.rmore"); 
            
            if($(restable).length >= 10){
                e.preventDefault();
                
                sfrage_show('Die Anzeige von Attributen einer Dokumentenklasse ist auf zehn (10) beschränkt. Möchten Sie dieses Attribut trotzdem aktivieren und stattdessen ein anderes automatisch deaktivieren?');
                $("#sfrage button:last").on("click", function(){
                    var removeit = $(restable)[0];
                    $(removeit).removeClass("rmore");
                    $(removeit).siblings("input.show").removeAttr("checked");
                    
                    $(mytable).addClass("rmore");
                    $(mytable).siblings("input.show").attr("checked", "checked");
                });
            } else {
                $(mytable).addClass("rmore");
            }
        }
    });
    
    $(sb).find("input:last").off().on("click", function(){
        $.post('sub_settings.php', {
            index: 's431',
            a: 'dk',
            dka: $(inhalt).find("#dkform").serialize()
        }, function(data){ logincheck(data);
            $(sb).hide("blind", 500); 
            
            reloadNavigation();
        }); 
    }); 
}

function e_backup(){ 
    var inhalt = $("#fs420 #e_set_backup"); 
    var send_status = $(inhalt).find("#bstatus");
    var sinfo = $(send_status).find("div.info");
    var progresssbar = $(send_status).find("#progresssbar");
     
    $(progresssbar).progressbar({
        value: 0
    });
    
    $(inhalt).find("table td.b button").off("click").on("click", function(e){
        e.preventDefault();
        
        var mebutton = $(this);
        $(mebutton).attr("disabled", "disabled");
        
        var count = 0;
        var mail = $(inhalt).find("#bemail").val(); 
        
        $(this).slideUp().remove();
        $(send_status).slideDown();
        $(inhalt).find("input").attr("disabled", "disabled");
        
        function send_bu(){        
            $.post('sub_settings.php', {
                index : 's431',
                a: 'backup',
                email: mail,
                count: count
            }, function(data){  
                var dar = data.split('|||');  console.log(data);
                
                if(dar[0] == 'fehler'){
                    alert("Fehler! Bitte Support kontaktieren: "+dar[0]);
                    dar[0] = 'ende';
                }
                
                if(dar[0] != 'ende'){
                    count = parseInt(dar[0]);
                    
                    var fortschritt = (count / parseInt(dar[1]) * 100);
                    $(progresssbar).progressbar("value", fortschritt); 
                    $(sinfo).append(dar[2]+'... ');
                    
                    setTimeout(function(){
                        send_bu();
                    }, 500);
                } else {
                    $(send_status).find("h2").html('Datenbank-Backup wurden erfolgreich verschickt');
                    $(sinfo).fadeOut().remove();
                    $(progresssbar).remove();
                    
                    $(mebutton).removeAttr("disabled");
                }
            });
        }
        send_bu();
    }); 
    
    
    var dbimport = $(inhalt).find("#db_import");
    var ifehler = $(dbimport).find("div.ifehler");
    var iok = $(dbimport).find("div.iok");
    var dump_file = $(dbimport).find("button[name=dump_file]");
    
    $(dump_file).off().on("click", function(e){
        e.preventDefault();
        
        startUpload({
            dir: 0,
            images: false,
            blackscreen: '',
            hide_edit: true,
            limit: 1,
            refresh: function(neww, data){
                var file = data[0];
                if(!file)
                    return false;
                    
                $.post('sub_settings.php', {
                    index : 's431',
                    a: 'import_dump',
                    file: file.file
                }, function(data){
                    if(data == "error") {
                        alert("Die Datei konnte nicht hochgeladen werden: In der Regel tritt dieses Problem bei nicht gesetzten Ordner-Rechten auf. Falls das Problem häufiger auftritt, kontaktieren Sie bitte ihren fokus-Partner.");
                        $(dump_file).html('Fehler aufgetreten. Bitte nochmal probieren');
                    } else if(data == 'ok') {  
                        $(dump_file).html('fokus-Dump-Datei erfolgreich hochgeladen');
                        $(dbimport).find("input[name=go]").removeAttr("disabled");
                    } else {
                        alert("error: "+data);
                    }
                });
                
                $(neww).find("p.close").trigger("click");
            }
        });
    });
    
    $(dbimport).find("input[name=go]").off().on("click", function(e){
        e.preventDefault();
        var mebutton = $(this);
        
        $(ifehler).hide();
        $(iok).hide();
        
        sfrage_show('Sind Sie sich sicher, dass Sie die aktuelle Datenbank komplett löschen und durch den hochgeladenen fokus-Dump ersetzen möchten?');
        $("#sfrage button:last").on("click", function(){
            $(mebutton).attr("disabled", "disabled");
            
            $.post('sub_settings.php', {
                index : 's431',
                a: 'import',
                f: $(dbimport).serialize()
            }, function(data){
                $(mebutton).removeAttr("disabled");
                
                if(data == 'ok'){
                    $(iok).html('<strong>Import-Vorgang erfolgreich abgeschlossen</strong>'+data).slideDown();
                    $(ifehler).hide();
                } else {
                    $(ifehler).html('<strong>Beim Import-Vorgang trat ein Fehler auf</strong>'+data).slideDown();
                    $(iok).hide();
                }
            });
        });
    });
}

function e_fehler(){ 
    var fs420 = $("#fs420 #e_set_fehler"); 
    var sb = $("#fs420 div.box_save");
    
    $(fs420).find("select").off().on("change", function(){
        $(sb).show();
    });
    
    $(fs420).find("div.errorbox a.goaway").off().on("click", function(e){
        e.preventDefault();
        var error = $(this).data('error');
        
        fenster({
            id: 'n149',
            width: 600,
            blackscreen: 'none',
            cb: function(mf, inhalt){
                $.post('inc_structure.php', {
                    index: 'n140',
                    error: error
                }, function(data){
                    logincheck(data);
                    $(inhalt).html(data);
                    
                    var sd = $(mf).find("#struk_doks");
                    $(inhalt).find("div.sprachbox").remove();
                    
                    strukturelement_strukdok(mf, sd, 0, '', error);
                });
            }
        });
    });
    
    $(sb).find("input:last").off().on("click", function(){
        $.post('sub_settings.php', {
            index: 's431',
            a: 'error_pages',
            fa: $(fs420).find("form").serialize()
        }, function(data){ 
            logincheck(data); 
        }); 
        
        $(sb).hide();
    }); 
}





if($("#fs490")[0] && lastindex == 's490'){
    var fs490 = $("#fs490");
    var save = $(fs490).find("div.box_save");
    
    $(fs490).find("article input").off().on("click", function(){
        $(save).show();
    });
    
    $(fs490).find("div.tab").children("p").find("input").on("click", function(){
        var sib = $(this).parent("p").siblings("aside"); 
        
        if($(sib).is(":hidden"))
            $(sib).show();
        else
            $(sib).hide();
        
        set_button(fs490, save);
    });
    
    
    $(save).find("input.bs2").off().on("click", function(e){
        e.preventDefault();
        
        var savedform = $(fs490).find("form").serialize();
        $(fs490).find("p.close").trigger("click");
        
        fenster({
            id: 's495',
            width: 700,
            blackscreen: '',
            cb: function(nwindow, ncontent){
                
                $.post('sub_settings.php', {
                    index: 's495',
                    f: savedform
                }, function(data){ 
                    logincheck(data); 
                    
                    $(ncontent).html(data);
                    save_button(nwindow);
                    
                    var progress = $(ncontent).find("div.progress");
                    var info = $(ncontent).find("ul.info");
                    var todo = parseInt($(progress).data('max'));
                    var psteps = parseInt((100 / todo));
                    
                    $(progress).progressbar({
            			value: 0
            		});
                    
                    function doPure(nr){
                        $.post('sub_settings.php', {
                            index: 's496',
                            f: savedform,
                            todo: todo,
                            nr: nr
                        }, function(data){
                            $(progress).progressbar('value', (nr * psteps));
                            $(info).append(data);
                            
                            if(nr < todo){
                                setTimeout(function(){
                                    doPure((nr + 1));
                                }, 1000);
                            } else {
                                $(progress).progressbar('value', 100);
                                
                                $(ncontent).find("div.box_save").show();    
                            }
                        });
                    }
                    
                    setTimeout(function(){
                        doPure(1);
                    }, 100);
                });
            }
        });  
    });
}



if($("#fextensions")[0] && lastindex == 'extensions'){
    var exto = $("#fextensions");
    var db = $(exto).find("#extension-dashboard");
    
    function loadExt(){
        $(db).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
        
        $.post('sub_settings.php', {
            index: 'extensions-load'
        }, function(data){
            $(db).html(data);
            
            $(db).find("a.action").off().on("click", function(){
                var id = $(this).data('id');
                var action = $(this).data('action');
                
                if(action == 'activate'){
                    doExtAction(id, action);
                } else {
                    sfrage_show('Wollen Sie diese Erweiterung wirklich deaktivieren? Von der Erweiterung gespeicherte Daten gehen dabei eventuell verloren.');
                    $("#sfrage button:last").on("click", function(){
                        doExtAction(id, action); 
                    });    
                }
            });
        });
    }
    
    function doExtAction(id, action){
        $(db).find("a.action").off("click");
        
        $.post('sub_settings.php', {
            index: 'extensions-action',
            action: action,
            id: id
        }, function(data){
            loadExt();
            reloadNavigation();
        });
    }
    
    loadExt();
}