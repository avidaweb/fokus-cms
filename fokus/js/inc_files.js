
if($("#fn400")[0] && lastindex == 'n400') {
    var size = 80;
    var eltern = null;
    var tab = 0;
    var finish = 0;
    var geladen = '';
    var choosen = new Array();
    var gotos = 'n402';
    var stack = '';
    var anzahlladen = 30;
    var pgeladen = 0;
    var open_stack = 0;
    
    var rel2 = new Array();
    if(rel != null){
        rel2 = rel.split('_');
        if(rel2.length > 1){
            open_stack = rel2[1];
            rel = rel2[0];
        }
    }
    
    setTabs();
    
    $("#fn400 p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        gimmeReload();
    });
}

function setTabs(){
    var bC = $("#fn400 #bilderC");
    
    $(bC).tabs({
        tabTemplate: '<li><a href="#{href}">#{label}</a></li>',
        selected: parseInt(rel),
        create: function(){
            $("#fn400 #bilderN").css({
                "height": "auto",
                "overflow": "inherit"
            });
        },
        show: function(event, ui){ 
            eltern = $(bC).find("div.bilderM").eq($(bC).tabs('option', 'selected'));
            
            $(bC).find("div.bilderM").html('');
            $(eltern).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
            
            $.get('inc_files.php', {
                index: 'n401',
                a: ($(bC).tabs('option', 'selected') + 1),
                dir: dirs[$(bC).tabs('option', 'selected')]
            }, function(data){ 
                logincheck(data); 
                
                $(bC).find("div.bilderM").html('');
                stack = '';
                anzahlladen = 30;
                choosen = new Array();
                
                $(eltern).html(data);
                
                $(eltern).find(".mitte").css('width', ($(eltern).width() - 17 -  $(eltern).find(".left").outerWidth(true) -  $(eltern).find(".right").outerWidth(true)) + 'px');  
                
                tab = $(bC).tabs('option', 'selected');
                gotos = (tab != 2?'n402':'n403');
                                
                $(bC).find(".dropdown, .dropdown2").find("input:checkbox, input:radio").off().on("click", function(){
                    abfragen(eltern, gotos);
                }); 
                $(bC).find("div.bsuche input").off("keyup").on("keyup", function(){
                    $(bC).find("div.bsuche input").not(this).val($(this).val());
                    abfragen(eltern, gotos);
                }); 
                $(bC).find(".dropdown").find("span").off().on("click", function(){ 
                    $(this).toggleClass("funten").nextAll().toggle();
                })
                .toggleClass("funten").nextAll().toggle();
                
                abfragen(eltern, gotos);         
                
                $(bC).find("#bilder_hochladen_1").off().on("click", function(){
                    startUpload({
                        refresh: gimmeReload,
                        dir: dirs[tab],
                        images: true,
                        blackscreen: '',
                        blackscreen_edit: '2'
                    });   
                });
                
                $(bC).find("#bilder_hochladen_2").off().on("click", function(){
                    startUpload({
                        refresh: gimmeReload,
                        images: false,
                        blackscreen: '',
                        blackscreen_edit: '2'
                    });   
                });
                
                $(bC).find(".bsuche button, #backbuttond").off().on("click", function(){
                    stack = '';
                    abfragen(eltern, gotos);
                });
                
                $(bC).find(".dir button").off("click").on("click", function(){
                    
                    fenster({
                        id: 'n480',
                        blackscreen: '',
                        width: 620,
                        cb: function(neww, inhalt){
    
                            function ordner_struktur_laden(dopen){
                                $.post('inc_files.php', {
                                    index: 'dir',
                                    kat: tab,
                                    ordner: dirs[tab]
                                }, function(data){ logincheck(data);
                                    $(inhalt).html(data);
                                    setFocus(neww);
                                    
                                    var ownO = $("#fn480 .own");
                                    
                                    if($(ownO).length < 2){
                                        $(ownO).css("background-color", "#e5eff9").find(".options").children("a, span").show();
                                    }
                                    
                                    // Mouseover
                                    $(ownO).off("mouseenter").on("mouseenter", function(){
                                        $(this).css("background-color", "#e5eff9").find(".options").children("a, span").show();
                                        $(this).find(".titel a").addClass("lcolor");
                                    }).off("mouseleave").on("mouseleave", function(){
                                        $(this).css("background-color", "transparent").find(".options").children("a, span").hide();
                                        $(this).find(".titel a").removeClass("lcolor");
                                    });
                
                                    // Ordner laden
                                    $("#fn480 .titel a").off("click").on("click", function(){
                                        dirs[tab] = $(this).attr("rel");
                                        abfragen(eltern, gotos);
                                        $("#fn480 p.close").trigger("click");
                                        $("#bilderC .dir p").html($(this).html());
                                    });
                
                                    // Neuen Ordner anlegen
                                    $("#fn480 a.new").off("click").on("click", function(){
                                        var nr = $(this).parents(".own:first").siblings(".o").length;
                                        var ordner = $(this).attr("rel");
                                        
                                        $.post('inc_files.php', {
                                            index: 'dir_new',
                                            kat: tab,
                                            ordner: ordner,
                                            nr: nr
                                        }, function(data){ 
                                            logincheck(data);
                                            ordner_struktur_laden(data);
                                            abfragen(eltern, gotos);
                                        });
                                    });
                
                                    // Ordner umbennen
                                    $("#fn480 a.umb").off("click").on("click", function(){
                                        var pa = $(this).parents(".own:first").find(".titel span");
                                        var ordner = $(this).attr("rel");
                                        
                                        $(pa).find("input, strong").show();
                                        $(pa).find("a").hide();
                                        
                                        $(pa).find("strong").off("click").on("click", function(){
                                            $(pa).find("strong").hide();
                                            var otitel = $(pa).find("input").val();
                                        
                                            $.post('inc_files.php', {
                                                index: 'dir_rename',
                                                kat: tab,
                                                ordner: ordner,
                                                titel: otitel
                                            }, function(data){ 
                                                logincheck(data);
                                                
                                                $(pa).find("input").hide();
                                                $(pa).find("a").show();
                                                
                                                if(otitel != ''){
                                                    $(pa).find("a").text(otitel);
                                                    abfragen(eltern, gotos);
                                                }
                                            });
                                        });
                                        
                                        $(pa).find("input").off("keypress").on("keypress", function(e){
                                            if(e.keyCode == 13) {
                                                $(pa).find("strong").trigger("click");
                                                e.preventDefault(); 
                                            }
                                        });
                                        
                                        $(pa).find("input").off("focus").on("focus", function(){
                                            this.select();
                                        }).focus();
                                    });
                                    
                                    if(dopen)
                                        $('#fn480 #o_'+dopen).find("div.own a.umb").trigger("click");
                
                                    // Ordner löschen
                                    $("#fn480 a.del").off("click").on("click", function(){
                                        var ordner = $(this).attr("rel");
                                        
                                        sfrage_show('Wollen Sie diesen Ordner wirklich entfernen? Alle Kindelemente werden in den dar&uuml;berliegenden Ordner verschoben.');
                                        $("#sfrage button:last").on("click", function(){
                                            $.post('inc_files.php', {
                                                index: 'dir_del',
                                                kat: tab,
                                                ordner: ordner
                                            }, function(data){ 
                                                logincheck(data);
                                                ordner_struktur_laden();
                                                abfragen(eltern, gotos);
                                            });
                                        });
                                    });
                                    
                                });
                            }
                            ordner_struktur_laden();
                        }
                    });
                });
            });
        }
    });
}


function abfragen(eltern, task){
    var bc = $("#bilderC");
    $(bc).find("#hauptLoading td").show(); 
    
    /// WHERE KLAUES
    var dt = '';
    if(task == 'n402'){
        if($(bc).find("#dateityp_1:checked").length > 0) dt += 'jpg|';
        if($(bc).find("#dateityp_2:checked").length > 0) dt += 'gif|';
        if($(bc).find("#dateityp_3:checked").length > 0) dt += 'png|';
    }else {
        if($(bc).find("#dateityp_1:checked").length > 0) dt += 'zip|';
        if($(bc).find("#dateityp_2:checked").length > 0) dt += 'rar|';
        if($(bc).find("#dateityp_3:checked").length > 0) dt += 'pdf|';
        if($(bc).find("#dateityp_4:checked").length > 0) dt += 'xls|xlsx|';
        if($(bc).find("#dateityp_5:checked").length > 0) dt += 'doc|docx|';
        if($(bc).find("#dateityp_6:checked").length > 0) dt += '*|';
    }
    
    var ar = '';
    if($(bc).find("#ausrichtung_1:checked").length > 0) ar += 'h|';
    if($(bc).find("#ausrichtung_2:checked").length > 0) ar += 'v|';
    
    /// SORT KLAUSEL
    var sort1 = '';
    if($(bc).find("#sort1_1:checked").length > 0) sort1 = 'titel';
    if($(bc).find("#sort1_2:checked").length > 0) sort1 = 'timestamp';
    if($(bc).find("#sort1_3:checked").length > 0) sort1 = 'last_timestamp';
    if($(bc).find("#sort1_4:checked").length > 0) sort1 = 'last_autor';
    
    var sort2 = '';
    if($(bc).find("#sort2_1:checked").length > 0) sort2 = 'asc';
    if($(bc).find("#sort2_2:checked").length > 0) sort2 = 'desc';
    
    var bsuche = $(bc).find("div.bsuche");
    var q = $(bsuche).find("input[type=text]").val();
    
    $(bc).find("#backbuttond").hide();
    
    $(window).on("scroll", function(){
        if($(bc)[0]){
            var abstand = ($(window).scrollTop() - $(bc).find("div.mitte").offset().top + 60);
            if(abstand > 0){
                if($(bc).find("div.left").height() + abstand - 60 < $(bc).find("div.mitte").outerHeight(true))
                    $(bc).find("div.left").css("padding-top", abstand + "px");
                if($(bc).find("div.right").height() + abstand - 60 < $(bc).find("div.mitte").outerHeight(true))
                    $(bc).find("div.right").css("padding-top", abstand + "px");
            } else {
                $(bc).find("div.left, div.right").css("padding-top", "0px");
            }
        }
    });
        
    $.get('inc_files.php', {
        kat: tab,
        index : task,
        dt : dt,
        ar : ar,
        q : q,
        sort1 : sort1,
        sort2 : sort2,
        stack : stack,
        laden: anzahlladen,
        dir: dirs[tab]
    }, function(data){ 
        logincheck(data); 
        
        if(task == 'n402'){
            var board = $(eltern).find("td.board");
            geladen = data;
            
            finish = 0;
            aufklappen();
            
            $("#wslider1, #wslider2").slider({
                min : 20,
                max : 300,
                value : size,
                slide: function(event, ui) {
                    size = ui.value;
                    aktualisieren(eltern, true);
                    
                    if($(this).attr("id") == "wslider1") $("#wslider2").slider("value", ui.value);
                    else $("#wslider1").slider("value", ui.value);
                }
            });
            
            $(board).html(geladen).css("visibility", "hidden");
            pgeladen = 0;
            var images = $(board).find("img");
            var loadtimer = null;
            
            loadtimer = setTimeout(function(){
                $(board).css("visibility", "visible");
                aktualisieren(eltern, false);
            }, 10000);
                
            if(!$(images)[0]){ 
                $(board).css("visibility", "visible");
                aktualisieren(eltern, false);
                clearTimeout(loadtimer);
            }
    
            $(images).on("load", function() {
                pgeladen ++;
                
                if(pgeladen >= $(images).length){ 
                    $(board).css("visibility", "visible");
                    
                    setTimeout(function(){
                        aktualisieren(eltern, false);
                    }, 10);
                    clearTimeout(loadtimer);
                }
            });
        } else {
            var dateien = $(eltern).find("#dateien"); 
            $(dateien).html(data);
            
            datei();
        }
    });
}

function aktualisieren(eltern, notfirst){ 
    var board = $(eltern).find("td.board"); 
    $(board).html(geladen);
    
    if(!notfirst)
        $("#bilderC #hauptLoading td").show();  
        
    var bone = $(board).find("div.one");
    var bmore = $(board).find("div.more");
    $(bone).width(size);
    
    var max = Math.floor($(board).width() / $(bone).outerWidth(true)); 
    var len = $(bone).length;
    var counts = 1;
    
    if(len == 0) finish = 1; 
    if(max == 0) max = 6; 
    
    for(var x=0; x < len; x += max){ 
        var boardrow = $('<div class="boardrow boardrow_'+counts+'" />');
        
        var nmax = x + max;
        $(bone).slice(x, nmax).wrapAll(boardrow); 
        
        if(counts == 2) counts = 0;
        counts ++;      
    }
    
    if($(bmore)[0]){
        var boardrow = $('<div class="boardrow boardrow_more boardrow_'+counts+'" />');
        $(bmore).wrapAll(boardrow); 
    
        $(bmore).find("a.moreA").off("click").on("click", function(){ 
            anzahlladen += 30;
            abfragen(eltern, gotos);
        });
        $(bmore).find("a.moreB").off("click").on("click", function(){
            anzahlladen = 999999999; 
            abfragen(eltern, gotos);
        });
    }
    
    $(board).find("div.boardrow").each(function(count){
        var boardrow = $(this);
        var mheight = 0;
        var ones = $(boardrow).find("div.one div.thumb");
        
        $(ones).css('margin-top', '0px');
        
        var xgeladen = 0;
        var ximages = $(ones).children("img.mainpic");

        $(ximages).on("load", function(){
            xgeladen ++;
            
            if(xgeladen >= $(ximages).length){ 
                $(ones).each(function(){
                    if($(this).height() > mheight)
                        mheight = $(this).height();
                }); 
                 
                $(ones).each(function(){
                    var diff = mheight - $(this).height();
                    $(this).css('margin-top', diff+'px'); 
                    
                    if(choosen.indexOf($(this).parent("div.one").attr("id")) != -1) 
                        $(this).parent("div.one").addClass("ui-selected");
                });
            }
        });
    });
        
    // Bei Doppelklick Ordner oder Bild öffnen
    $(board).find("img.mainpic").off("dblclick").on("dblclick", function(){
        if(!$(this).siblings("input.isdir")[0]){
            var dwidth = $(this).siblings("input.dateiwidth").val(); 
            var ddatei = $(this).siblings("input.dateibig").val();
            
            big_image(dwidth, ddatei);
        } else {
            var ndir = $(this).siblings("input.isdir").val();
            var dtitel = $(this).parent("div.thumb").siblings("div.titel").text();
            
            ordner_oeffnen(ndir, dtitel);
        }
    });
        
    // Ordner-Struktur
    $("#dir_struktur a").off("click").on("click", function(){
        var ndir = $(this).attr("rel");
        var dtitel = $(this).text();
        
        ordner_oeffnen(ndir, dtitel);
    });
    
    if(!notfirst)
        $("#bilderC #hauptLoading td").hide();
        
    $(board).find("#real_pics").selectable({
        filter : '.one',
        start: function(event, ui) { 
            if(!$("#mauswahl1, #mauswahl2").is(":checked"))
                choosen = new Array();
        },
        selected: function(event, ui) {
            var sel = ui.selected;
            choosen.push($(sel).attr("id")); 
        },
        stop: function(event, ui) {
            vorschau(choosen[0]);
            for(x = 0; x < choosen.length; x++)
                $('#'+choosen[x]).addClass("ui-selected");
        },
        unselected: function(){
            $(eltern).find("#vorschau").html('');
            for(x = 0; x < choosen.length; x++)
                $('#'+choosen[x]).addClass("ui-selected");
        }
    });
        
    if(open_stack){
        var openid = $('#bild_'+open_stack);
        $(openid).addClass("ui-selected"); 
        choosen.push('bild_'+open_stack);
        vorschau('bild_'+open_stack);
    }
    
}

function datei(){
    $("#bilderC #dateien").selectable({
        filter : '.one',
        start: function(event, ui) {
            choosen = new Array();
        },
        selected: function(event, ui) {
            var sel = ui.selected;
            choosen.push($(sel).attr("id"));
            vorschau($(sel).attr("id"));
        },
        unselected: function(){
            $(eltern).find("#vorschau").html('');
        }
    });
    
    if(open_stack){
        var openid = $('#datei_'+open_stack);
        $(openid).addClass("ui-selected"); 
        choosen.push('datei_'+open_stack);
        vorschau('datei_'+open_stack);
    }
}


function ordner_oeffnen(dir, dtitel){
    dirs[tab] = dir;
    var task = (tab != 2?'n402':'n403');
    
    abfragen(eltern, task);
    $("#bilderC .dir p").html(dtitel);
    $("#vorschau").html('');
}

function big_image(dwidth, ddatei){
    fenster({
        id: '490',
        blackscreen: '',
        width: dwidth,
        cb: function(neww, inhalt){
            $(inhalt).append('<img src="'+ddatei+'" alt=" " class="big" style="display:none;" />');
            $(inhalt).find("img.big").on("load", function() {
                $(this).show();
                $(inhalt).find("img.ladebalken").hide();
            });
        }
    });
}

function vorschau(id){
    if(!id)
        return false;
    
    var ida = id.split("_");
    id = ida[1];
    var vor = $(eltern).find("#vorschau").html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />'); 
    
    choosen = new Array();
    var choosen_string = '';
    
    if($("#real_pics")[0]){
        $("#real_pics div.ui-selected").each(function(){
            var tid = $(this).attr("id");
            
            choosen.push(tid);
            choosen_string += (!choosen_string?'':',')+tid;
        });
    } else if($("#dateien")[0]){
        $("#dateien div.ui-selected").each(function(){
            var tid = $(this).attr("id");
            
            choosen.push(tid);
            choosen_string += (!choosen_string?'':',')+tid;
        });
    }
    
    $.get('inc_files.php', {
        index: 'n400_preview',
        id: id,
        count: choosen.length
    }, function(data){ 
        logincheck(data);
        vor.html(data);
        
        var bilderC = $("#bilderC");
        
        $(bilderC).find("#ordner_oeffnen").off("click").on("click", function(){
            var ndir = $(vor).find("#dateistack").val(); 
            var dtitel = $(vor).find("#dateistack_titel").val();
            
            ordner_oeffnen(ndir, dtitel);
        });
        
        $(bilderC).find("a.bigimg").off("click").on("click", function(){
            if(!$("#v_isdir")[0]){
                var dwidth = $(vor).find("#dateiwidth").val(); 
                var ddatei = $(vor).find("#dateibig").val();
                big_image(dwidth, ddatei);
            } else {
                $(bilderC).find("#ordner_oeffnen").trigger("click");
            }
        });
        
        $(bilderC).find("#del_auswahl").off("click").on("click", function(){
            sfrage_show('Wollen Sie die ausgew&auml;hlten '+(gotos == 1?'Bilder':'Dateien')+' ('+choosen.length+') wirklich l&ouml;schen?');
            $("#sfrage button:last").on("click", function(){
                for (var i = 0; i < choosen.length; i++){
                    var delid = split_id(choosen[i]);  
                    
                    $.get('inc_files.php', {
                        index: 'del', 
                        id: delid
                    }, function(data){
                        gimmeReload();
                        
                        choosen = new Array();
                        $(eltern).find("#vorschau").html('');
                    });
                }
            });
        });
        
        $(bilderC).find("#n450").off("click").on("click", function(){
            neu($(this));
        });
        
        $(bilderC).find("#n460").off("click").on("click", function(){
            var the_file = $(this).data('file');
            var the_file_version = $(this).data('file_version');
            
            startImageEdit({
                blackscreen: '',
                file: the_file,
                file_version: the_file_version,
                callback: function(){
                    vorschau_reload();
                    gimmeReload();
                }
            });
        });
        
        $(bilderC).find("#download").off("click").on("click", function(){
            if($(this).data('dir')){
                window.location.href = 'inc_files.php?index=n400_download&dir='+$(this).data('dir');       
            } else if(choosen.length < 2){
                window.location.href = 'inc_files.php?index=n400_download&id='+$("#dateiid").val();       
            } else {
                var allchoosen = '';
                for (var i = 0; i < choosen.length; i++){
                    allchoosen += (!allchoosen?'':'|')+split_id(choosen[i]); 
                }
                if(allchoosen)
                    window.location.href = 'inc_files.php?index=n400_download&ids='+allchoosen;
            }
        });
        
        $(bilderC).find("#move_in_dir").off("click").on("click", function(e){
            e.preventDefault();
            
            fenster({
                id: 'n480',
                blackscreen: '',
                width: 620,
                cb: function(neww, ninhalt){
                    $.post('inc_files.php', {
                        index: 'dir',
                        kat: 0,
                        ordner: dirs[0]
                    }, function(data){ 
                        logincheck(data);
                        $(ninhalt).html(data);
                        setFocus(neww);
                        
                        $(ninhalt).find("p.titel a").off().on("click", function(e){
                            e.preventDefault();
                            var in_dir = $(this).attr("rel");
                            
                            $.post('inc_files.php', {
                                index: 'dir_move_in',
                                dir: in_dir,
                                stacks: choosen_string
                            }, function(data){
                                gimmeReload();
                            });
                            
                            $(neww).find("p.close").trigger("click");
                        });
                    });
                }
            });
        });
        
        $(bilderC).find("#set_rights").off("click").on("click", function(e){
            e.preventDefault();
            
            fenster({
                id: 'n406',
                blackscreen: '',
                width: 600,
                cb: function(neww, ninhalt){
                    $.post('inc_files.php', {
                        index: 'n406',
                        stacks: choosen_string
                    }, function(data){ 
                        logincheck(data);
                        $(ninhalt).html(data);
                        setFocus(neww);
                        
                        $(ninhalt).find("div.box_save input.bs2").off().on("click", function(e){
                            e.preventDefault();
                            $(this).attr("disabled", true);
                            
                            $.post('inc_files.php', {
                                index: 'n406_save',
                                f: $(ninhalt).find("form").serialize()
                            }, function(data){
                                logincheck(data);
                                gimmeReload();
                            });
                            
                            $(neww).find("p.close").trigger("click");
                        });
                    });
                }
            });
        });
            
        $(bilderC).find("button.new_version").off().on("click", function(e){
            e.preventDefault();
            
            startUpload({
                dir: 0,
                images: ($(this).data('images') == 1?true:false),
                parent: parseInt($(this).data('id')),
                blackscreen: '',
                hide_edit: true,
                limit: 1,
                refresh: function(neww, data){
                    vorschau_reload();
                    gimmeReload();
                    
                    $(neww).find("p.close").trigger("click");
                }
            });
        });
    });
}

function vorschau_reload(){
    var lid = 'a_'+$("#vorschau #dateistack").val();
    vorschau(lid);
}


function aufklappen(){
    $("#bilderC").find("td.obenL, td.hauptL, td.untenL, td.obenR, td.hauptR, td.untenR").off().on("click", function(){
        if($(this).attr("class") == "obenL" || $(this).attr("class") == "hauptL" || $(this).attr("class") == "untenL"){
            var sub = $(eltern).find("div.left");
            var sub2 = $(eltern).find("td.obenL, td.untenL");
            var nwidth = 165;
            var pfeil = new Array("l", "r");
        }
        else {
            var sub = $(eltern).find("div.right");
            var sub2 = $(eltern).find("td.obenR, td.untenR");
            var nwidth = 250;
            var pfeil = new Array("r", "l");
        }
        
        $(sub).stop(true, true)
        if($(sub).width() == 0) {
            $(sub).css("display", "block").animate({
                width: nwidth +'px'
            }, 0, function(){
                $(eltern).find(".mitte").css('width', ($(eltern).width() - 17 -  $(eltern).find(".left").outerWidth(true) -  $(eltern).find(".right").outerWidth(true)) + 'px'); 
                $(sub2).css("background-image", "url(images/pfeil_"+pfeil[0]+".jpg)");
                aktualisieren(eltern);
            });
        }
        else {
            $(sub).animate({
                width: '0px'
            }, 0, function(){ 
                $(sub).css("display", "none");
                $(eltern).find(".mitte").css('width', ($(eltern).width() - 17 -  $(eltern).find(".left").outerWidth(true) -  $(eltern).find(".right").outerWidth(true)) + 'px'); 
                $(sub2).css("background-image", "url(images/pfeil_"+pfeil[1]+".jpg)");
                aktualisieren(eltern);
            });
        }
        
    }); 
}

if(($("#fn450")[0]) && (lastindex == "n450")) {
    var zp_dv = 0;
    var zeit = $("#fn450 #dzs");
    var stackid = rel;  
    
    $(zeit).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
    
    datei_zs();
}

function datei_zs(){
    $.get('inc_files.php', {
        index : 'n451',
        id: stackid,
        v: zp_dv
    }, function(data){   
        logincheck(data);
        var pcontent = $("#dzs #pcontent");
        
        if($(pcontent)[0]){
            var clone = $(pcontent).clone();
            var oleft = $(pcontent).offset().left;
            $(clone).css({
                "top": ($(pcontent).offset().top - $(window).scrollTop()) + "px",
                "left": oleft + "px"
            }).addClass("flytomoon");
        } 
        
        $(zeit).html(data);
        var dzs = $("#dzs");
        
        tooltipp();
        
        var zpL = $(dzs).find(".zpL");
        $(clone).appendTo(zpL);
            
        $(dzs).find("a.loadit").off().on("click", function(){
            if($(this).parent("td")[0])
                $(this).replaceWith('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
            $(dzs).find(".short h2:first").html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
            
            zp_dv = $(this).attr("rel");
            datei_zs();
        });
        
        $(dzs).find(".laden button").off().on("click", function(){
            $(dzs).find(".short h2:first").html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
            
            $.get('inc_files.php', {
                index : 'n452',
                id: stackid,
                v: $(dzs).find("#zp_alteversion").val()
            }, function(data){ 
                zp_dv = 0;
                datei_zs();
                
                vorschau_reload();
                gimmeReload();
            });
        });
        
        $(dzs).find("button#n460").off("click").on("click", function(e){
            e.preventDefault();
            
            var the_file = $(this).data('file');
            var the_file_version = $(this).data('file_version');
            
            startImageEdit({
                blackscreen: '',
                file: the_file,
                file_version: the_file_version,
                callback: function(){
                    datei_zs();
                    vorschau_reload();
                    gimmeReload();
                }
            });
        });
        
        $(dzs).find("#zs_del_auswahl").off().on("click", function(){
            sfrage_show('Wollen Sie diese Version des Bildes wirklich entg&uuml;ltig entfernen?');
            $("#sfrage button:last").on("click", function(){
                $(dzs).find(".short h2:first").html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
                
                $.get('inc_files.php', {
                    index : 'n453',
                    id: stackid,
                    v: $(dzs).find("#zp_alteversion").val()
                }, function(data){ 
                    if(!data){
                        zp_dv = 0;
                        datei_zs();
                    } else {
                        $("#fn450 p.close").trigger("click");
                    }
                    
                    vorschau_reload();
                    gimmeReload();
                });
            });
        });
        
        if($(clone)[0]){
            $(clone).animate({
                "opacity": "0.0"
            }, 700, function(){
                $(clone).remove();
            });
        }
        
            
        $(dzs).find("button.new_version").off().on("click", function(e){
            e.preventDefault();
            
            startUpload({
                dir: 0,
                images: ($(this).data('images') == 1?true:false),
                parent: parseInt($(this).data('id')),
                blackscreen: '',
                hide_edit: true,
                limit: 1,
                refresh: function(neww, data){
                    vorschau_reload();
                    gimmeReload();
                
                    zp_dv = 0;
                    datei_zs();
                    
                    $(neww).find("p.close").trigger("click");
                }
            });
        });
    });        
}



function gimmeReload(){
    if($("#bilderC")[0]){
        var tab = $("#bilderC").tabs('option', 'selected');
        var gotos = (tab != 2?'n402':'n403');
        var eltern2 = $("div.bilderM").eq($("#bilderC").tabs('option', 'selected'));    
        abfragen(eltern2, gotos);
    }
}