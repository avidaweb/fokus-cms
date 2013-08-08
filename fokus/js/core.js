
/// LAUFZEIT VARIABELN
var zindex = 3000;
var dyn = 980;
var rel = 0;
var lastindex = 0;
var lan = 'de';
var input_lan = 'de';
var waiting = '<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />';

var navi_einblend_dauer = (debug?10:1200);

var tab_aktiv = true;

///////////////// FÜR DIE DOKUMENTE
var d_opt = '';
var d_dklassen = '';
var d_sortA = 'timestamp_edit';
var d_sortB = 'DESC';
var d_sortC = '';
var d_lastsort = '';
var d_search = '';

///////////////// FÜR DIE PERSONEN
var p_opt = '';
var p_sortA = 'id';
var p_sortB = 'DESC';
var p_sortC = '';
var p_lastsort = '';
var p_search = '';

//////////////// FÜR DIE FIRMEN
var f_opt = '';
var f_sortA = '';
var f_sortB = '';
var f_sortC = '';
var f_search = '';

////////////// FÜR DIE GALERIE
var dirs = new Array(0,0,0,0);

////////////// FÜR DIE PNS
var pn_limit = 10;
var pn_aktiv = 0;
var pn_timer = null;

///////////// SCROLLING
var scroll_doks = false;
var scroll_pics = false;

var reload_button = ['n200', 'n295', 'n290', 'n640', 'n510', 'n515', 'n400', 'n120', 'n130', 's110', 's115', 's120', 'n650'];

    
function start(){
    $.get('nav.php', {
        language : lan
    }, function(data){
        $("nav").remove();
        $("footer").remove();
        $("#main").before(data);
        init();
    }); 
}

function sprache_wechseln(tclicked){
    sfrage_show('Wollen Sie wirklich die Eingabe-Sprache &auml;ndern? Alle aktiven Fenster der Bereiche STRUKTUR und DOKUMENTE werden geschlossen!');
    $("#sfrage button:last").on("click", function(){
        lan2 = $(tclicked).attr("class");
        
        $.get('language/input_language.php', {
            input: lan2
        }, function(data) {
            input_lan = lan2;
            
            $.getScript('js/language.js'); 
            
            $.get('nav.php', {
                language : lan
            }, function(data){
                $("nav").remove();
                $("footer").remove();
                $("#main").before(data);
                init();
            });
        });
    });
}
    
function init(){
    var naviO = $("#navigationO");
    
    if($(window).width() < navi_breite){
        var navi_breiteN = $(window).width();
        $(naviO).find("li.nchild").width((navi_breiteN / $(naviO).find("li.nchild").length) - 1);
    } else {
        var navi_breiteN = navi_breite;
        $(naviO).find("li.nchild").width(160);
    }
    
    $(naviO).children("#nav").width(navi_breiteN);
    
    $(naviO).disableSelection().animate({
        'top': '0px'
    }, parseInt(navi_einblend_dauer), function(){
        $("#navshadow").fadeIn();
    }); 
    
    $("#main").css({
        height: ($(document).height() - $(naviO).height()) + 'px'
    });
    
    $("#sfrage").css({
        left: ($(window).width() / 2 - $("#sfrage").width() / 2) + 'px',
        top: '150px'
    });
        
    $("h1, div.kopfleiste").disableSelection(); 
    
    //////
    dyn = ($(window).width() <= 1024?980:($(window).width() >= 1484?1440:($(window).width() - 44)));
    dyn2 = ($(window).width() <= 1024?980:($(window).width() >= 1484?1300:($(window).width() - 80)));
    dyn3 = ($(window).width() <= 1024?980:($(window).width() >= 1584?1540:($(window).width() - 44)));

    // FENSTERGROESSEN ARRAY
    $("body").data({
        'n100': '720',
        'n120': '750',
        'n130': '750',
        'n140': '600',
        'n170': '710',
        'n180': '727',
        'n190': '740',
        'n198': '740',
        'n200': '964',
        'n210': '530',
        'n250': '935',
        'n260': '680',
        'n270': '980',
        'n280': '720',
        'n290': '960',
        'n300': '964',
        'n310': '530',
        'n400': dyn,
        'n421': '950',
        'n450': '960',
        'n460': '980',
        'n510': '964',
        'n515': '964',
        'n520': '980',
        'n530': '700',
        'n570': '700',
        'n534': '760',
        'n535': '700',
        'n540': '760',
        'n550': '986',
        'n590': '450',
        'n600': '400',
        'n610': '860',
        'n615': '600',
        'n620': '800',
        'n630': '600',
        'n631': dyn3,
        'n640': '900',
        'n645': '670',
        'n650': '800',
        'n660': '940',
        's110': '610',
        's115': '920',
        's120': '842',
        's310': '625',
        's410': '640',
        's420': '905',
        's450': '680',
        's480': '940',
        's490': '680',
        's500': '820',
        's510': '795',
        'extensions': '660',
        'w100': '800'
    });
    //////////////////////////    
            
    var nav_childs = $(naviO).find("#nav li ul li");  
    var navi_ul = $(naviO).find("li ul");
    
    $(navi_ul).off("mouseenter click mouseleave").css("cursor", "auto"); 
    
    $(navi_ul).on("mouseleave", function(){
        $(this).data('isopen', 'false').children("li").hide();
    });
        
    if(subnavi_click == 0){
        $(navi_ul).on("mouseenter", function(){
            $(this).data('isopen', 'true').children("li").show();
        });
    } else {    
        $(navi_ul).on("click", function(){
            $(this).data('isopen', 'true').children("li").show();
        }).css("cursor", "pointer");
    }

    $(naviO).children("#nav").find("a").off("click").on("click", function(e) {
        if($(this).attr("rel") == "extern")
            return false;
            
        if($(this).hasClass("app"))
            return false;
            
        e.stopPropagation();
        e.preventDefault();
        
        var meself = $(this); 
        if($(meself).attr("class") == "none")
            meself = $(this).parent().find("ul a");
        
        if($(meself).attr("class") != "none")
            neu(meself);
    });  
    
    last_use();
    taskleiste_titel();
    
    // Changelog zeigen
    if(show_changelog){ 
        fenster({
            id: 'changelog',
            width: 600,
            blackscreen: '',
            cb: function(nwin, ninhalt){
                $.get('changelog.php', function(data){
                    $(ninhalt).html(data);
                    
                    $(ninhalt).find("div.box_save button").off().on("click", function(e){
                        e.preventDefault();
                        $(nwin).find("p.close").trigger("click");
                    });
                });
            }
        });
        show_changelog = false;
    }
    
    
    $(naviO).find("a.app").off().on("click", function(){
        var app_id = $(this).data('id');
        var app_width = $(this).data('width');
        
        openAppWindow(app_id, app_width);
    });
}

$(document).ready(function() {
    var ua = $.browser; 
    if((ua.msie && parseInt(ua.version.slice(0,2)) < 9) || (ua.mozilla  && parseInt(ua.version.slice(0,2)) < 5)){
        $("#old-browser").show();
    } else {
        $("#old-browser").remove();
    }
	
    $(window).focus(function() { 
		tab_aktiv = true; 
	}).blur(function() { 
		tab_aktiv = false; 
	});
	
	$(document.body).bind("online", function(){
		$("#fks-offline").hide();
	});
    $(document.body).bind("offline", function(){
		$("#fks-offline").show();
	});
    
    input_lan = $("#input_language").val();
    start();
    
    $(window).resize(init);
    
    function unsavedChanges() {
        if($("table.fenster").find("input[type=text], textarea")[0])
            return "Es wurden geöffnete Fenster mit Eingabefeldern gefunden. Wenn Sie die Seite schließen, könnten ungespeicherte Änderungen verloren gehen!";
	}
	window.onbeforeunload = unsavedChanges;
    
    
    $.post('sub_info.php', {
        index: 'check_dclasses'
    }, function(data){
        if(data != 'finish'){
            fenster({
                id: 'heavy_error_1',
                width: 880,
                blackscreen: 'none',
                cb: function(nwin, ninhalt){
                    $(ninhalt).html('<h1>Schwerwiegender Fehler</h1><div class="box">Anscheinend sind Dokumentenklassen beschädigt. Dadurch können bei der weiteren Verwendung von fokus Fehler auftreten. Bitte leiten Sie diese Nachricht an einen technischen Ansprechpartner weiter.</div><div class="box fehlerbox">'+data+'</div>');
                }
            });
        }
    });
    
    $.post('sub_info.php', {
        index: 'check_teaser'
    }, function(data){
        if(data != 'finish'){
            fenster({
                id: 'heavy_error_2',
                width: 880,
                blackscreen: 'none',
                cb: function(nwin, ninhalt){
                    $(ninhalt).html('<h1>Schwerwiegender Fehler</h1><div class="box">Anscheinend sind Teaserklassen beschädigt. Dadurch können bei der weiteren Verwendung von fokus Fehler auftreten. Bitte leiten Sie diese Nachricht an einen technischen Ansprechpartner weiter.</div><div class="box fehlerbox">'+data+'</div>');
                }
            });
        }
    });
    
    
    $(window).on("load", function(){
        $(window, document).scrollTop(0);

        setTimeout(function(){
            $("body").children("div.logo").addClass("started");
        }, 300);
    });
    
    
    init_hotkeys();
});


function neu(clicked, no_ajax) { 
    var reltmp = $(clicked).children("a").attr("rel");
    if(!reltmp) reltmp = $(clicked).children("span").attr("id");
    if(!reltmp) reltmp = $(clicked).attr("rel");
    if(reltmp) rel = reltmp; 
    
    var stop = false;
    if($(clicked).find("a").attr("rel") == "extern"){ 
        stop = true;
    }
    
    if(no_ajax == undefined)
        no_ajax = false;
    
    var index = $(clicked).attr("id");
    var value = $(clicked).attr("class"); 
    lastindex = index;
    
    zindex ++;  
    
    if($('#f'+index)[0]){
        var alt = $('#f'+index);
        var task = $("#taskleiste").find('a#taskf'+index);
        
        if($(alt).css("display") == "none" && $(task)[0]){   
            oeffnen(alt, clicked, task);
            stop = true; 
        }
        else if(index == 'n100' && !$('#f'+index).hasClass("rel_"+rel)) {
            $("#fn100").remove();
        }
        else if(index == 'n200' && !$('#f'+index).hasClass("rel_"+rel)) {
            $("#fn200").remove();
        }
        else if(t_click || ($('#f'+index).hasClass("rel_"+rel))) {
            t_click = false;
            stop = true; 
            
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
        }
        else {
            stop = true; 
                
            sfrage_show('Es ist bereits eine Instanz dieses Fensters ge&ouml;ffnet. Soll diese geschlossen werden? Ungespeicherte &Auml;nderungen gehen dabei verloren!');
            $("#sfrage button:last").on("click", function(){
                $('#f'+index).find("p.close").trigger("click");
                $('#f'+index).remove();
                neu_go();
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
        neu_go();
   
   function neu_go(){ 
    
        if(neues_fenster_task == 1){
            $("table.fenster").each(function(){
                $(this).find("p.mini").trigger("click");
            });
        } else if(neues_fenster_task == 2){
            $("table.fenster").each(function(){
                $(this).find("p.close").trigger("click");
            });
        }
        
        $('#taskf'+index).remove();
        
        var n_width = $("body").data(index);
        if(!n_width) n_width = 540;
          
        var neww = $('<table id="f'+index+'" class="fenster rel_'+rel+'"></table>');
        $(neww).appendTo("#main");
        
        var n_height = $(window).scrollTop() + 40 + ($("table.fenster").length * 14);
        if(n_height < 64) n_height = 64;
        
        $(neww).html('<tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2"></td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>').width(n_width).css({
            'top': n_height + 'px',
            'left': ($("#main").width() / 2 - $(neww).width() / 2) + 'px',
            'z-index': zindex
        });
        
        var mainTD = $(neww).find("td.B2"); 
                  
        //////////// KOPFLEISTE START /////////////////
        var kopfleiste = $('<div class="kopfleiste"></div>');
        $(kopfleiste).prependTo(mainTD);
        
        var mini = $('<p class="mini">ablegen.</p>');
        $(mini).appendTo(kopfleiste).off("click").on("click", function(e){
            minimieren(neww, clicked);
        });
        
        var reload = '';
        if($.inArray(index, reload_button) != -1)
            reload = '<a class="reload"></a>';
        
        var drag = $('<p class="move">'+reload+'</p>').appendTo(kopfleiste).disableSelection().css("width", ($(kopfleiste).width() - 142) + 'px').on("dblclick", function(e){
            minimieren(neww, clicked);
        });
        
        var close = $('<p class="close">schlie&szlig;en.</p>');
        $(close).appendTo(kopfleiste).off("click").on("click", function(e){
            closed(neww, clicked);
        });
                
        var inhalt = $('<div class="inhalt"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></div>');
        $(inhalt).appendTo(mainTD);
                
        var fuss = $('<div class="fuss"></div>');
        $(fuss).appendTo(mainTD);
   
        checkBottom(neww);
        taskleiste(neww, value, rel);
  
        if(!no_ajax){   
            $.get(value+'.php', {
                index : index,
                rel : rel
            }, function(data){
                $(inhalt).html(data); 
                
                $(inhalt).find("h1").disableSelection().off("dblclick").on("dblclick", function(e){
                    minimieren(neww, clicked);
                });
        
                $.ajax({
                    url: 'js/'+value+'.js',
                    dataType: 'script'
                }); 
                
                setFocus(neww);
            });
        } else { 
            $.ajax({
                url: 'js/'+value+'.js',
                dataType: 'script'
            }); 
        }
                     
        $(neww).draggable({
            handle : 'p.move',
            drag: function(event, ui){
                var fn254 = $("#fn254");
                if(index == 'n250' && $(fn254)[0]){ 
                    $(fn254).css({
                        'top': ui.offset.top + 'px',
                        'left': ui.offset.left + $("#fn250").outerWidth(true) - 5 + 'px',
                    });
                }
            },
            stop: function(event, ui){ 
                if($(this).offset().top < 62) $(this).css('top', '62px');
                if($(this).offset().left < 0) $(this).css('left', '0px');
                checkBottom(neww);
            }
        }).mousedown(function(){
            zindex ++;
            $(this).css("z-index", zindex);
            
            if(index == 'n250' && $("#fn254")[0])
                $("#fn254").css("z-index", zindex);
        });
        /////////
        
        tooltipp();       
   }
}

var t_click = false;
function taskleiste(win, value, rel){
    var ltitel = $(win).find("h1:first").text();  
    
    var task = $('<a id="task'+$(win).attr("id")+'">'+ltitel+'</p>').hide(); 
    
    $("#taskleiste").append(task); 
    
    $(task).off().on("click", function(){
        t_click = true;
        var fid = $(win).attr("id").replace("f", ""); 
        
        var nclick = $('<a id="'+fid+'" class="'+value+'" rel="'+rel+'">'+ltitel+'</a>');
        neu(nclick);
    }).on("mouseenter", function(){
        $("#footer").css('z-index', 9000);
    }).on("mouseleave", function(){
        $("#footer").css('z-index', 1);
    });
}

var timerTT;
function taskleiste_titel(){
    clearTimeout(timerTT);
     
    $("#taskleiste a").each(function(){
        var childtl = $(this);
        
        if($(childtl).text() == ''){
            var fid = $(this).attr("id").replace("task", "");
            var ltitel = $('#'+fid).find("h1:first").text();
            $(childtl).text(ltitel);    
        }       
    });
    
    timerTT = setTimeout(function(){
        taskleiste_titel();
    }, 1500);
}

var timer_last_used, last_use_last;
function last_use(){
    clearTimeout(timer_last_used);
    
	if(tab_aktiv){
		$.get('sub_last.php', {
			index: 's130',
			time: last_use_last
		}, function(data){
			logincheck(data); 
			
			if(data){
				var slastu = $("ul#nav li.dlast");
                var slpar = $(slastu).parent("ul");
				
                if($(slpar).data('isopen') != 'true'){
    				$(slpar).find("li.last").remove();
    				$(slastu).after(data); 
    				
    				$(slpar).find("li.last a").not(".selement").off("click").on("click", function(){
    					neu($(this));
    				});
                    $(slpar).find("li.last a.selement").off("click").on("click", function(){
                        open_strukturelement($(this).data('id'));
                    });
    				
    				last_use_last = $(slpar).find("li.last:first").data("id"); 
                }     
			}
		});
	}
    
	timer_last_used = setTimeout(function(){
		last_use();
	}, (tab_aktiv?12000:30000));
}

function checkBottom(fenster){
    var ghoehe = $(document).height();
    var fhoehe = $(fenster).outerHeight(true) + $(fenster).offset().top + 150;
    
    if(ghoehe < fhoehe){
        var differenz = fhoehe - ghoehe;
        $("#main").height(ghoehe + differenz - 300);
    }
}

function closed(obj, linked, task){
    var tleiste = $("#taskleiste");
    $("div").stop(false, true); 
    
    if($("#blackscreen")[0]){
        $("#blackscreen").remove();
        $("#footer").css("z-index", "9000");
        $("#navigationO").css("z-index", "9000");
        $("body").removeClass("mit_vorschau");
    }
    
    task = $(tleiste).find('p#task'+$(obj).attr("id")); 
    
    $(obj).hide("slide", function(){  
        if($(obj).attr("id") == "fn250" && $("#fn254")[0])
            $("#fn254").remove(); 
        
        $(task).hide("drop", function(){ 
            $(this).remove();
            
            if(!$(tleiste).find("p")[0])
                $(tleiste).hide();
        });
        
        $(obj).remove(); 
    });
}

function minimieren(obj, linked){
    var tleiste = $("#taskleiste");
    $("div").stop(false, true);
    
    var klon = $(obj).clone();
    $(klon).removeAttr("id").appendTo("#main");
    $(obj).hide();
        
    var task = $(tleiste).find('a#task'+$(obj).attr("id"));
    $(task).fadeIn();
    
    if($(obj).attr("id") == "fn250" && $("#fn254")[0])
        $("#fn254").fadeOut();
    
    var pos2 = ($(task).offset()?$(task).offset().left:0); 
    
    $(klon).animate({
        'top': ($(tleiste).offset().top - 70) + 'px',
        'left': pos2 + 'px',
        'opacity': '0.5',
        'width': '80px',
        'height': '10px'
    }, 300, function(){
       $(klon).detach();
    }); 
}

function oeffnen(obj, linked, task){
    $("div").stop(false, true);
    
    zindex ++;
    $(obj).css("z-index", zindex);
    
    var klon = $(obj).clone();
    
    $(klon).appendTo("#main").css({
        'top': $("#taskleiste").offset().top + 'px',
        'left': $(task).offset().left + 'px',
        'opacity': '0.7',
        'width': '80px',
        'height': '10px',
        'display': 'block'
    });
    
    $(klon).animate({
        'top': $(window).scrollTop() + 80 + 'px',
        'left': ($("#main").width() / 2 - $(obj).width() / 2) + 'px',
        'opacity': '1',
        'width': $(obj).width() + 'px',
        'height': $(obj).height() + 'px'
    }, 300, function(){ 
        $(klon).detach();
        $(obj).css({
            'top': $(window).scrollTop() + 80 + 'px',
            'left': ($("#main").width() / 2 - $(obj).width() / 2) + 'px'
        }).show();
    
        if($(obj).attr("id") == "fn250" && $("#fn254")[0]){ // Inhalt bearbeiten
            $("#fn254").css({
                'top': $("#fn250").offset().top + 'px',
                'left': $("#fn250").offset().left + $("#fn250").outerWidth(true) - 5 + 'px'
            }).fadeIn();
        }
    
        if($(obj).attr("id") == "fn400" && $("#fn400")[0]){ // Galerie checkboxen
            $("#sort1_2, #sort2_2").attr("checked", "checked");
        }
    });
    
    $(task).hide();
}

function vordergrund(obj, linked){
   zindex ++;
   $(obj).css("z-index", zindex);
}

function show_back(obj){
   $(obj).find("p.back").show();
   $(obj).find("p.move").width(($(obj).find("div.kopfleiste").width() - 207));
}

function hide_back(obj){
   $(obj).find("p.back").hide();
   $(obj).find("p.move").width(($(obj).find("div.kopfleiste").width() - 142)); 
}
    


function sfrage_show(text){
    var sfrageO = $("#sfrage");
    
    $("body").addClass("mit_vorschau");
    $(sfrageO).after('<div id="blackscreen5"></div>').show(0);
    $(sfrageO).find("div").html(text);
    
    $(sfrageO).find("button").off().on("click", function(){
        $("#blackscreen5").remove();
        $("body").removeClass("mit_vorschau");
        $(sfrageO).hide(0);
    });
}

function tooltipp(){ 
    $(".tooltipp").addClass("lcolor").off("mouseenter mouseleave").on("mouseenter", function(e){
        var span = $(this).children("span");
        
        $(span).css({
            "left": (e.pageX + 20) + "px",
            "top": (e.pageY - $(window).scrollTop() + 22) + "px"
        }).fadeIn();
    }).on("mouseleave", function(e){
        var span = $(this).children("span");
        
        $(span).fadeOut();
    });
}

function laden(obj, show, overlay, type){
    if(!type)
        type = '';
    
    if(show){
        var add = '<div style="padding:15px 0px; float:none; width:100%; text-align:center;" class="is_loading"><img src="images/loading'+type+'.gif" alt="Wird geladen..." /></div>';
        if(overlay) $(obj).prepend('<div style="padding:15px 0px; float:none; width:100%; text-align:center;" class="is_loading"><img src="images/loading'+type+'.gif" alt="Wird geladen..." /></div>');
        else $(obj).html('<div style="padding:15px 0px; float:none; width:100%; text-align:center;" class="is_loading"><img src="images/loading'+type+'.gif" alt="Wird geladen..." /></div>');  
    } else { 
        $(obj).find("div.is_loading").remove();
    }
}


if (typeof console === "undefined" || typeof console.log === "undefined") {
    console = {};
    console.log = function() {};
}



///////////// EDITOR
var ckconfig = {
    toolbar_Full: [
        ['Bold', 'Italic', '-', 'RemoveFormat'],  
        ['Link', 'Unlink'], 
        ['Undo', 'Redo'], 
        ['Source'],
        ['Maximize']
    ],
    enterMode: CKEDITOR.ENTER_BR,
    shiftEnterMode: CKEDITOR.ENTER_BR,
    forceEnterMode: true,
    forcePasteAsPlainText: true,
    extraPlugins : 'resize',
    removePlugins : 'autogrow',
    startupFocus: true,
    language: 'de',
    contentsLanguage: 'de',
    defaultLanguage: 'de',
    resize_dir: 'vertical',
    fillEmptyBlocks: false
};

   
function load_fck_button(f){
    var editor = $('span.'+f.id)[0]; 
    
    f.removeListener('doubleclick');
    f.on('doubleclick', function(evt){ 
		var element = CKEDITOR.plugins.link.getSelectedLink( editor ) || evt.data.element;

		if(!element.isReadOnly()){
			if(element.is('a')){
                fck_link(f, editor);
            }
		}
        
		evt.stopPropagation();
	});
    
    f.addCommand('link', {
        exec : function(editor2) { 
            fck_link(f, editor);
        }
    });
}

///////////////////////