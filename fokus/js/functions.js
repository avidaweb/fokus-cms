function init_hotkeys(){ 
    // ALT + S => trigger save button of active window
    $(document).off('keydown.ctrl_s').on('keydown.ctrl_s', function(e){
        e.preventDefault(); 
        
        active_window = getActiveWindow();
        if(active_window != null){ 
            $(active_window).find("div.box_save:visible").children("input:last, button:last").trigger("click");   
        }
    });
     
    // ESC => close active window
    $(document).off('keydown.esc').on('keydown.esc', function(e){ 
        e.preventDefault(); 
        
        active_window = getActiveWindow();
        if(active_window != null){ 
            $(active_window).find("p.close").trigger("click");  
        }
    });
    
    // SPACE => shuffle windows
    $(document).off('keydown.tab').on('keydown.tab', function(e){
        e.preventDefault(); 
        
        var bs = $("#blackscreen, #blackscreen2, #blackscreen3, #blackscreen4, #blackscreen5, #blackscreen6");
        
        lowest_window = getActiveWindow(true);
        if(lowest_window != null && !$(bs)[0]){ 
            $(lowest_window).trigger("mousedown");  
        }
    });
    
    // ctrl + n => create new
    $(document).off('keydown.ctrl_y').on('keydown.ctrl_y', function(e){
        e.preventDefault();  
        
        active_window = getActiveWindow();
        if(active_window != null){ 
            $(active_window).find(".shortcut-new").trigger("click");  
        }
    });
    
    // ctrl + F => open search window
    $(document).off('keydown.ctrl_f').on('keydown.ctrl_f', function(e){
        e.preventDefault(); 
        $("#nav a#s110").trigger("click");
    });
    
    // ctrl + X => open errors window
    $(document).off('keydown.ctrl_x').on('keydown.ctrl_x', function(e){
        e.preventDefault();
        
        if($("#fserrors")[0]){
            $("#fserrors p.close").trigger("click");
            return true;
        }
        
        fenster({
            id: 'serrors',
            width: 850,
            blackscreen: 'none',
            cb: function(nwin, ninhalt){
                $.post('sub_info.php', {
                    index: 'errors'
                }, function(data){
                    logincheck(data);
                    $(ninhalt).html(data);
                    
                    $(ninhalt).find("a").off().on("click", function(){
                        $.post('sub_info.php', {
                            index: 'errors-clear'
                        }, function(data){ logincheck(data);
                            $(ninhalt).find("textarea").val('');
                        }); 
                    }); 
                });
            }
        });
    });
    
    // ctrl + H => open help window
    $(document).off('keydown.ctrl_h').on('keydown.ctrl_h', function(e){
        e.preventDefault();
        
        if($("#fshotkeys")[0]){
            $("#fshotkeys p.close").trigger("click");
            return true;
        }
        
        fenster({
            id: 'shotkeys',
            width: 540,
            blackscreen: 'none',
            cb: function(nwin, ninhalt){
                $.post('sub_info.php', {
                    index: 'hotkeys'
                }, function(data){
                    logincheck(data);
                    $(ninhalt).html(data);
                    save_button(nwin);
                    
                    $(ninhalt).find("div.box_save input").off().on("click", function(e){
                        e.preventDefault();
                        $(nwin).find("p.close").trigger("click");
                    });
                });
            }
        });
    });
}


function correctOptions(opt){
    if(opt.popup != undefined) opt.blackscreen = opt.popup;
    opt.blackscreen = parseInt(opt.blackscreen);
    if(!opt.blackscreen) opt.blackscreen = 'none';
    if(opt.blackscreen == 1) opt.blackscreen = '';

    return opt;
}

function split_id(identifiers){
    var parent = identifiers.split("_");
    return parent[1];
}

function uniqid(){
    var newDate = new Date;
    return (newDate.getTime()+Math.round((10000000 * Math.random())));
} 

function logincheck(data){
    if(data == 'new-log-in')
        window.location.replace('fokus.php');
} 

function kuerzen(string, laenge, zeichen){
    var ende = (zeichen?zeichen:'...');
    
    if(string.length > laenge){
        return string.substr(0, laenge)+ende;
    }
    
    return string;
}


function getActiveWindow(lowest){
    var z = (!lowest?0:999999);
    var active = null;
    
    $("table.fenster:visible").each(function(){
        var curz = parseInt($(this).css("z-index"));
        if((!lowest && curz > z) || (lowest && curz < z)){
            z = curz;
            active = $(this);
        }
    });
    
    return active;
}

function reloadNavigation(){
    $.get('nav.php', {
        only_navi: true
    }, function(data){
        $("#navigationO").replaceWith(data);
        init();
    }); 
}

function rbutton(button, container, rollout_text, rollin_text, cb_rollout, cb_rollin, duration){ 
    if($(button).hasClass("rollin"))
        $(container).addClass("isopen").show();
    else
        $(container).removeClass("isopen").hide();
    
    var fenster = $(button).parents("table.fenster");
    var sb = $(fenster).find("div.box_save"); 
    var c_tag_name = $(container).prop("tagName");
	
	if(!duration) duration = 500;
        
    $(button).off("click").on("click", function(e){
        e.preventDefault();
        
        if($(container).hasClass("isopen")){
            if(c_tag_name != 'TD' && c_tag_name != 'TABLE'){
                $(sb).removeClass("box_save_shadow").css({
                    "bottom": "0px",
                    "padding-bottom": "10px"
                });
        
                $(container).removeClass("isopen").slideUp(duration, function(){
                    set_button($(fenster), $(sb));
                    if($.isFunction(cb_rollin)) cb_rollin();
                });
            } else {
                $(container).removeClass("isopen").hide();
                set_button($(fenster), $(sb));
                if($.isFunction(cb_rollin)) cb_rollin();
            }
            $(button).removeClass("rollin").addClass("rollout").children("span").html(rollout_text);
        } else {
            if(c_tag_name != 'TD' && c_tag_name != 'TABLE'){
                $(container).addClass("isopen").slideDown(duration, function(){
                    set_button($(fenster), $(sb));
                    if($.isFunction(cb_rollout)) cb_rollout();
                });
            } else {
                $(container).addClass("isopen").show();
                set_button($(fenster), $(sb));
                if($.isFunction(cb_rollout)) cb_rollout();
            }
            $(button).removeClass("rollout").addClass("rollin").children("span").html(rollin_text);
        }
    });
}


$.datepicker.regional['de'] = {
    clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
    closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
    prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
    nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
    currentText: 'heute', currentStatus: '',
    monthNames: ['Januar','Februar','März','April','Mai','Juni',
    'Juli','August','September','Oktober','November','Dezember'],
    monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
    'Jul','Aug','Sep','Okt','Nov','Dez'],
    monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
    weekHeader: 'Wo', weekStatus: 'Woche des Monats',
    dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
    dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
    dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
    dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
    dateFormat: 'dd.mm.yy', firstDay: 1,
    initStatus: 'Wähle ein Datum', isRTL: false
};
$.datepicker.setDefaults($.datepicker.regional['de']);

function calcDatePicker(inhalt, hasFormat){
    $(inhalt).find(".datepicker").each(function(){
        $(this).datepicker({
            showOn: 'button',
    		buttonImage: 'images/kalender.jpg',
    		buttonImageOnly: true,
            dateFormat: 'dd.mm.yy',
            constrainInput: false
    	}).off("blur").on("blur", function(){
            var myobj = $(this);
            var myval = $(this).val();
            
            if(myval != ''){
                $.get('sub_info.php', {
                    index: 'date',
                    datum: myval
                }, function(data){
                    $(myobj).val(data);
                });
            }
        });
     });
}    
    
function set_button(fenster, save, bs){
    if($(fenster)[0]){
        if(!save)
            save = $(fenster).find("div.box_save");
            
        if(!$(bs)[0] && !$(fenster).parent("#main")[0])
            bs = $(fenster).parent("#blackscreen, #blackscreen2, #blackscreen3, #blackscreen4, #blackscreen5, #blackscreen6");
        
        if($(bs)[0]){
            var footer_hoehe = $(window).height();
            var fenster_hohe = $(fenster).position().top + $(fenster).height();
        } else {
            var footer_hoehe = $(window).scrollTop() + $(window).height();
            var fenster_hohe = $(fenster).offset().top + $(fenster).height();
        }
         
        var minus = 15;
        
        if($(bs)[0]){
            fenster_hohe -= 15;
            minus = 55;   
        }
        
        if(fenster_hohe - minus >= footer_hoehe){
            var abstand = fenster_hohe - footer_hoehe - minus;
            
            if(abstand < $(fenster).height() - 200){
                $(fenster).addClass("fixed_footer");
                
                $(save).css({
                    "bottom": "0px",
                    "padding-bottom": (10 + abstand) + "px"
                }).addClass("box_save_shadow");
            }
        } else {   
            $(fenster).removeClass("fixed_footer");
                
            $(save).removeClass("box_save_shadow").css({
                "bottom": "0px",
                "padding-bottom": "10px"
            });
        }
    }
}
  
function save_button(fenster){  
    var save = $(fenster).find("div.box_save");
    var inhalt = $(fenster).find("div.inhalt");
    
    if($(save)[0]){
        set_button(fenster, save);
        
        $(window).on("scroll", function(){
            set_button(fenster, save);
        });
        
        $(fenster).parent("#blackscreen, #blackscreen2, #blackscreen3, #blackscreen4, #blackscreen5, #blackscreen6").on("scroll", function(){
            var bs = $(this);
            set_button(fenster, save, bs);
        });
        
        $(fenster).on("dragstop", function(event, ui) {
            set_button(fenster, save);
        });
        
        $(save).find(".bs1").off("click").on("click", function(e){
            e.preventDefault();
            $(fenster).find("p.close").trigger("click");
        });
        
        $(inhalt).find("input").off("keypress keydown.ctrl_s").on("keypress", function(e){
            if(e.keyCode == 13) {
                $(save).find("input:last, button:last").trigger("click");
                e.preventDefault();
            }
        }).on("keydown.ctrl_s", function(e){
            e.preventDefault(); 
            $(save).find("input:last, button:last").trigger("click");
        });
    }
}

function slideDownSave(ele, speed, fenster, sb){
    $(ele).slideDown(speed, function(){
        set_button(fenster, sb);
    });
}

function slideUpSave(ele, speed, fenster, sb){
    if(sb){
        $(sb).removeClass("box_save_shadow").css({
            "bottom": "0px",
            "padding-bottom": "10px"
        });
    }
            
    $(ele).slideUp(speed, function(){
        set_button(fenster, sb);
    });
}    

function setFocus(fenster){
    var fid = $(fenster).attr("id");
    
    if(!fid){ 
        // nothing
    } else if(fid == 'fn143'){ // Neues Strukturelement
        $(fenster).find("input#titel").focus();
    } else if(fid == 'fn210'){ // Neues Dokument
        $(fenster).find("input[name=titel]").focus();
    } else if(fid == 'fn650'){ // Lifetalk
        $(fenster).find("textarea").focus();
    }  else if(fid == 'fs110'){ // Suche
        $(fenster).find("#q").focus();
    }   
    
    save_button(fenster);
}

function fenster(wopt){
    /*
    clicked = Ursprungselement
    blackscreen = Blackscreen Version
    width = Breite des Fenster
    id = ID des Fensters
    cb = Callback-Funktion
    */
    
    var stop = false;
    
    var index = wopt.id;
    var clicked = wopt.clicked;
    lastindex = index;
    
    zindex ++;  
    
    if($('#f'+index)[0]){
        var alt = $('#f'+index);
        var task = $("#taskleiste").find('a#taskf'+index);
        
        if($(alt).css("display") == "none" && $(task)[0]){   
            oeffnen(alt, clicked, task);
            stop = true; 
        }
        else {
            stop = true; 
                
            sfrage_show('Es ist bereits eine Instanz dieses Fensters ge&ouml;ffnet. Soll diese geschlossen werden? Ungespeicherte &Auml;nderungen gehen dabei verloren!');
            $("#sfrage button:last").on("click", function(){
                $(alt).find("p.close").trigger("click");
                $(alt).remove();
                fenster_go();
            });
            
            $("#sfrage button:first").on("click", function(){
                var ani_dauer = ($(window).scrollTop() - $(alt).offset().top - 120);
                ani_dauer = ((ani_dauer < 1?(ani_dauer * -1):ani_dauer) * 0.75);
                
                $(alt).css("z-index", zindex);
                $.scrollTo(alt, {
                    duration: ani_dauer,
                    offset: { left: 0, top: -120 },
                    onAfter: function(){
                        $(alt).animate({ "left" : "-=25px" },20).animate({ "left" : "+=25px" },100).animate({ "left" : "+=25px" },20).animate({ "left" : "-=25px" },100);
                    }
                });
            });
        }
    }
    
    if(!stop)
        fenster_go();
    
    function fenster_go(){
        
        if(wopt.blackscreen != 'none'){
            zindex ++;
            $("body").addClass("mit_vorschau").append('<div id="blackscreen'+wopt.blackscreen+'" />');
            $('#blackscreen'+wopt.blackscreen).css("z-index", zindex);
            $("#footer a").css("z-index", "1");
            $("#navigationO").css("z-index", "1");
            $("#widget-dashboard").css("z-index", "1");
            
            var appendTo = $('#blackscreen'+wopt.blackscreen);
            var ptop = 60 + $("table.fenster").length * 12;
        } else {
            if(neues_fenster_task == 1){
                $("table.fenster").each(function(){
                    $(this).find("p.mini").trigger("click");
                });
            } else if(neues_fenster_task == 2){
                $("table.fenster").each(function(){
                    $(this).find("p.close").trigger("click");
                });
            }
            
            var appendTo = $('#main');
            var ptop = $(window).scrollTop() + 75 + $("table.fenster").length * 12;
        }
        
        $('#taskf'+index).remove();
        
        zindex++; 
        
        var neww = $('<table id="f'+wopt.id+'" class="fenster'+(wopt.blackscreen != 'none'?' wbs':'')+'"></table>');
        $(neww).appendTo(appendTo).html('<tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2"></td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>').width(wopt.width).css({
            'top': ptop + 'px',
            'left': ($("#main").width() / 2 - $(neww).width() / 2) + 'px',
            'z-index': zindex
        });
        var mainTD = $(neww).find("td.B2"); 
                  
        var kopfleiste = $('<div class="kopfleiste"></div>');
        $(kopfleiste).prependTo(mainTD);
        var kminus = 71;
        
        if(wopt.blackscreen == 'none'){
            var mini = $('<p class="mini">ablegen.</p>');
            $(mini).appendTo(kopfleiste).on("click", function(e){
                minimieren(neww, clicked);
            });
            
            kminus = 142;
        }
        
        var reload = (wopt.reload?'<a class="reload"></a>':'');
        var drag = $('<p class="move">'+reload+'</p>').appendTo(kopfleiste).disableSelection().css("width", ($(kopfleiste).width() - kminus) + 'px');
        
        var close = $('<p class="close">schlie&szlig;en.</p>');
        $(close).appendTo(kopfleiste).off().on("click", function(e){
            $(neww).hide("slide", {}, 300, function(){ 
                $(this).remove();
                
                if(wopt.blackscreen != 'none'){
                    $('#blackscreen'+wopt.blackscreen).remove();
                    if(parseInt(wopt.blackscreen) < 2 ||  wopt.blackscreen == ''){ 
                        $("#footer a").css("z-index", "9000");
                        $("#navigationO").css("z-index", "9000");
                        $("#widget-dashboard").css("z-index", "9500");
                        $("body").removeClass("mit_vorschau");
                    }
                }
            });
        });
        
        var inhalt = $('<div class="inhalt"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></div>');
        $(inhalt).appendTo(mainTD);
        
        var fuss = $('<div class="fuss"></div>');
        $(fuss).appendTo(mainTD);
        
        $(neww).draggable({
            handle : 'p.move',
            opacity: 1,
            stop: function(event, ui){ 
                if($(this).offset().top < 60)
                    $(this).css('top', (wopt.blackscreen == 'none'?'60':'0')+'px');
            }
        }); 
        
        if(wopt.blackscreen == 'none'){
            $(neww).mousedown(function(){
                zindex ++;
                $(this).css("z-index", zindex);
            });
        }
        
        if($.isFunction(wopt.cb))
            wopt.cb(neww, inhalt);
            
          
        if(wopt.blackscreen == 'none'){  
            var task = $('<a id="taskf'+wopt.id+'"></p>').hide(); 
            $("#taskleiste").append(task); 
            
            $(task).off().on("click", function(){
                fenster(wopt);
            }).on("mouseenter", function(){
                $("#footer").css('z-index', 9000);
            }).on("mouseleave", function(){
                $("#footer").css('z-index', 1);
            });
        }
    }          
}

function bildauswahl(clicked, mfa, block_inhalt){                                           
    var index = "n270";
    var value = "inc_documents"; 
    
    fenster({
        id: 'n270',
        blackscreen: '2',
        width: 980,
        cb: function(neww, inhalt){
        
            $.get(value+'.php', {
                index: 'n270',
                rel: 1
            }, function(data){ logincheck(data);    
                
                $(inhalt).html(data);  
                setFocus(neww);
                
                $(inhalt).find(".bwL input").off().on("click keyup", zeige_vorschaubilder);
                
                $(inhalt).find(".bwL .dropdown").find("span").off().on("click", function(){ 
                    $(this).toggleClass("funten").nextAll().toggle();
                }).toggleClass("funten").nextAll().toggle();
                
                var bladen = 28;
                var tdir = 0;
                var sbilder = $(inhalt).find("#s_bilder");  
                var p2g = $(inhalt).find("#pics2gal");
                    
                function zeige_vorschaubilder(){ 
                    var tchoosen = new Array();
                    
                    $(sbilder).find("div.dirs, div.pre").remove();
                    $(sbilder).find("div.dirbr").after('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                    
                    var dt = '';
                    if($(inhalt).find("#bw_dateityp_1:checked").length > 0) dt += 'jpg|';
                    if($(inhalt).find("#bw_dateityp_2:checked").length > 0) dt += 'gif|';
                    if($(inhalt).find("#bw_dateityp_3:checked").length > 0) dt += 'png|';
                    var ar = '';
                    if($(inhalt).find("#bw_ausrichtung_1:checked").length > 0) ar += 'h|';
                    if($(inhalt).find("#bw_ausrichtung_2:checked").length > 0) ar += 'v|';
                    var sort1 = '';
                    if($(inhalt).find("#bw_sort1_1:checked").length > 0) sort1 = 'titel';
                    if($(inhalt).find("#bw_sort1_2:checked").length > 0) sort1 = 'id';
                    if($(inhalt).find("#bw_sort1_3:checked").length > 0) sort1 = 'last_timestamp';
                    if($(inhalt).find("#bw_sort1_4:checked").length > 0) sort1 = 'last_autor';
                    var sort2 = '';
                    if($(inhalt).find("#bw_sort2_1:checked").length > 0) sort2 = 'asc';
                    if($(inhalt).find("#bw_sort2_2:checked").length > 0) sort2 = 'desc';
                    
                    $.get('inc_documents.php', {
                        index : 'n271',
                        rel: $(clicked).attr("rel"),
                        qT: $(inhalt).find("#bw_qT").val(),
                        rel: 1,
                        dt : dt,
                        ar : ar,
                        sort1 : sort1,
                        sort2 : sort2,
                        laden: bladen,
                        dir: tdir,
                        mfa: (mfa?'true':'')
                    }, function(data){
                        logincheck(data);                  
                        $(sbilder).html(data);
                        
                        var preO = $(sbilder).find("div.pre");
                        
                        $(sbilder).find("a.moreA").off().on("click", function(){
                            bladen += 14;   
                            zeige_vorschaubilder();
                        });
                        $(sbilder).find("a.moreB").off().on("click", function(){
                            bladen += 999999999999;   
                            zeige_vorschaubilder(); 
                        });
                
                        // Ordner-Struktur
                        $(sbilder).find("div.dirbr a, div.dirs a").off("click").on("click", function(){
                            tdir = $(this).attr("rel"); 
                            zeige_vorschaubilder(); 
                        });
                        
                        function bild_hover(){
                            $(preO).off("mouseenter").on("mouseenter", function(){ 
                                var pre = $(this); 
                                
                                $(pre).css("border", "1px solid #fff");
                                $(pre).children("img").show().css({
                                    "top": ((14 - $(pre).children("img").height()) / 2 + $(pre).offset().top - $(window).scrollTop()) + "px",
                                    "left": ($(pre).offset().left - 19) + "px"
                                });
                            });    
                        }
                        bild_hover();
                        
                        $(preO).off("mouseleave").on("mouseleave", function(){
                            $(this).css("border", "1px solid #000");
                            $(this).children("img").hide();    
                        });
                        
                        if(!mfa){
                            $(preO).find("a").off("click").on("click", function(){ 
                                var apform = $("#fn260 #add_pic_form");
                                
                                $(apform).find("#ins_bild_id").val($(this).attr("rel"));
                                $(apform).find("#ins_bild_titel").html($(this).attr("title"));
                                
                                var bg = $(this).attr("class").split('_');
                                var text_bild = $(apform).find("div.text_bild");
                                
                                $(text_bild).find("span.bildgr").html('Original: '+bg[0]+'x'+bg[1]+'px');
                                
                                if($(text_bild).find("input.bild_h").css("display") != "none"){
                                    $(text_bild).find("input.bild_w").not(":disabled").val(bg[0]);
                                    $(text_bild).find("input.bild_h").not(":disabled").val(bg[1]);
                                }
                                
                                $(apform).find("#preview_picture").attr("src", $(this).siblings("img").attr("src")).fadeIn();
                                $(apform).find("button.edit_current_pic").show().data('file', $(this).attr("rel"));
                                
                                zurfreigabe();
                                $(neww).find("p.close").trigger("click");
                            });
                            
                            $(preO).find("img").off("click").on("click", function(){
                                $(this).siblings("a").trigger("click");
                            });
                        } else {
                            $(sbilder).find("div.choose_dir a").off().on("click", function(){
                                $.get('inc_documents.php', {
                                    index : 'n272',
                                    dir: tdir,
                                    block: $("#fn260").find("#block_id").val(),
                                    ibid: $("#fn260").find("#block_ibid").val(),
                                    blockindex: $("#fn260").find("#blockindex").val(),
                                    id : ausgewaehltes_dokument
                                }, function(data){
                                    $(neww).find("p.close").trigger("click");
                                    block_inhalt();
                                });
                            });
                                                                            
                            
                            $(p2g).off("click").on("click", function(e){
                                e.preventDefault();
                                
                                if(tchoosen.length > 0){
                                    var scho = '';
                                    for(var q = 0; q < tchoosen.length; q++){
                                        scho += (q == 0?'':'_')+tchoosen[q];
                                    }
                                    
                                    $.get('inc_documents.php', {
                                        index : 'n272',
                                        pid: scho,
                                        block: $("#fn260").find("#block_id").val(),
                                        ibid: $("#fn260").find("#block_ibid").val(),
                                        blockindex: $("#fn260").find("#blockindex").val(),
                                        id : ausgewaehltes_dokument
                                    }, function(data){
                                        $(neww).find("p.close").trigger("click");
                                        block_inhalt();
                                    });
                                }
                            });
                            
                            $(sbilder).find("div.tpics").disableSelection().selectable({
                                filter : 'div.pre',
                                start: function(event, ui) { 
                                    $(preO).off("mouseenter").removeClass("ui-selected");
                                    tchoosen = new Array();
                                    $(p2g).hide();
                                },
                                selected: function(event, ui) {
                                    var sel = ui.selected;
                                    tchoosen.push($(sel).children("a").attr("rel")); 
                                },
                                stop: function(event, ui) {
                                    var sel = ui.selected;
                                    
                                    $(sel).addClass("ui-selected");
                                    bild_hover();
                                    
                                    if(tchoosen.length > 0){
                                        $(p2g).html(tchoosen.length+' Element'+(tchoosen.length != 1?'e':'')+' in die Galerie einf&uuml;gen').show();
                                    }
                                }
                            });
                        } 
                        
                    });
                }
                zeige_vorschaubilder();
            });
        }
    });
}



function dok_uebersicht_1(doc, funkt){
    var fn200 = doc; 
    var oldsearch = '';
    
    $(fn200).parents("table.fenster:first").find("p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        funkt();
    });
        
    var search_timeoutD = null;
    $(fn200).find("input[name=suche]").focus().val(d_search).off("keyup change").on("keyup change", function(){
        clearTimeout(search_timeoutD);
        var mesearch = $(this);
        search_timeoutD = setTimeout(function(){
            d_search = $(mesearch).val(); 
            
            if(oldsearch != d_search)
                funkt();
            
            oldsearch = d_search;
        }, 400);
    });
    
    $(fn200).find("div.opt1 a").off().on("click", function(){
        var inp = $(this).next("div:first").find("input");
        if($(inp).not(":checked")[0]) $(inp).attr("checked", "checked");
        else $(inp).removeAttr("checked");
        funkt();
    });
    
    $(fn200).find("div.opt input").off().on("click change", function(){
        funkt();
    });
    
    $(fn200).find("div.opt1 div.ch input").off().on("click", function(){
        if($(this).val() == '1'){
            $(fn200).find("div.opt1 div.showdk").slideDown();
        } else {
            $(fn200).find("div.opt1 div.showdk").slideUp().find("input").removeAttr("checked");
        }
        funkt();
    });
    
    $(fn200).find("a.rbutton").each(function(){
        var sib = $(this).siblings("div.opt");
        rbutton($(this), $(sib), 'einblenden', 'ausblenden');
    });
}

function dok_uebersicht_2(doc){
    var fn200 = doc; 
    var thead = $(fn200).find("table #headline");
    var loading = '<tr><td class="loading" colspan="'+($(thead).find("th").length)+'"><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>';
      
    $(thead).after(loading);
    
    d_opt = '';
    $(fn200).find("div.opt2 input").each(function(){
        if($(this).is(":checked"))
            d_opt += $(this).val()+'+';
    });
    
    d_dklassen = '';
    $(fn200).find("div.opt1 input[type=checkbox]").each(function(){
        if($(this).is(":checked"))
            d_dklassen += '+'+$(this).val();
    });
}

function dok_uebersicht_3(doc, table, funkt){
    var thead = $(table).find("#headline");
    $(thead).find("th").disableSelection().off().on("click", function(){
        $(thead).find("th").removeClass("sort desc asc");
        $(this).addClass("sort");
        
        if($(this).data('sort') == d_lastsort){
            $(this).addClass("desc");
            d_lastsort = '';
            d_sortB = 'DESC';
        } else {
            $(this).addClass("asc");
            d_lastsort = $(this).data('sort');
            d_sortB = 'ASC';
        }
            
        d_sortA = $(this).data('sort'); 
        
        funkt();
    });
    
    if($(table).find("tr.entry").length == 1){
        $(doc).find("input[name=suche]").off("keypress").on("keypress", function(e){
            if(e.keyCode == 13) {
                e.preventDefault();
                $(table).find("tr.entry a:first").trigger("click");
            }
        });
    }
    
    $(doc).find("div.seiten_info").hover(function(){
        var kind = $(this).children("ul");
        var pfeil = $(this).children("p.pfeil");
        
        if($(kind)[0]){
            var parents = $(doc).parents("table.fenster:first");
            var left = $(parents).offset().left;
            var width = $(parents).width();
            var top = $(this).offset().top - $(window).scrollTop() - 7;
            
            $(kind).show().css({
                "left": (left + width - 33) + "px",
                "top": top + "px",
                "z-index": "9999998"
            });
            $(pfeil).show().css({
                "left": (left + width - 48) + "px",
                "top": (top + 4) + "px",
                "z-index": "9999999"
            });
        }
    }, function(){
        $(this).children("ul").hide();
        $(this).children("p.pfeil").hide();
    });
    
    $(doc).find("div.seiten_info a").off().on("click", function(){
        var mid = $(this).data('id');
        
        if($(this).data('type') == 'ele'){
            open_strukturelement(mid);
        } else if($(this).data('type') == 'slot'){
            var tmpneu = $('<a id="n180" class="inc_structure" rel="0"></a>');
            neu(tmpneu);
        }
    });
}

function dok_uebersicht_4(doc, table, funkt){
    var mr = $(table).find("td.more_results");
    
    if($(mr)[0]){
        var inp_limit = $(doc).find("input#akt_limit");
        var dlimit = parseInt($(inp_limit).val());
        
        $(mr).children("a.next").off("click").on("click", function(){ 
            dlimit += 15;
            $(inp_limit).val(dlimit);
            funkt();
        });
        
        $(mr).children("a.all").off("click").on("click", function(){ 
            dlimit = 1000000000;
            $(inp_limit).val(dlimit);
            funkt();
        });
    }
}


//// FCK EDITOR
//// FCK LINK EINFÜGEN
function fck_link_ext(opt){
    opt = correctOptions(opt);
    opt.ext = true;

    fck_link(null, null, false, false, opt);
}

function fck_link(f, editor, picture, menue, opt){
    if(opt == undefined){
        opt = {};
        opt.blackscreen = '3';
    } else {
        if(opt.blackscreen == undefined)
            opt.blackscreen = '3';
        var ext = (opt.ext == undefined?false:true);
        var url_only = (opt.url_only != undefined && opt.url_only == true?true:false);
    }

    fenster({
        id: 'flink',
        blackscreen: opt.blackscreen,
        width: 590,
        cb: function(neww, inhalt){
    
            if(picture){
                var bildlink = $("div.bild_verlinken");
                
                var fck_href = $(bildlink).find("input[name=link_href]").val();
                var fck_ziel = $(bildlink).find("input[name=link_ziel]").val();
                var fck_power = $(bildlink).find("input[name=link_power]").val();
                var fck_titel = $(bildlink).find("input[name=link_titel]").val();
                var fck_klasse = $(bildlink).find("input[name=link_klasse]").val();
            } else if(menue) {

            }  else if(ext) {
                var fck_href = '';

                if(opt.file != null && opt.file != 0)
                    fck_href = '{d-'+opt.file+'}';
                else if(opt.element != null && opt.document != null && opt.element != 0 && opt.document != 0)
                    fck_href = '{s-'+opt.element+'_'+opt.document+'}';
                else if(opt.element != null && opt.element != 0)
                    fck_href = '{s-'+opt.element+'}';
                else if(opt.href != null && opt.href != '')
                    fck_href = opt.href;
                else if(opt.email != null && opt.email != '')
                    fck_href = 'mailto:'+opt.email;

                var fck_text = opt.text;
                var fck_titel = opt.title;
                var fck_klasse = opt.classes;
                var fck_ziel = (opt.target == 'blank'?1:0);
                var fck_power = (opt.power == 'nofollow'?1:0);
            } else {
                var selection = f.getSelection();
                var start = null;
                
                if (selection.getType() == CKEDITOR.SELECTION_TEXT) { 
                    var fck_text = selection.getSelectedText();
                    
                    start = selection.getStartElement();
                    if(start.is('a')){
                        selection.selectElement(start);
                        
                        fck_text = selection.getSelectedText();
                        
                        var fck_href = start.getAttribute('href');
                        var fck_zielT = start.getAttribute('target');
                        var fck_ziel = (fck_zielT == '_blank'?1:0);
                        var fck_powerT = start.getAttribute('rel');
                        var fck_power = (fck_powerT == 'nofollow'?1:0);
                        var fck_titel = start.getAttribute('title');
                        var fck_klasse = start.getAttribute('class');
                    }
                } else if (selection.getType() == CKEDITOR.SELECTION_ELEMENT) { 
                    var selectionT = selection.getSelectedElement();
                    
                    var fck_text = selectionT.getText();
                    var fck_href = selectionT.getAttribute('href');
                    var fck_zielT = selectionT.getAttribute('target');
                    var fck_ziel = (fck_zielT == '_blank'?1:0);
                    var fck_powerT = selectionT.getAttribute('rel');
                    var fck_power = (fck_powerT == 'nofollow'?1:0);
                    var fck_titel = selectionT.getAttribute('title');
                    var fck_klasse = selectionT.getAttribute('class');
                } 
                
                var bookmarks = f.getSelection().createBookmarks();
            }
            
                
            $.post('fck_buttons.php', {
                index: 'link',
                text: fck_text,
                href: fck_href,
                ziel: fck_ziel,
                power: fck_power,
                titel: fck_titel,
                klasse: fck_klasse,
                picture: (picture?1:0),
                ext: (ext?1:0),
                url_only: (url_only?1:0),
                menue: menue
            }, function(data){ 
                logincheck(data);
                $(inhalt).html(data);
                setFocus(neww);
                
                var linkeO = $(inhalt).find("div.intern div.elover");
                
                function getLinkElemente(nhref, suche){
                    $(linkeO).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                
                    if(menue)
                        nhref = $(inhalt).find("input[name=nhref]").val();
                    
                    $.post('fck_buttons.php', {
                        index: 'link_elements',
                        type: $(inhalt).find("#hiddentype").val(),
                        href: nhref,
                        q: suche
                    }, function(data){ 
                        $(linkeO).html(data);
                        
                        $(linkeO).find("div.elemente span").off("click").on("click", function(){
                            var self = $(this);
                            var parent = $(self).parents("div.elemente:first");
                            var childs = $(parent).children("div.elemente");
                            
                            if($(self).hasClass("aktiv")){
                                $(self).removeClass("aktiv");
                                $(childs).hide();
                            } else {
                                $(self).addClass("aktiv");
                                $(childs).show();
                            }
                            
                            set_button(neww);
                        });
        				
        				var openme = $(linkeO).find("#open_me");
        				if($(openme)[0])
                            $(openme).parents("div.elemente").children("div.own").find("span").trigger("click");
                    });
                }
                getLinkElemente(fck_href);
                
                $(inhalt).find("#searchelement").off("keyup").on("keyup", function(){
                    var q = $(this).val();
                    getLinkElemente('', q);
                });
                
                $(inhalt).find("#fck_opt_more").disableSelection().off("click").on("click", function(){
                    var so = $(inhalt).find("tr.fck_opt_more td");
                    
                    if($(so).css("display") == "none") {
                        $(so).show();
                        $(this).html("Link-Optionen ausblenden").css('background', '#fff url(images/rpfeil_oben.png) no-repeat 2px center');
                    } else {
                        $(so).hide();
                        $(this).html("Link-Optionen einblenden").css('background', '#fff url(images/rpfeil_unten.png) no-repeat 2px center');
                    }
                    
                    set_button(neww);
                });
                
                $(inhalt).find("input[name=linktype]").off("click").on("click", function(){
                    var bextern = $(inhalt).find("tr.tr_e td");
                    var bintern = $(inhalt).find("tr.tr_i td");
                    var bdatei = $(inhalt).find("tr.tr_d td");
                    var bmail = $(inhalt).find("tr.tr_m td");
                    
                    $(bextern).hide();
                    $(bintern).hide();
                    $(bdatei).hide();
                    $(bmail).hide();
                    
                    if($(this).val() == '0'){
                        $(bextern).show();
                        $(inhalt).find("#fck_href").focus();
                    } else if($(this).val() == '1'){
                        $(bintern).show();
                        $(inhalt).find("#searchelement").focus();
                    } else if($(this).val() == '2'){
                        $(bdatei).show();
                    } else if($(this).val() == '3'){
                        $(bmail).show();
                        $(inhalt).find("#fck_mail").focus();
                    }
                    
                    set_button(neww);
                });


                $(neww).find("p.close").on("click", function(){
                    if($.isFunction(opt.closed))
                        opt.closed();
                });

                $(inhalt).find("input.bs1").on("click", function(){
                    if($.isFunction(opt.closed))
                        opt.closed();
                });


                $(inhalt).find("input.bs2").off("click").on("click", function(){
                    $(this).attr("disabled", "disabled");
                    
                    var n_text = $(inhalt).find("#fck_text").val();
                    var n_int = $(inhalt).find("input[name=int_link]:checked").val();
                    var n_datei = $(inhalt).find("input[name=int_file]:checked").val();
                    var n_ext = $(inhalt).find("#fck_href").val()
                    var n_mail = $(inhalt).find("#fck_mail").val()
                    var n_type = $(inhalt).find("input[name=linktype]:checked").val();
                    var n_ziel = $(inhalt).find("#fck_ziel").val();
                    var n_power = $(inhalt).find("#fck_power").val();
                    var n_titel = $(inhalt).find("#fck_titel").val();
                    var n_klasse = '';
                    
                    $(inhalt).find("input.classes").filter(":checked").each(function(){
                        n_klasse += $(this).val()+' ';
                    });
                    $.trim(n_klasse);
                    
                    if(menue)
                        var sprachen = $(inhalt).find("div.linktext form").serialize();
                    
                    var url = '';
                    if(n_type == 1){
                        url = '{s-'+n_int+'}';
                    } else if(n_type == 2){
                        url = '{d-'+n_datei+'}';
                    } else if(n_type == 3){
                        url = 'mailto:'+n_mail;
                    } else {
                        url = n_ext;
                    }


                    if(ext){
                        var robj = {};
                        robj.href = '';
                        robj.email = '';
                        robj.element = 0;
                        robj.document = 0;
                        robj.file = 0;

                        if(n_type == 1){
                            robj.element = $(inhalt).find("input[name=int_link]:checked").data('element');
                            robj.document = $(inhalt).find("input[name=int_link]:checked").data('document');
                        } else if(n_type == 2){
                            robj.file = n_datei;
                        } else if(n_type == 3){
                            robj.email = n_mail;
                        } else {
                            robj.href = n_ext;
                        }

                        if(!url_only){
                            robj.text = n_text;
                            robj.title = n_titel;
                            robj.classes = n_klasse;
                            robj.target = (n_ziel == 1?'blank':'self');
                            robj.power = (n_power == 1?'nofollow':'follow');
                        }

                        $(neww).find("p.close").trigger("click");

                        if($.isFunction(opt.picked))
                            opt.picked(robj);

                        return true;
                    }

                    
                    var build_link = '<a href="'+url+'"'+(n_ziel == 1?' target="_blank"':'')+(n_power == 1?' rel="nofollow"':'')+(n_titel?' title="'+n_titel+'"':'')+(n_klasse?' class="'+n_klasse+'"':'')+'>'+n_text+'</a>'; 
                    
                    if(picture){
                        $(bildlink).find("input[name=link_href]").val(url);
                        $(bildlink).find("input[name=link_ziel]").val(n_ziel);
                        $(bildlink).find("input[name=link_power]").val(n_power);
                        $(bildlink).find("input[name=link_titel]").val(n_titel);
                        $(bildlink).find("input[name=link_klasse]").val(n_klasse);
                    } else if(menue) {
                        $.post('inc_structure.php', {
                            index: 'n174',
                            id: menue,
                            url: url,
                            ziel: n_ziel,
                            power: n_power,
                            klasse: n_klasse,
                            spr: sprachen
                        }, function(data){ 
                            logincheck(data);
                            
                            menue_start({
                                open: menue
                            });
                        });
                    } else {
                        if(n_text){
                            f.getSelection().selectBookmarks( bookmarks );  
                            f.insertHtml(build_link);
                        }
                    }
                        
                    $(neww).find("p.close").trigger("click");
                });
            });
        }
    });
}


function struktur_start(sopt){
    if($("#fn120")[0] && !sopt.just_select){
        var cont = $("#fn120 div.inhalt");
    } else {
        var just_select = true;
        var cont = $("#fn130 div.inhalt");
    }
        
    var loadme = $(cont).find("div.loadme");
    var baum = $(cont).find("div.baum");
    var canvas = $(cont).find("canvas");
    
    $.jCanvas({
        strokeStyle: "#999",
        strokeWidth: 1,
        strokeJoin: "miter"
    });
	
	// Mehrfachauswahl
	var mfa = $(cont).find("div.mfa");
	var mfaO = $(mfa).find("input[name=is_mfa]");
            
	if($(mfa)[0]){
		var mfap = $(mfa).find("p");
		var mfab = $(mfa).find("a.rbutton");
		
		// Mehrfachauswahl ausfahren
		rbutton(mfab, mfap, 'öffnen', 'schließen', function(){
			$(mfaO).val('true');
            $(baum).addClass("is_mfa");
			load_struktur();
		}, function(){
			$(mfaO).val('');
            $(baum).removeClass("is_mfa");
			load_struktur();
		}, 1);
		
		function getMFAselection(){
			var sel = '';
			$(baum).find("span.select input").filter(":checked").each(function(){
				sel += (!sel?'':',')+$(this).val();
			});
			return sel;
		}
		
		var umes = {
			del: 'in den Papierkorb verschieben möchten?',
			clone: 'duplizieren möchten?',
			close: 'sperren möchten?', 
			free: 'freischalten möchten?'
		};
		
		// Auswahl direkt verarbeiten
        function init_mfa(){
    		$(mfap).children("a.direct").off().on("click", function(){
    			var task = $(this).data('task');
    			var eanzahl = $(baum).find("span.select input").filter(":checked").length;
    			
    			sfrage_show('Sind Sie sicher, dass Sie die ausgewählten Strukturelemente ('+eanzahl+') '+umes[task]);
                $("#sfrage button:last").on("click", function(){
    				$(loadme).show();
    				
    				$.get('inc_structure.php', {
    					index: 'n123',
    					elemente: getMFAselection(),
    					task: task
    				}, function(data){ 
    					logincheck(data);
    					
    					if($(mfab).hasClass("rollin"))
    						$(mfab).trigger("click");
    				});
    			});
    		});
    		
    		// Auswahl bearbeiten
    		$(mfap).children("a.edit").off().on("click", function(){
                fenster({
                    id: 'n124',
                    width: 600,
                    blackscreen: '',
                    cb: function(nwin, ninhalt){
                        var selected_elemente = getMFAselection();
                        
                        $.get('inc_structure.php', {
        					index: 'n124',
        					elemente: selected_elemente
        				}, function(data){ 
        				    logincheck(data);
        					
        					$(ninhalt).html(data);
                            
                            $(ninhalt).find("div.more").hide();
                            
                            save_button(nwin);
                            calcDatePicker(ninhalt);
                            
                            var sb = $(ninhalt).find("div.box_save");
                            
                            $(ninhalt).find("p.checkme input[type=checkbox]").off().on("click", function(){
                                $(sb).slideDown();
                                var more = $(this).parents("div.area").children("div.more");
                                
                                if($(this).is(":checked")){
                                    $(more).slideDown(300, function(){
                                        set_button(nwin, sb);
                                    });
                                } else {
                                    $(more).slideUp(300, function(){
                                        set_button(nwin, sb);
                                    });
                                }
                            });
                            
                            $(ninhalt).find("table.zeitraum input.vonbis").on("click", function(){
                                var parentTR = $(this).parents("tr:first");
                                var td2 = $(parentTR).children("td.xstr"); 
                                var inp = $(parentTR).find("input, select").not(".vonbis");
                                
                                if($(this).is(":checked")){
                                    $(td2).removeClass("notaktiv");
                                    $(inp).removeAttr("disabled");
                                } else {
                                    $(td2).addClass("notaktiv");
                                    $(inp).attr("disabled", "disabled");
                                }
                            });
                            
                            $(sb).find("input.bs2").off().on("click", function(e){
                                e.preventDefault();
                                $(this).attr("disabled", true);
                                
                                $.post('inc_structure.php', {
            					   index: 'n125',
            					   elemente: selected_elemente,
        					       f: $(ninhalt).find("#mfa_bearbeiten").serialize()
                				}, function(data){ 
                                   logincheck(data);
                                   $(nwin).find("p.close").trigger("click");
                                   
                                   if($(mfab).hasClass("rollin"))
    						          $(mfab).trigger("click");
                                });
                            });
        				});
                    }
                });
    		});
        }
	}
    
    function load_struktur(newobj){
        $(loadme).show();
        
        // Initialisieren der wiederoeffnenenden Elemente
        var reopen = '';
        if($(baum).find("div.row")[0]){
            $(baum).find("a.reopen").each(function(){
                reopen += $(this).data('kat')+','; 
            }); 
        }
        
        $.get('inc_structure.php', {
            index: 'n121',
            open: reopen,
            just_select: just_select,
			mfa: $(mfaO).val(),
			show_all: ($(cont).find("input[name=show_all]").is(":checked")?'true':'')
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
                new_child(0, 0);
            });
                
            // Komplette Struktur ausklappen
            $(cont).find("a.open_all").off().on("click", function(e){
                $(row).not(".is_open").find("a.klappen").trigger("click");
            });
                
            // Komplette Struktur einklappen
            $(cont).find("a.close_all").off().on("click", function(e){
                $(row).filter(".is_open").find("a.klappen").trigger("click");
            });
            
            if(!$(zweig)[0])
                return false;
            
            $(canvas).attr("height", $(baum).height());
			
			// Versteckte anzeigen
			$(cont).find("input[name=show_all]").off().on("click", function(){
				load_struktur(newobj);
			});
			
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
                var srow = $(this).parents("div.row:first");
                var szweige = $(srow).siblings("div.zweig");
                
                if($(this).data('open') == 'true'){
                    $(szweige).hide();
                    load_canvas();
                    $(this).removeClass("reopen").data('open', 'false').children("strong").html('aufklappen');
                    $(srow).removeClass("is_open");
                } else {
                    $(szweige).show();
                    load_canvas();
                    $(this).addClass("reopen").data('open', 'true').children("strong").html('zuklappen');
                    $(srow).addClass("is_open");
                }
            });
            
            // Schon bei Start alte Elemente wieder ausklappen
            $(row).find("a.reopen").trigger("click");
            
			
			if($(mfaO).val() == 'true' && $(mfa)[0]){ // Mehrfachauswahl
                $(mopt).remove();
                $(more).find("div.move").remove();
                $(cont).find("button.new").remove();
                $(whites).find("a.name").remove();
                
                $(baum).find("span.select input").off().on("click", function(){ 
                    if($(baum).find("span.select input").filter(":checked").length > 0){
                        $(mfap).children("a").removeClass("disabled");
                        init_mfa();
                    } else {
                        $(mfap).children("a").addClass("disabled").off("click");
                    }
                });
			
			} else if(just_select){ // Wenn man nur ein Element selektieren will
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
                    open_strukturelement(z_id);
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
                                index: 'n122',
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
                    
                    sfrage_show('Wollen Sie dieses Element wirklich unwiederruflich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $(loadme).show();
                        $(myrow).hide();
                        
                        $.post('inc_structure.php', {
                            index: 'n122',
                            id: did,
                            task: 'remove'
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
                    
                    sfrage_show('Wollen Sie dieses Element samt Kindelementen wirklich eine Ebene höher verschieben?');
                    $("#sfrage button:last").on("click", function(){
                        $(loadme).show();
                        $(myrow).hide();
                        
                        $.post('inc_structure.php', {
                            index: 'n122',
                            id: did,
                            task: 'move_higher'
                        }, function(data){ logincheck(data);
                            load_struktur(data);
                        });         
                    });
                });
                
                // Element anderem Element zuordnen
                $(mopt).find("a.move_another").off().on("click", function(){
                    var myrow = $(this).parents("div.row");
                    var did = $(this).parents("div.optarea").data('kat');
                    
                    sfrage_show('Wenn Sie dieses Element samt Kindelementen einem anderen Element zuordnen wollen, klicken Sie auf weiter und wählen Sie anschließend im Baum das gewünschte Element aus.');
                    $("#sfrage button:last").on("click", function(){
                        var white = $(row).find("div.white");
                        
                        $(white).find("a").off();
                        $(white).addClass("chooseme").off("click").on("click", function(){
                            $(loadme).show();
                            var toid = $(this).data('kat');
                            
                            $.post('inc_structure.php', {
                                index: 'n122',
                                id: did,
                                task: 'move_another',
                                to: toid
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
                    new_child(0, did);
                });
                
                // Hinzufügen Geschwister
                $(mopt).find("a.add_sibling").off().on("click", function(){
                    $(this).off("click");
                    var did = $(this).parents("div.optarea").data('kat');
                    new_child(did, 0);
                });
            }
            
            // Canvas-Element zeichen
            function load_canvas(){
                if(!$(canvas)[0] || !$(zweig).filter(".zweig_0")[0])
                    return false;
					
				$(canvas).attr("height", ($(baum).height() + 30));
                
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
            index: 'n122',
            id: did,
            task: 'new',
            type: type
        }, function(data){ logincheck(data);
            if(parseInt(data) > 0){
                open_strukturelement(data, true);
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
        
    $(cont).parents("table.fenster:first").find("p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        load_struktur();
    });      
}    


$.ui.plugin.add("resizable", "alsoResizeReverse", {

    start: function(event, ui) {

        var self = $(this).data("resizable"), o = self.options;

        var _store = function(exp) {
            $(exp).each(function() {
                $(this).data("resizable-alsoresize-reverse", {
                    width: parseInt($(this).width(), 10), height: parseInt($(this).height(), 10),
                    left: parseInt($(this).css('left'), 10), top: parseInt($(this).css('top'), 10),
                    laenge: $(exp).length
                });
            });
        };

        if (typeof(o.alsoResizeReverse) == 'object' && !o.alsoResizeReverse.parentNode) {
            if (o.alsoResizeReverse.length) { o.alsoResize = o.alsoResizeReverse[0];    _store(o.alsoResizeReverse); }
            else { $.each(o.alsoResizeReverse, function(exp, c) { _store(exp); }); }
        }else{
            _store(o.alsoResizeReverse);
        }
    },

    resize: function(event, ui){
        var self = $(this).data("resizable"), o = self.options, os = self.originalSize, op = self.originalPosition;

        var delta = {
            height: (self.size.height - os.height) || 0, width: (self.size.width - os.width) || 0,
            top: (self.position.top - op.top) || 0, left: (self.position.left - op.left) || 0
        },

        _alsoResizeReverse = function(exp, c) {
            $(exp).each(function() {
                var el = $(this), start = $(this).data("resizable-alsoresize-reverse"), style = {}, css = c && c.length ? c : ['width', 'height', 'top', 'left'];

                $.each(css || ['width', 'height', 'top', 'left'], function(i, prop) {
                    var sum = (start[prop]||0) - (delta[prop]||0); // subtracting instead of adding
                    if (sum && sum >= 0)
                        style[prop] = sum || null;
                });

                //Opera fixing relative position
                if (/relative/.test(el.css('position')) && $.browser.opera) {
                    self._revertToRelativePosition = true;
                    el.css({ position: 'absolute', top: 'auto', left: 'auto' });
                }

                el.css(style);
            });
        };

        if (typeof(o.alsoResizeReverse) == 'object' && !o.alsoResizeReverse.nodeType) {
            $.each(o.alsoResizeReverse, function(exp, c) { _alsoResizeReverse(exp, c); });
        }else{
            _alsoResizeReverse(o.alsoResizeReverse);
        }
    },

    stop: function(event, ui){
        var self = $(this).data("resizable");

        //Opera fixing relative position
        if (self._revertToRelativePosition && $.browser.opera) {
            self._revertToRelativePosition = false;
            el.css({ position: 'relative' });
        }

        $(this).removeData("resizable-alsoresize-reverse");
    }
});


$.fn.autogrow = function(options) {
        
    this.filter('textarea').each(function() {
        
        var $this       = $(this),
            minHeight   = $this.height(),
            lineHeight  = $this.css('lineHeight');
        
        var shadow = $('<div></div>').css({
            position:   'absolute',
            top:        -10000,
            left:       -10000,
            width:      $(this).width() - parseInt($this.css('paddingLeft')) - parseInt($this.css('paddingRight')),
            fontSize:   $this.css('fontSize'),
            fontFamily: $this.css('fontFamily'),
            lineHeight: $this.css('lineHeight'),
            resize:     'none'
        }).appendTo(document.body);
        
        var update = function() {
    
            var times = function(string, number) {
                for (var i = 0, r = ''; i < number; i ++) r += string;
                return r;
            };
            
            var val = this.value.replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/&/g, '&amp;')
                                .replace(/\n$/, '<br/>&nbsp;')
                                .replace(/\n/g, '<br/>')
                                .replace(/ {2,}/g, function(space) { return times('&nbsp;', space.length -1) + ' ' });
            
            shadow.html(val);
            $(this).css('height', Math.max(shadow.height() + 20, minHeight));
        
        }
        
        $(this).change(update).keyup(update).keydown(update);
        $("#format img").mouseup(update);
        
        update.apply(this);
        
    });
    
    return this;
    
}


function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
}



function dko_start(cb_start, cb_loop){
    if($("#fn290")[0] || $("#fn295")[0]){
        if($("#fn295")[0]){
            var add_width = 120;
            var mywindow = $("#fn295"); 
        } else{
            var add_width = 0;
            var mywindow = $("#fn290");
        } 
            
        var fn290 = $(mywindow).find("div.inhalt"); 
            
        var dktable = $(fn290).find("table.overview");
        var thead = $(dktable).find("tr.head");
        
        var q = $(fn290).find("input[name=q]");
        var dk = $(fn290).find("input[name=datei]").val();
        var slug = $(fn290).find("input[name=slug]").val();
        var dklasse = $(fn290).find("input[name=klasse]").val();
        var anz_spalten = $(fn290).find("input[name=anz_spalten]").val();
        var breiten = $(fn290).find("input[name=breiten]").val();
        var laengen = $(fn290).find("input[name=laengen]").val();
        var choose = $(fn290).find("input[name=choose]").val();
        
        var realspalten = $(thead).find("th").length;
        var limit = 15;
        var loading = '<tr class="loading"><td colspan="'+realspalten+'"><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>';
        
        var gbreite = parseInt($(fn290).find("input[name=gbreite]").val()) + 685 + add_width; 
        if($(dktable).outerWidth(true) > gbreite + 80)
            gbreite = $(dktable).outerWidth(true) + 80;
            
        $(mywindow).css({
            "width": gbreite+"px",
            "left": ($("#main").width() / 2 - gbreite / 2) + "px"
        }).find("p.move").width((gbreite - 164));
        
        $(fn290).find("button.new_doc").off("click").on("click", function(e){
            e.preventDefault();
            var newlink = $('<a class="inc_documents" id="n210" rel="fks_dk_'+dk+'"></a>');
            neu($(newlink));
        });
        
        function reload_dk(){
            $(thead).after(loading);
            
            var sort = $(thead).find("th.sort").data('sort');
            var sort2 = ($(thead).find("th.sort").hasClass("desc")?"DESC":"ASC");
            
            $.post('inc_documents.php', {
                index: 'n291',
                klasse: dklasse,
                slug: slug,
                spalten: anz_spalten,
                breiten: breiten,
                laengen: laengen,
                q: $(q).val(),
                cats: $(fn290).find("form.category_selection").serialize(),
                sort: sort,
                sort2: sort2,
                choose: choose,
                limit: limit,
                realspalten: realspalten
            }, function(data){
                logincheck(data);
                
                $(dktable).find("tr").not(".head").remove();
                $(thead).after(data);
                
                $(dktable).find("tr.serp a").off().on("click", function(){
                    neu($(this));
                });
        
                var mr = $(dktable).find("td.more_results");
                if($(mr)[0]){
                    $(mr).children("a.next").off("click").on("click", function(){ 
                        limit += 15;
                        reload_dk();
                    });
                    
                    $(mr).children("a.all").off("click").on("click", function(){ 
                        limit = 1000000000;
                        reload_dk();
                    });
                }
                
                if($.isFunction(cb_loop))
                    cb_loop(fn290, dktable);
            });
        }
        reload_dk();
        
        $(mywindow).find("p.move a.reload").off().on("click", function(ev){
            ev.preventDefault();
            reload_dk();
        });
        
        var lastsort = '';        
        $(thead).find("th").disableSelection().off().on("click", function(){
            $(thead).find("th").removeClass("sort desc asc");
            $(this).addClass("sort");
            
            if($(this).data('sort') == lastsort){
                $(this).addClass("desc");
                lastsort = '';
            } else {
                $(this).addClass("asc");
                lastsort = $(this).data('sort');
            }
            
            reload_dk();
        });
        
        var search_timeout = null;
        $(q).off("keyup change").on("keyup change", function(){
            clearTimeout(search_timeout);
            search_timeout = setTimeout(function(){
                reload_dk();
            }, 250);
        });


        if($.isFunction(cb_start))
            cb_start(fn290);


        $(fn290).find("a.rbutton").each(function(){
            var sib = $(this).siblings("div.opt");
            rbutton($(this), $(sib), 'einblenden', 'ausblenden');
        });

        $(fn290).find("form.category_selection input").off().on("click", function(){
            reload_dk();
        });
    }
}


function startImageEdit(opt){
    opt = correctOptions(opt);

    fenster({
        id: 'n460',
        width: 980,
        blackscreen: opt.blackscreen,
        cb: function(neww, ncontent){
            $.post('inc_files.php', {
                index: 'n460',
                file: opt.file,
                file_version: opt.file_version
            }, function(data){
                $(ncontent).html(data);
                save_button(neww);
                
                var sb = $(ncontent).find("div.box_save");
                
                var editO = $(ncontent).find("#picedit");
                var pic = $(editO).find("img#cropbox"); 
                var vh = parseFloat($(editO).find("#pic_vh").val()); 
                var crop = null; 
                
                $(pic).off("load").on("load", function(){
                    set_button(neww, sb); 
                });
                
                // Bild zuschneiden
                var pic_w = $(editO).find("#pic_w, #pic_breite");
                var pic_h = $(editO).find("#pic_h, #pic_hoehe");
                var pic_x = $(editO).find("#pic_x");
                var pic_y = $(editO).find("#pic_y");
                var pic_s = $(editO).find("#pic_s");
                
                function showCoords(c)
            	{
                    var w = parseInt(c.w * vh); 
                    var h = parseInt(c.h * vh);
                    var x = parseInt(c.x * vh);
                    var y = parseInt(c.y * vh);
                    
                    $(pic_w).val(w);
                    $(pic_h).val(h);
                    $(pic_x).val(x);
                    $(pic_y).val(y);
            	}   
                
                $(editO).find("#go_zuschnitt").off("click").on("click", function(e){
                    $(this).hide().parent("p").children("span").slideDown();
                    $(editO).find("#table_zuschnitt").fadeIn().after('<span class="trenn"></span>');
                    
                    crop = $.Jcrop($(pic), {
                		onChange: showCoords,
                		onSelect: showCoords
                	});
                }); 
                
                $(editO).find("div.croparea span").off().on("click", function(){
                    $(this).siblings("span").removeClass("active");
                    $(this).addClass("active");
                });
                
                $(neww).find("p.close").on("click", function(){
                    if(crop){
                        crop.destroy();
                        crop = null;
                    }
                });
                
                $(sb).find("input.bs2").off().on("click", function(e){ 
                    e.preventDefault();
                    $(this).attr("disabled", true);
                    
                    var thumb_cropped = parseInt($(editO).find("div.croparea span.active").data('crop'));
                    
                    var callback_object = {
                        title: $(ncontent).find("#pic_titel").val(),
                        descr: $(ncontent).find("#pic_desc").val(),
                        id: opt.file,
                        cropped: thumb_cropped   
                    };
                    
                    $.post('inc_files.php', {
                        index: 'n461',
                        stackid: opt.file,
                        fileid: opt.file_version,
                        w: $(editO).find("#pic_w").val(),
                        h: $(editO).find("#pic_h").val(),
                        x: $(editO).find("#pic_x").val(),
                        y: $(editO).find("#pic_y").val(),
                        c: $(editO).find("#pic_c").val(),
                        b: $(editO).find("#pic_b").val(),
                        s: ($(editO).find("#pic_s").is(":checked")?1:0),
                        cropped: thumb_cropped,
                        titel: $(ncontent).find("#pic_titel").val(),
                        desc: $(ncontent).find("#pic_desc").val(),
                        cf_form: $(ncontent).find("form.ufields").serialize()
                    }, function(){
                        if($.isFunction(opt.callback))
                            opt.callback(callback_object);
                            
                        $(neww).find("p.close").trigger("click");
                    });
                    
                    $(editO).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                });
            });
        }
    }); 
}

function startImageSelect(opt){
    opt = correctOptions(opt);

    fenster({
        id: 'n270',
        blackscreen: opt.blackscreen,
        width: 980,
        cb: function(neww, iscontent){

            $.get('inc_documents.php', {
                index: 'n270',
                rel: 1
            }, function(data){
                logincheck(data);

                $(iscontent).html(data);
                setFocus(neww);

                $(iscontent).find(".bwL input").off().on("click keyup", showThumbnails);

                $(iscontent).find(".bwL .dropdown").find("span").off().on("click", function(){
                    $(this).toggleClass("funten").nextAll().toggle();
                }).toggleClass("funten").nextAll().toggle();

                var bladen = 28;
                var tdir = 0;
                var sbilder = $(iscontent).find("#s_bilder");
                var p2g = $(iscontent).find("#pics2gal");

                function showThumbnails(){
                    var tchoosen = new Array();

                    $(sbilder).find("div.dirs, div.pre").remove();
                    $(sbilder).find("div.dirbr").after('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');

                    var dt = '';
                    if($(iscontent).find("#bw_dateityp_1:checked").length > 0) dt += 'jpg|';
                    if($(iscontent).find("#bw_dateityp_2:checked").length > 0) dt += 'gif|';
                    if($(iscontent).find("#bw_dateityp_3:checked").length > 0) dt += 'png|';
                    var ar = '';
                    if($(iscontent).find("#bw_ausrichtung_1:checked").length > 0) ar += 'h|';
                    if($(iscontent).find("#bw_ausrichtung_2:checked").length > 0) ar += 'v|';
                    var sort1 = '';
                    if($(iscontent).find("#bw_sort1_1:checked").length > 0) sort1 = 'titel';
                    if($(iscontent).find("#bw_sort1_2:checked").length > 0) sort1 = 'id';
                    if($(iscontent).find("#bw_sort1_3:checked").length > 0) sort1 = 'last_timestamp';
                    if($(iscontent).find("#bw_sort1_4:checked").length > 0) sort1 = 'last_autor';
                    var sort2 = '';
                    if($(iscontent).find("#bw_sort2_1:checked").length > 0) sort2 = 'asc';
                    if($(iscontent).find("#bw_sort2_2:checked").length > 0) sort2 = 'desc';

                    $.get('inc_documents.php', {
                        index : 'n271',
                        qT: $(iscontent).find("#bw_qT").val(),
                        rel: 1,
                        dt : dt,
                        ar : ar,
                        sort1 : sort1,
                        sort2 : sort2,
                        laden: bladen,
                        dir: tdir,
                        mfa: 0
                    }, function(data){
                        logincheck(data);
                        $(sbilder).html(data);

                        var preO = $(sbilder).find("div.pre");

                        $(sbilder).find("a.moreA").off().on("click", function(){
                            bladen += 14;
                            showThumbnails();
                        });
                        $(sbilder).find("a.moreB").off().on("click", function(){
                            bladen += 999999999999;
                            showThumbnails();
                        });

                        // Ordner-Struktur
                        $(sbilder).find("div.dirbr a, div.dirs a").off("click").on("click", function(){
                            tdir = $(this).attr("rel");
                            showThumbnails();
                        });

                        function bild_hover(){
                            $(preO).off("mouseenter").on("mouseenter", function(){
                                var pre = $(this);

                                $(pre).css("border", "1px solid #fff");
                                $(pre).children("img").show().css({
                                    "top": ((14 - $(pre).children("img").height()) / 2 + $(pre).offset().top - $(window).scrollTop()) + "px",
                                    "left": ($(pre).offset().left - 19) + "px"
                                });
                            });
                        }
                        bild_hover();

                        $(preO).off("mouseleave").on("mouseleave", function(){
                            $(this).css("border", "1px solid #000");
                            $(this).children("img").hide();
                        });


                        $(preO).find("a").off("click").on("click", function(){
                            var file = {
                                id: $(this).data('id'),
                                title: $(this).data('title'),
                                width: $(this).data('width'),
                                height: $(this).data('height'),
                                thumb100: $(this).data('thumb100'),
                                thumb160: $(this).data('thumb160'),
                                thumb200: $(this).data('thumb200'),
                                thumb100h: $(this).data('thumb100h')
                            };

                            if($.isFunction(opt.selected))
                                opt.selected(file);

                            $(neww).find("p.close").trigger("click");
                        });

                        $(preO).find("img").off("click").on("click", function(){
                            $(this).siblings("a").trigger("click");
                        });

                    });
                }
                showThumbnails();
            });
        }
    });
}

function startUpload(opt){
    opt = correctOptions(opt);

    fenster({
        id: 'n410',
        width: 900,
        blackscreen: opt.blackscreen,
        cb: function(neww, ncontent){
            $.post('inc_files.php', {
                index: 'n410',
                dir: parseInt(opt.dir),
                images: (opt.images?1:0),
                parent: (opt.parent?opt.parent:0),
                limit: (opt.limit?opt.limit:100)
            }, function(data){
                $(ncontent).html(data);
                save_button(neww);
                
                var button_bar = $(ncontent).find("div.fileupload-buttonbar");
                var button_add = $(button_bar).find("button.fileinput-button");
                var button_start = $(button_bar).find("button.start");
                var button_cancel = $(button_bar).find("button.cancel");
                var button_delete = $(button_bar).find("button.delete");
                var whole_progress = $(ncontent).find("div.fileupload-progress");
                
                var upload_count = 0;
                var upload_size_limit = parseInt($(ncontent).find("#fileupload").data('max-upload'));
                var the_files = new Array();
                
                $(ncontent).find("#fileupload").fileupload({
                    url: 'inc_files.php',
                    acceptFileTypes: (opt.images?/(png)|(jpe?g)|(gif)$/i:/(..)|(...)|(....)|(.....)$/i),
                    previewAsCanvas: false,
                    autoUpload: true,
                    prependFiles: true,
                    limitMultiFileUploads: (opt.limit?opt.limit:100),
                    maxFileSize: (upload_size_limit > 0?upload_size_limit:314572800),
                    formData: [
                        {
                            name: 'index',
                            value: 'n400_upload'
                        },
                        {
                            name: 'a',
                            value: 'upload_'+(opt.images?'1':'2')
                        },
                        {
                            name: 'ordner',
                            value: parseInt(opt.dir)
                        },
                        {
                            name: 'stack',
                            value: (opt.parent?opt.parent:0)
                        }
                    ],
                    done: function(e, data){                            
                        var that = $(this).data('fileupload'), template;
                        
                        var info = data.jqXHR.responseText;
                        if(info){
                            var jobj = jQuery.parseJSON(info); 
                            if(jobj.status == "ok"){ 
                                for(var c = 0; c < jobj.files.length; c++){
                                    var file = jobj.files[c];
                                    the_files.push(file);
                                
                                    var my_tr = $(ncontent).find("#fileupload tr.template-upload").filter('[data-name="'+file.original_name+'"]')[0]; 
                                    if(!$(my_tr)[0])
                                        continue;
                                        
                                    var my_img = $(my_tr).find("td.preview");
                                    
                                    if(!$(my_img).find("img")[0]){ 
                                        var new_img = $('<img src="'+file.thumbnail_url+'" alt=" " />'); 
                                        $(my_img).html(new_img);    
                                        
                                        $(new_img).off().on("load", function(){
                                            $(my_img).addClass("loaded");
                                        });
                                        
                                        $(my_tr).find("td.name").html(file.show_name);
                                        $(my_tr).find("div.ui-progressbar").attr("aria-valuenow", 100).hide().siblings("div.upload-successed").show();
                                        
                                        $(my_tr).find("td.edit a").show().off().on("click", function(){
                                            if(!file.id || !file.file_id)
                                                return false;
                                            
                                            startImageEdit({
                                                blackscreen: opt.blackscreen_edit,
                                                file: file.id,
                                                file_version: file.file_id,
                                                callback: function(rtn){
                                                    if($.isFunction(opt.refresh))
                                                        opt.refresh();
                                                        
                                                    $(my_tr).find("td.name span").html(rtn.title);
                                                    $(new_img).attr("src", file.thumbnail_url+'?random='+Math.random());
                                                }
                                            });  
                                        });
                                        
                                        if(opt.hide_edit)
                                            $(my_tr).find("td.edit a").remove();
                                    }
                                }
                            } 
                        }
                        
                        
                        var allDone = true;
                        $(ncontent).find("#fileupload table div.progress").each(function(){
                            var valuenow = parseInt($(this).attr("aria-valuenow"));
                            if(valuenow < 100)
                                allDone = false;
                        });
                        
                        if(allDone){
                            $(whole_progress).hide();
                            
                            if($.isFunction(opt.refresh))
                                opt.refresh(neww, the_files);
                            the_files = new Array();
                        }
                    },
                    add: function(e, data){ 
                        
                        var that = $(this).data('fileupload'),
                            options = that.options,
                            files = data.files;
                            
                        $(this).fileupload('process', data).done(function () {
                            that._adjustMaxNumberOfFiles(-files.length);
                            data.isAdjusted = true;
                            data.files.valid = data.isValidated = that._validate(files);
                            data.context = that._renderUpload(files).data('data', data);
                            options.filesContainer[
                                options.prependFiles ? 'prepend' : 'append'
                            ](data.context);
                            that._renderPreviews(files, data.context);
                            that._forceReflow(data.context);
                            that._transition(data.context).done(
                                function () {
                                    if ((that._trigger('added', e, data) !== false) &&
                                            (options.autoUpload || data.autoUpload) &&
                                            data.autoUpload !== false && data.isValidated) {
                                        data.submit();
                                    }
                                }
                            );
                        });
                        
                        
                        $(ncontent).find("#fileupload td.error").each(function(){
                            var partr = $(this).parent("tr");
                            
                            if(!$(partr).hasClass("has_error")){
                                $(partr).addClass("has_error");
                            }
                        });
                    },
                    send: function(e, data){
                        $(whole_progress).show();
                        
                        var that = $(this).data('fileupload');
                            
                        if (!data.isValidated) {
                            if (!data.isAdjusted) {
                                that._adjustMaxNumberOfFiles(-data.files.length);
                            }
                            if (!that._validate(data.files)) {
                                return false;
                            }
                        }
                        if (data.context && data.dataType &&
                                data.dataType.substr(0, 6) === 'iframe') {
                            // Iframe Transport does not support progress events.
                            // In lack of an indeterminate progress bar, we set
                            // the progress to 100%, showing the full animated bar:
                            data.context
                                .find('.progress').addClass(
                                    !$.support.transition && 'progress-animated'
                                )
                                .attr('aria-valuenow', 100)
                                .find('.bar').css(
                                    'width',
                                    '100%'
                                );
                        }
                        return that._trigger('sent', e, data);   
                    }
                });
            });           
        }
    });
}


function chooseDir(opt){
    opt = correctOptions(opt);

    fenster({
        id: 'n480',
        blackscreen: opt.blackscreen,
        width: 620,
        cb: function(neww, ninhalt){
            $.post('inc_files.php', {
                index: 'dir',
                kat: 0,
                ordner: parseInt(opt.active)
            }, function(data){ 
                logincheck(data);
                $(ninhalt).html(data);
                setFocus(neww);
                
                $(ninhalt).find("p.titel a").off().on("click", function(e){
                    e.preventDefault();
                    
                    var dir = $(this).attr("rel");
                    var dtitle = $(this).html();
                    
                    if($.isFunction(opt.cb))
                        opt.cb(dir, dtitle);
                    
                    $(neww).find("p.close").trigger("click");
                });
            });
        }
    });
}


function chooseDocument(opt){
    opt = correctOptions(opt);

    fenster({
        id: 'n142',
        width: 964,
        blackscreen: opt.blackscreen,
        cb: function(nwin, ninhalt){
            $.post('inc_documents.php', {
                index: 'n200',
                rel: 0
            }, function(data){ 
                logincheck(data);
            
                $(ninhalt).html(data);
                setFocus(nwin);
                       
                function getDocuments(){
                
                    dok_uebersicht_2(ninhalt);
                    
                    $.get('inc_documents.php', {
                        index: 'n201',
                        q: d_search,
                        opt: d_opt,
                        dklassen: d_dklassen,
                        sortA: d_sortA,
                        sortB: d_sortB,
                        limit: $(ninhalt).find("input#akt_limit").val(),
                        rel: 0
                    }, function(data){ 
                        logincheck(data); 
                        
                        var table = $(ninhalt).find("#dokumente table#docs_auflistung");
                        $(table).html(data);
                        
                        $(table).find("td a").off("click").on("click", function(e){
                            var parent_tr = $(this).parents("tr.entry");
                            var doc_id = $(parent_tr).data('id');
                            var doc_title = $(parent_tr).data('title');
                            
                            if($.isFunction(opt.cb))
                                opt.cb(doc_id, doc_title); 
                    
                            $(nwin).find("p.close").trigger("click");   
                        });
                        
                        dok_uebersicht_3(ninhalt, table, getDocuments);
                        dok_uebersicht_4(ninhalt, table, getDocuments);
                    }); 
                }
                getDocuments();
                
                dok_uebersicht_1(ninhalt, getDocuments);
                
                $(ninhalt).find("button.inc_documents").remove();
            });
        }
    });    
}


function newDocumentScript(){
    var fn210 = $("#fn210");
    var sb = $(fn210).find("div.box_save");
    var vorlagev = $(fn210).find("#vorlage_verwenden"); 
    var neu_zsb = $(fn210).find("#neu_zsb"); 
    var dokument_title = $(fn210).find("tr.dokument_title td");
    
    $(fn210).find("#doc_neu input, #doc_neu select").off("keyup change").on("keyup change", function(){
        if($(sb).css("display") == "none"){
            $(sb).show();
            set_button(fn210, sb);
        }
    });
    
    $(sb).find("input:first").off("click").on("click", function(){
        $(fn210).find("p.close").trigger("click");
    });
    
    $(sb).find("input:last").off("click").on("click", function(){
        var selfbutton = $(this);
        $(selfbutton).attr("disabled", "disabled").off("click");
        
        $.post('inc_documents.php', {
            index: 'n211',
            all: $(fn210).find("form").serialize()
        }, function(data){ logincheck(data);
            $(selfbutton).removeAttr("disabled");
            $(fn210).find("p.close").trigger("click");
            
            if(!$(fn210).find("input[name=do_not_open]")[0]){
                var nextA = data.split("____");
                var next = $('<span class="inc_documents" id="n250">'+nextA[1]+'<a rel="'+nextA[0]+'"></a></span>'); 
                neu(next);
            
                if(typeof dokumente_start !== 'undefined')
                    dokumente_start();
                if(typeof dko_start !== 'undefined')
                    dko_start();
            }
            
            extern_strukturelement_strukdok();
        }); 
    });
    
    $(fn210).find("select[name=klasse]").off("change").on("change", function(){ 
        if($(this).val() != ''){
            $(vorlagev).fadeOut();
            $(neu_zsb).find("td").addClass("last");
        } else {
            $(vorlagev).fadeIn();
            $(neu_zsb).find("td").removeClass("last");
        }
        
        var selected = $(this).find('option:selected');
        var extra = selected.data('notitel'); 
        
        if(extra+'' == 'true') {
            $(dokument_title).hide();
        } else {
            $(dokument_title).show();
        }
        
        $(sb).show();
    });
    
    var selected = $(fn210).find("select[name=klasse] option:selected");
    var extra = selected.data('notitel'); 
    
    if(extra+'' == 'true') {
        $(dokument_title).hide();
        $(sb).show();
    } else {
        $(dokument_title).show();
    }
    
    $(fn210).find("input[name=zsb]").off("change click").on("change click", function(){
        if($(this).is(":checked")){
            $(neu_zsb).find("div.vbox2").slideDown();
        } else {
            $(neu_zsb).find("div.vbox2").slideUp();
        }
        
        $(sb).show();
    });
    
    $(fn210).find("input[name=vorlage]").off("change click").on("change click", function(){
        if($(this).is(":checked")){
            $(vorlagev).find("div.vbox2").slideDown();
        } else {
            $(vorlagev).find("div.vbox2").slideUp();
        }
    });
    
    $(fn210).find("input[name=type]").off("change click").on("change click", function(){
        $(this).parent().siblings("div.vbox2").find("p").slideUp();
        $(this).siblings("p").slideDown();
    });
    
    // DOKUMENT SUCH FENSTER 
    $(fn210).find("button#nd_choose").off("click").on("click", function(e){
        e.stopPropagation();
        e.preventDefault();
        clicked = $(this);
        
        fenster({
            id: 'n216',
            blackscreen: '',
            width: 964,
            cb: function(neww, inhalt){
        
                $.post('inc_documents.php', {
                    index: 'n200',
                    rel: 0,
                    eid: 'nd'
                }, function(data){ logincheck(data);
                
                    $(inhalt).html(data);
                    setFocus(neww);
                    
                    /// EXAKT WIE BEI DEN DOKUMENTEN O'REALLY            
                    function documents_intern_start() {
                        var fn200 = inhalt; 
                        
                        function doc_verwalten_inhalt(){
                        
                            dok_uebersicht_2(fn200);
                            
                            $.get('inc_documents.php', {
                                index: 'n201',
                                q: d_search,
                                opt: d_opt,
                                dklassen: d_dklassen,
                                sortA: d_sortA,
                                sortB: d_sortB,
                                limit: $(fn200).find("input#akt_limit").val(),
                                rel: 0
                            }, function(data){ logincheck(data); 
                                var table = $(fn200).find("#dokumente table#docs_auflistung");
                                $(table).html(data);
                                
                                $(table).find("td a.inc_documents").off("click").on("click", function(){
                                    $(fn210).find("input[name=gew_dok]").val($(this).attr("rel"));
                                    $(fn210).find("#gew_dok").html($(this).text());
                                    
                                    $(neww).find("p.close").trigger("click", function(){ 
                                        $("#blackscreen2").remove(); 
                                    });
                                });
                                
                                dok_uebersicht_3(fn200, table, doc_verwalten_inhalt);
                                dok_uebersicht_4(fn200, table, doc_verwalten_inhalt);
                            }); 
                        }
                        doc_verwalten_inhalt();
                        
                        dok_uebersicht_1(fn200, doc_verwalten_inhalt);
                        
                        $(fn200).find("button.inc_documents").remove();
                    }
                    documents_intern_start();
                });
            }
        });                            
    });
}

function openAppWindow(app_id, app_width){
    fenster({
        id: 'app-'+app_id,
        width: app_width,
        blackscreen: 'none',
        cb: function(nwin, ncontent){
            $.post('app.php', {
                id: app_id
            }, function(data){ 
                logincheck(data);
                $(ncontent).html(data);
        
                save_button(nwin); 
                
                var app = $(ncontent).find("div.fks-app");
                var js_file = $(app).data('js');
                var css_file = $(app).data('css');
                var win_width = $(app).data('width');
                var nsb = $(ncontent).children("div.box_save");
                var hiddeninputs = $(ncontent).find("div.hidden-inputs");
                
                if(!app_width)
                    $(nwin).width(win_width).css('left', ($("#main").width() / 2 - win_width / 2) + 'px').find("p.move").width((win_width - 164));
                
                if(js_file){
                    $.getScript(js_file, function(){
                        set_button(nwin, nsb);

                        setTimeout(function(){
                            set_button(nwin, nsb);
                        }, 600);
                    });
                }

                if(css_file){
                    $.get(css_file, function(css){
                        $('<style type="text/css"></style>').html(css).appendTo("head");
                        set_button(nwin, nsb);
                    });
                }

                setTimeout(function(){
                    set_button(nwin, nsb);
                }, 600);
                    
                $(nsb).find("input.bs2").off().on("click", function(e){
                    e.preventDefault();
                    $(this).attr("disabled", true);

                    // insert hidden inputs
                    $(app).find("input, textarea, select").each(function(){
                        var iname = $(this).attr("name");
                        if(iname == '' || !iname)
                            return true;

                        $(hiddeninputs).append('<input type="hidden" name="fks-app-hidden-inputs[]" value="'+iname+'" />');
                    });
                    
                    $.post('app.php', {
                        id: app_id,
                        save: true,
                        f: $(ncontent).children("form.autosave").serialize()
                    }, function(data){
                        $(nwin).find("p.close").trigger("click");
                    });
                });
            });   
        }
    });
}