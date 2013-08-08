function lade_strukturverwaltung(){ 
    var sver = $("#fn100 #strukturverwaltung");
    var stable = $(sver).find("table");
    var colspan = $(stable).find("tr.first td").length;
        
    function lade_strukturverwaltung_inhalt(){
        $(stable).find("tr").not(".first").remove();
        $(stable).append('<tr class="loadit"><td colspan="'+colspan+'"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>');
        
        $.post('inc_structure.php', {
            index: 'n101'
        }, function(data){  
            logincheck(data);
            
            $(stable).find("tr.loadit").remove();
            $(stable).append(data);
            
            function strukturverwaltung_inhalt_do(id, task){
                $.post('inc_structure.php', {
                    index: 'n102',
                    task: task,
                    id: id
                }, function(data){
                    logincheck(data);
                    lade_strukturverwaltung_inhalt();
                });
            }
            
            $(stable).find("a.goto").off().on("click", function(){
                var mgoto = $('<a class="inc_structure" id="n120" rel="0"></a>');
                neu($(mgoto));
            });
            
            $(stable).find("a.opt").off().on("click", function(){
                var id = $(this).data('id');
                var task = $(this).data('task');
                
                if(task == 'del'){
                    sfrage_show('Wollen Sie diese Struktur wirklich in den Papierkorb verschieben?');
                    $("#sfrage button:last").on("click", function(){
                        strukturverwaltung_inhalt_do(id, task);
                    });
                } else {
                    strukturverwaltung_inhalt_do(id, task);    
                }
            });
        });
    }
    lade_strukturverwaltung_inhalt();
    
    $(sver).find("button").off().on("click", function(e){
        e.preventDefault();
        
        fenster({
            id: 'n105',
            width: 540,
            cb: function(nwin, ninhalt){
                $.post('inc_structure.php', {
                    index: 'n105'
                }, function(data){
                    logincheck(data);
                    
                    $(ninhalt).html(data);
                    save_button(nwin);
                    
                    var sb = $(ninhalt).find("div.box_save");
                    $(ninhalt).find("input").focus().off("keyup").on("keyup", function(){
                        $(sb).slideDown();
                    });
                    
                    $(sb).find("input.bs2").off().on("click", function(e){
                        e.preventDefault();
                        $(this).attr("disabled", "disabled");
                        
                        $.post('inc_structure.php', {
                            index: 'n106',
                            titel: $(ninhalt).find("input[name=titel]").val(),
                            clone: $(ninhalt).find("select[name=clone]").val()
                        }, function(data){
                            logincheck(data);
                            $(nwin).find("p.close").trigger("click");
                            lade_strukturverwaltung_inhalt();
                        });
                    });
                });    
            }
        });
    });
}

if($("#fn100")[0] && lastindex == 'n100') {
    lade_strukturverwaltung();
}




// Strukturelemente bearbeiten
if(($("#fn120")[0] && lastindex == 'n120') || ($("#fn130")[0] && lastindex == 'n130')){
    struktur_start({});
}



// Menues bearbeiten
if(($("#fn170")[0] && lastindex == 'n170')){
    menues_start();
}

function menues_start(){
    var vmenues = $("#fn170 #vmenues");
    
    $(vmenues).find("a").off().on("click", function(){
        var menue = $(this).data('id');
        
        fenster({
            id: 'n171',
            width: 750,
            blackscreen: '',
            cb: function(mf, inhalt){
                $.post('inc_structure.php', {
                    index: 'n171',
                    menue: menue
                }, function(data){
                    logincheck(data);
                    $(inhalt).html(data);
                    
                    menue_start({
                        menue: menue    
                    });
                });
            }
        });
    });
}  

function open_menue(mid, menue){
    fck_link(0, 0, 0, mid);
}

function menue_start(sopt){
    if(!$("#fn171")[0])
        return false;
        
    var cont = $("#fn171 div.inhalt");
        
    var loadme = $(cont).find("div.loadme");
    var baum = $(cont).find("div.baum");
    var canvas = $(cont).find("canvas");
    
    if(!sopt.menue)
        sopt.menue = $(cont).find("input[name=menue]").val();
    
    $.jCanvas({
        strokeStyle: "#999",
        strokeWidth: 1,
        strokeJoin: "miter",
    });
    
    function load_struktur(newobj){
        $(loadme).show();
        
        $.get('inc_structure.php', {
            index: 'n172',
            open: newobj,
            menue: sopt.menue
        }, function(data){ 
            logincheck(data);
            $(baum).html(data);
            
            $(loadme).hide();
            
            var zweig = $(baum).find("div.zweig");
            var row = $(zweig).find("div.row");
            var whites = $(row).find("div.white");
            var more = $(row).find("div.more");
            var mopt = $(more).find("div.opt");
                
            // Hinzufügen Frisch per Button
            $(cont).find("button.new").off().on("click", function(e){
                $(this).off("click");
                e.preventDefault();
                new_child(0, 0, sopt.menue);
            });
            
            if(!$(zweig)[0])
                return false;
            
            $(canvas).attr("height", $(baum).height());
            
            // Breite der Bereiche anpassen
            $(row).each(function(){
                var white = $(this).find("div.white");
                var tmore = $(this).find("div.more");
                $(tmore).width(($(this).width() - $(white).width()));
            });
            
            $(zweig).not(".zweig_0").hide();
            $(more).hide();
            
            // Graue Balken bei Hover anzeigen
            $(row).off("mouseenter mouseleave").on("mouseenter", function(){
                $(this).find("div.more").show();
            }).on("mouseleave", function(){
                $(this).find("div.more").hide().find("div.optarea").hide();
            });
            
            // Weitere Elemente ausklappen lassen
            $(row).find("a.klappen").off().on("click", function(){
                var szweige = $(this).parents("div.row:first").siblings("div.zweig");
                
                if($(this).data('open') == 'true'){
                    $(szweige).hide();
                    load_canvas();
                    $(this).data('open', 'false').children("strong").html('aufklappen');
                } else {
                    $(szweige).show();
                    load_canvas();
                    $(this).data('open', 'true').children("strong").html('zuklappen');
                }
            });
            
            // Wenn man nur ein Element selektieren will
            var just_select = false;
            if(just_select){
                $(mopt).remove();
                $(more).find("div.move").remove();
                $(cont).find("button.new").remove();
                
                $(whites).find("a.name").off().on("click", function(){
                    var z_id = $(this).data('id');
                    var z_titel = $(this).data('titel');
                    var z_element = $(this).data('element');
                    
                    if($.isFunction(sopt.select_cb))
                        sopt.select_cb(z_id, z_titel, z_element);
                });  
            } else { // Normale Element-Ansicht
                
                $(whites).find("a.name").off().on("click", function(){
                    var z_id = $(this).data('id');
                    open_menue(z_id, sopt.menue);
                });  
                
                // Optionsbereich anzeigen oder verstecken
                $(mopt).children("a").off().on("click", function(){
                    $(this).siblings("div.optarea").show();
                });
                $(mopt).off("mouseleave").on("mouseleave", function(){
                    $(this).children("div.optarea").hide();
                });
                
                // Eintraege sortierbar machen
                $(cont).find("div.baum, div.zweig").each(function(){
                    var tthis = $(this);
                    var itemsT = $(this).children("div.zweig");
                    var handleT = $(itemsT).children("div.row").find("div.move");
                    
                    var pid = $(this).data('kat');
                    var abfolge = '';
                    
                    $(handleT).disableSelection();
                    
                    $(this).sortable({
                        items: itemsT,                    
                        handle: handleT,
                        containment: cont,
                        axis: 'y',
                        start: function(){
                            $(canvas).hide();
                        },
                        stop: function(e, ui){
                            var mei = ui.item;
                            $(mei).find("div.more").hide();
                            
                            $(tthis).children("div.zweig").each(function(index){
                               abfolge += (index > 0?'|':'')+$(this).data('kat'); 
                            });
                            
                            $(canvas).show();
                            load_canvas();
                            
                            $.post('inc_structure.php', {
                                index: 'n173',
                                id: pid,
                                task: 'sort',
                                nsort: abfolge,
                                menue: sopt.menue
                            }, function(data){ 
                                logincheck(data);       
                            }); 
                        }
                    });
                });
                
                // Elemente loeschen
                $(mopt).find("a.delete").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wollen Sie diesen Menüpunkt wirklich unwiederruflich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $(loadme).show();
                        $(myrow).hide();
                        
                        $.post('inc_structure.php', {
                            index: 'n173',
                            id: did,
                            task: 'remove',
                            menue: sopt.menue
                        }, function(data){ logincheck(data);
                            load_struktur(data);
                        });         
                    });
                });
                
                // Element bearbeiten
                $(mopt).find("a.edit").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var mywhite = $(myrow).children("div.white");
                    
                    $(mywhite).find("a.name").trigger("click");
                });
                
                // Element eine Ebene höher verschieben
                $(mopt).find("a.move_higher").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wollen Sie diesen Menüpunkt samt Kind-Menüpunkten wirklich eine Ebene höher verschieben?');
                    $("#sfrage button:last").on("click", function(){
                        $(loadme).show();
                        $(myrow).hide();
                        
                        $.post('inc_structure.php', {
                            index: 'n173',
                            id: did,
                            task: 'move_higher',
                            menue: sopt.menue
                        }, function(data){ logincheck(data);
                            load_struktur(data);
                        });         
                    });
                });
                
                // Element anderem Element zuordnen
                $(mopt).find("a.move_another").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wenn Sie diesen Menüpunkt samt Kind-Menüpunkten einem anderen Menüpunkt zuordnen wollen, klicken Sie auf weiter und wählen Sie anschließend im Baum den gewünschte Menüpunkt aus.');
                    $("#sfrage button:last").on("click", function(){
                        var white = $(row).find("div.white");
                        
                        $(white).find("a").off();
                        $(white).addClass("chooseme").off("click").on("click", function(){
                            $(loadme).show();
                            var toid = $(this).data('kat');
                            
                            $.post('inc_structure.php', {
                                index: 'n173',
                                id: did,
                                task: 'move_another',
                                to: toid,
                                menue: sopt.menue
                            }, function(data){ logincheck(data);
                                load_struktur(data);
                            });  
                        });       
                    });
                });
                
                // Hinzufügen Kind
                $(mopt).find("a.add_child").off().on("click", function(){
                    $(this).off("click");
                    var did = $(this).parents("div.optarea").data('kat');
                    new_child(0, did, sopt.menue);
                });
                
                // Hinzufügen Geschwister
                $(mopt).find("a.add_sibling").off().on("click", function(){
                    $(this).off("click");
                    var did = $(this).parents("div.optarea").data('kat');
                    new_child(did, 0, sopt.menue);
                });
                
                // Falls neues Element hinzugefügt wurde
                if(newobj){
                    var open = $(baum).find("#open_me");
                    $(open).parents("div.zweig").children("div.row").find("a.klappen").trigger("click");
                }
            }
            
            // Canvas-Element zeichen
            function load_canvas(){
                if(!$(canvas)[0] || !$(zweig).filter(".zweig_0")[0])
                    return false;
                
                $(canvas).clearCanvas();
                var cat = $(canvas).offset().top;
                var cal = $(canvas).offset().left;
                
                function recurs_drawing(ebene){
                    var mye = $(zweig).filter('.zweig_'+ebene);
                    if($(mye)[0]){
                        ebene ++;
                        
                        $(mye).each(function(){
                            var mchilds = $(this).children("div.zweig").filter(":visible");
                            if($(mchilds)[0]){                            
                                var y1 = parseInt($(this).offset().top - cat + 13);
                                var y2 = parseInt($(this).children("div.zweig").last().offset().top - cat + 13);
                                var x1 = parseInt($(this).children("div.row").offset().left - cal + 8);
                                
                                $(canvas).drawLine({
                                    x1: x1, y1: y1,
                                    x2: x1, y2: y2
                                });
                            }
                        });
                        
                        recurs_drawing(ebene);
                        ebene --;
                    }                        
                }
                recurs_drawing(0);
                
                $(canvas).drawLine({
                    x1: 27, y1: 0,
                    x2: 27, y2: parseInt($(zweig).filter(".zweig_0").last().offset().top - cat + 14)
                });
                
                $(row).each(function(){
                    var cury = parseInt($(this).offset().top - cat + 13);
                    var curl = parseInt($(this).offset().left - cal); 
                    
                    $(canvas).drawLine({
                        x1: curl, y1: cury,
                        x2: (curl - 12), y2: cury
                    });
                });
            }
            load_canvas();
        });
    }
    
    // Neues Element erstellen
    function new_child(sibling, child, menue){
        var did = 0;
        var type = '';
        
        if(sibling > 0){
            did = sibling;
            type = 'sibling';
        } 
        if(child > 0){
            did = child;
            type = 'child';
        }
        
        $.post('inc_structure.php', {
            index: 'n173',
            id: did,
            task: 'new',
            type: type,
            menue: menue
        }, function(data){ logincheck(data);
            if(parseInt(data) > 0){
                open_menue(data, menue);
                load_struktur(data);
            } else {
                alert('Beim Anlegen trat ein Fehler auf. Bitte benachrichtigen Sie das CMS fokus Team! Fehlermeldung: '+data);
            }
        });
    }
     
    if(!sopt.open)       
        load_struktur();
    else
        load_struktur(sopt.open);        
}




// Slots bearbeiten
if(($("#fn180")[0] && lastindex == 'n180')){
    slots_start();
}

function slots_start(){
    var vslots = $("#fn180 #vslots");
    
    $(vslots).find("a").off().on("click", function(){
        var slot = $(this).data('id');
        
        fenster({
            id: 'n148',
            width: 600,
            blackscreen: 'none',
            cb: function(mf, inhalt){
                $.post('inc_structure.php', {
                    index: 'n140',
                    slot: slot
                }, function(data){
                    logincheck(data);
                    $(inhalt).html(data);
                    
                    var sd = $(mf).find("#struk_doks");
                    $(inhalt).find("div.sprachbox").remove();
                    
                    strukturelement_strukdok(mf, sd, 0, slot);
                });
            }
        });
    });
}




// Kategorien bearbeiten
if(($("#fn198")[0] && lastindex == 'n198') || ($("#fn190")[0] && lastindex == 'n190')){
    kategorien_start();
}

function kategorien_start(){
    if(($("#fn190")[0] && lastindex == 'n190')){
        var cont = $("#fn190 div.inhalt");
    } else {
        var just_select = true;
        var cont = $("#fn198 div.inhalt");
    }
        
    var loadme = $(cont).find("div.loadme");
    var baum = $(cont).find("div.baum");
    var canvas = $(cont).find("canvas");
    
    $.jCanvas({
        strokeStyle: "#999",
        strokeWidth: 1,
        strokeJoin: "miter",
    });
    
    function load_kats(newobj, mustrename){
        $(loadme).show();
        
        $.get('inc_structure.php', {
            index: 'n191',
            open: newobj,
            just_select: just_select
        }, function(data){ logincheck(data);
            $(baum).html(data);
            
            $(canvas).attr("height", $(baum).height());
            
            var zweig = $(baum).find("div.zweig");
            var row = $(zweig).find("div.row");
            var whites = $(row).find("div.white");
            var more = $(row).find("div.more");
            var mopt = $(more).find("div.opt");
            
            // Breite der Bereiche anpassen
            $(row).each(function(){
                var white = $(this).find("div.white");
                var tmore = $(this).find("div.more");
                $(tmore).width(($(this).width() - $(white).width()));
            });
            
            $(zweig).not(".zweig_0").hide();
            $(more).hide();
            
            // Graue Balken bei Hover anzeigen
            $(row).off("mouseenter mouseleave").on("mouseenter", function(){
                $(this).find("div.more").show();
            }).on("mouseleave", function(){
                $(this).find("div.more").hide().find("div.optarea").hide();
            });
            
            // Weitere Elemente ausklappen lassen
            $(row).find("a.klappen").off().on("click", function(){
                var szweige = $(this).parents("div.row:first").siblings("div.zweig");
                
                if($(this).data('open') == 'true'){
                    $(szweige).hide();
                    load_canvas();
                    $(this).data('open', 'false').children("strong").html('aufklappen');
                } else {
                    $(szweige).show();
                    load_canvas();
                    $(this).data('open', 'true').children("strong").html('zuklappen');
                }
                
                if(just_select)
                    set_button($("#fn198"), $(cont).find("div.box_save"));
            });
            
            // Wenn man nur ein Element selektieren will
            if(just_select){
                $(mopt).remove();
                $(more).find("div.move").remove();
                $(cont).find("button.new").remove();
                
                var sb = $(cont).find("div.box_save");
                var cboxen = $(whites).find("p.just_select input");
                
                save_button($("#fn198"));
                
                $(cboxen).off().on("click", function(){
                    $(sb).show();
                });
                
                $(sb).find("input.bs1").off().on("click", function(){
                    $(cboxen).removeAttr("checked");
                    $(sb).hide();
                });
                
                $(sb).find("input.bs2").off().on("click", function(){
                    var ins_to = $("#fn251_1_categories div.boxedarea ul");
                    if($(ins_to)[0]){
                        $(cboxen).filter(":checked").each(function(index){
                            if(!$(ins_to).children("li").hasClass('in_kat_'+$(this).val())){
                                $(ins_to).append('<li class="in_kat_'+$(this).val()+'"><span>'+$(this).data('name')+'</span><a></a><input type="hidden" name="kat[]" value="'+$(this).val()+'" /></li>');        
                            }
                        });
                        
                        $(ins_to).find("li a").off("click").on("click", function(){
                            var me_li = $(this).parent("li");
                            
                            sfrage_show('Wollen Sie diese Kategorie-Zuordnung wirklich entfernen?');
                            $("#sfrage button:last").on("click", function(){
                                $(me_li).remove();
                            });
                        });
                    }
                    
                    $("#fn198 p.close").trigger("click");
                });
            } else { // Normale Kategorie-Ansicht
                
                // Optionsbereich anzeigen oder verstecken
                $(mopt).children("a").off().on("click", function(){
                    $(this).siblings("div.optarea").show();
                });
                $(mopt).off("mouseleave").on("mouseleave", function(){
                    $(this).children("div.optarea").hide();
                });
                
                // Eintraege sortierbar machen
                $(cont).find("div.baum, div.zweig").each(function(){
                    var tthis = $(this);
                    var itemsT = $(this).children("div.zweig");
                    var handleT = $(itemsT).children("div.row").find("div.move");
                    
                    var pid = $(this).data('kat');
                    var abfolge = '';
                    
                    $(handleT).disableSelection();
                    
                    $(this).sortable({
                        items: itemsT,                    
                        handle: handleT,
                        containment: cont,
                        axis: 'y',
                        start: function(){
                            $(canvas).hide();
                        },
                        stop: function(e, ui){
                            var mei = ui.item;
                            $(mei).find("div.more").hide();
                            
                            $(tthis).children("div.zweig").each(function(index){
                               abfolge += (index > 0?'|':'')+$(this).data('kat'); 
                            });
                            
                            $(canvas).show();
                            load_canvas();
                            
                            $.post('inc_structure.php', {
                                index: 'n192',
                                id: pid,
                                task: 'sort',
                                nsort: abfolge
                            }, function(data){ 
                                logincheck(data);       
                            }); 
                        }
                    });
                });
                
                // Elemente loeschen
                $(mopt).find("a.delete").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wollen Sie diese Kategorie wirklich unwiederruflich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $(loadme).show();
                        $(myrow).hide();
                        
                        $.post('inc_structure.php', {
                            index: 'n192',
                            id: did,
                            task: 'remove'
                        }, function(data){ logincheck(data);
                            load_kats(data);
                        });         
                    });
                });
                
                // Kategorie umbennen
                $(mopt).find("a.rename").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var mywhite = $(myrow).children("div.white");
                    var rnit = $(mywhite).children("p.renameit");
                    
                    $(myrow).find("div.optarea").hide();
                    
                    $(mywhite).find("a.name").hide();
                    $(rnit).show().children("input").focus().select();
                });
                    
                $(whites).find("a.save").off().on("click", function(){
                    $(loadme).show();
                    $(this).off("click");
                    
                    var tval = $(this).siblings("input").val();
                    var did = $(this).parents("div.row").find("div.optarea").data('kat');
                    
                    $.post('inc_structure.php', {
                        index: 'n192',
                        id: did,
                        task: 'rename',
                        val: tval
                    }, function(data){ logincheck(data);
                        load_kats(did);
                    });  
                });
                
                $(whites).find("input").off("keyup").on("keyup", function(e){
                    if(e.keyCode == 13) {
                        e.preventDefault();
                        $(this).off("keyup");
                        $(this).siblings("a.save").trigger("click");
                    }
                });
                
                // Element eine Ebene höher verschieben
                $(mopt).find("a.move_higher").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wollen Sie diese Kategorie samt Kindkategorien wirklich eine Ebene höher verschieben?');
                    $("#sfrage button:last").on("click", function(){
                        $(loadme).show();
                        $(myrow).hide();
                        
                        $.post('inc_structure.php', {
                            index: 'n192',
                            id: did,
                            task: 'move_higher'
                        }, function(data){ logincheck(data);
                            load_kats(data);
                        });         
                    });
                });
                
                // Element anderem Element zuordnen
                $(mopt).find("a.move_another").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wenn Sie diese Kategorie samt Kindkategorien einer anderen Kategorie zuordnen wollen, klicken Sie auf weiter und wählen Sie anschließend im Baum die gewünschte Kategorie aus.');
                    $("#sfrage button:last").on("click", function(){
                        var white = $(row).find("div.white");
                        
                        $(white).find("a").off();
                        $(white).addClass("chooseme").off("click").on("click", function(){
                            $(loadme).show();
                            var toid = $(this).data('kat');
                            
                            $.post('inc_structure.php', {
                                index: 'n192',
                                id: did,
                                task: 'move_another',
                                to: toid
                            }, function(data){ logincheck(data);
                                load_kats(data);
                            });  
                        });       
                    });
                });
                
                // Hinzufügen Kind
                $(mopt).find("a.add_child").off().on("click", function(){
                    $(this).off("click");
                    var did = $(this).parents("div.optarea").data('kat');
                    new_child(0, did);
                });
                
                // Hinzufügen Geschwister
                $(mopt).find("a.add_sibling").off().on("click", function(){
                    $(this).off("click");
                    var did = $(this).parents("div.optarea").data('kat');
                    new_child(did, 0);
                });
                
                // Hinzufügen Frisch per Button
                $(cont).find("button.new").off().on("click", function(e){
                    $(this).off("click");
                    e.preventDefault();
                    new_child(0, 0);
                });
                
                // Falls neues Element hinzugefügt wurde
                if(newobj){
                    var open = $(baum).find("#open_me");
                    $(open).parents("div.zweig").children("div.row").find("a.klappen").trigger("click");
                    
                    if(mustrename)
                        $(open).children("div.row").find("a.rename").trigger("click");
                }
            }
            
            // Canvas-Element zeichen
            function load_canvas(){
                if(!$(canvas)[0] || !$(zweig).filter(".zweig_0")[0])
                    return false;
                    
                $(canvas).clearCanvas();
                var cat = $(canvas).offset().top;
                var cal = $(canvas).offset().left;
                
                function recurs_drawing(ebene){
                    var mye = $(zweig).filter('.zweig_'+ebene);
                    if($(mye)[0]){
                        ebene ++;
                        
                        $(mye).each(function(){
                            var mchilds = $(this).children("div.zweig").filter(":visible");
                            if($(mchilds)[0]){                            
                                var y1 = parseInt($(this).offset().top - cat + 13);
                                var y2 = parseInt($(this).children("div.zweig").last().offset().top - cat + 13);
                                var x1 = parseInt($(this).children("div.row").offset().left - cal + 8);
                                
                                $(canvas).drawLine({
                                    x1: x1, y1: y1,
                                    x2: x1, y2: y2
                                });
                            }
                        });
                        
                        recurs_drawing(ebene);
                        ebene --;
                    }                        
                }
                recurs_drawing(0);
                
                $(canvas).drawLine({
                    x1: 27, y1: 0,
                    x2: 27, y2: parseInt($(zweig).filter(".zweig_0").last().offset().top - cat + 14)
                });
                
                $(row).each(function(){
                    var cury = parseInt($(this).offset().top - cat + 13);
                    var curl = parseInt($(this).offset().left - cal); 
                    
                    $(canvas).drawLine({
                        x1: curl, y1: cury,
                        x2: (curl - 12), y2: cury
                    });
                });
            }
            load_canvas();
            
            $(loadme).hide();
        });
    }
    
    // Neues Element erstellen
    function new_child(sibling, child){
        var did = 0;
        var type = '';
        
        if(sibling > 0){
            did = sibling;
            type = 'sibling';
        } 
        if(child > 0){
            did = child;
            type = 'child';
        }
        
        $.post('inc_structure.php', {
            index: 'n192',
            id: did,
            task: 'new',
            type: type
        }, function(data){ logincheck(data);
            load_kats(data, true);
        });
    }
            
    load_kats();
}    