function newsletter_laden(){
    var nl = $("#fn610 div.inhalt");
    var vnl = $(nl).find("#v_newsletter");
    
    $(vnl).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
    
    $.post('inc_communication.php', {
        index: 'n611'
    }, function(data){ logincheck(data); 
        $(vnl).html(data);
    
        $(nl).find("a.inc_communication").off("click").on("click", function(e){
            e.preventDefault();
            neu($(this));
        });
        
        $(nl).find("p.del a").off("click").on("click", function(e){
            e.preventDefault();
            var dell = $(this).attr("rel");
            var eld = $(this).parents("div.spalte");
            
            sfrage_show('Wollen Sie diesen Newsletter wirklich entfernen?');
            $("#sfrage button:last").on("click", function(){
                $(eld).remove();
                
                $.get('inc_communication.php', {
                    index: 'n612',
                    rel: dell
                }, function(data){ 
                    logincheck(data); 
                }); 
            });
        });
    });
}

if($("#fn610")[0] && lastindex == 'n610'){ // Newsletter Übersicht
    newsletter_laden();
}


if($("#fn615")[0] && lastindex == 'n615'){ // Newsletter bearbeiten
    var inhalt = $("#fn615 div.inhalt");
    var sb = $(inhalt).find("div.box_save"); 
    var doc_container = $(inhalt).find("#Kstruk_doks");
    
    function doks(){
        var ins_docs = $(doc_container).children("article");
            
        function calcHeight(){      
            var max_height = 0;
            $(ins_docs).each(function(){                
                max_height = 0;
                $(this).find("div.spalte").each(function(){
                    max_height = ($(this).height() > max_height?$(this).height():max_height);
                });
                if(max_height)
                    $(this).find("div.spalte").height(max_height);
                    
                var ch = $(this).children("div.c").height();
                $(this).children("div.drag").height(ch); 
            }); 
        }
        calcHeight();
        
        var vorgeladen = 0;
        $(ins_docs).find("img").on("load", function(){ 
            vorgeladen ++; 
            
            if(vorgeladen == $(ins_docs).find("img").length){
                calcHeight();
            }
        });
        
        $(doc_container).sortable({
            items: 'article',
            axis: 'y',
            handle: 'div.drag',
            containment: inhalt,
            stop: function(){
                $(sb).show();
                calcHeight();
            }
        });
        
        $(doc_container).find("a.titel").off().on("click", function(){
            if(!$(this).hasClass("duebersicht"))
                var clickdumme = $('<a class="inc_documents" id="n250" rel="'+$(this).data('id')+'"></a>');
            else
                var clickdumme = $('<a class="inc_documents" id="n290" rel="'+$(this).data('id')+'"></a>');
                
            neu(clickdumme);
        });
                            
        $(doc_container).find("a.del").off().on("click", function(){
            var delid = $(this).data('id');
            
            sfrage_show('Möchten Sie die Zuordnung zwischen diesem Dokument und dem Newsletter wirklich entfernen?');
            $("#sfrage button:last").on("click", function(){
                if($(sb).css("display") == "none") 
                    $(sb).show();
                
                $.post('inc_communication.php', {
                    index: 'n616',
                    del: delid,
                    rel: $(inhalt).find("input[name=kid]").val()
                }, function(data){ logincheck(data); 
                    $(doc_container).html(data);
                    doks();
                });
            });
        });
        
        var doc_offen = false;
        $(doc_container).find(".options").off('click').on('click', function(event) {
            if(!doc_offen){ 
                $(this).children(".add").stop(true, true).show("blind", 150);
                doc_offen = true;
            }
        }).off('mouseleave').on('mouseleave', function(event) {
            if(doc_offen){
                $(this).children(".add").stop(true, true).hide("blind", 150);
                doc_offen = false;
            }
        }); 
    
        // DOKUMENT SUCH FENSTER 
        $(doc_container).find("button").off().on("click", function(e){ 
            e.stopPropagation();
            e.preventDefault();
            var buttonclass = $(this).attr("class");
            
            fenster({
                id: 'n616',
                blackscreen: '',
                width: 964,
                cb: function(neww, inhalt){
                
                    $.post('inc_documents.php', {
                        index: 'n200',
                        rel: 0
                    }, function(data){ logincheck(data);
                    
                        $(inhalt).html(data);
                        setFocus(neww);
                        
                        /// EXAKT WIE BEI DEN DOKUMENTEN O'REALLY            
                        function dokumente_start() {
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
                                        var selected = $(this).attr("rel");
                                        
                                        $("#fn616 p.close").trigger("click");
                                        $(doc_container).siblings("div.loadme").show();
                                        
                                        if($(sb).css("display") == "none") 
                                            $(sb).show("blind", 500);
                                        
                                        $.post('inc_communication.php', {
                                            index: 'n616',
                                            newe: selected,
                                            rel: $("#fn615 input[name=kid]").val()
                                        }, function(data){ logincheck(data); 
                                            $(doc_container).html(data).siblings("div.loadme").hide();
                                            doks();
                                            calcHeight();
                                        });
                                    });
                                    
                                    $(table).find("#headline td").off("click").on("click", function(){ 
                                        var sort = $(this).attr("id").replace('ddd_', ''); 
                                        if(sort == d_sortA) d_sortC ++;
                                        else d_sortC = 0;
                                        d_sortB = (d_sortC % 2 == 0?"ASC":"DESC");
                                        d_sortA = sort; 
                                        doc_verwalten_inhalt();
                                    });
                                    
                                    dok_uebersicht_4(fn200, table, doc_verwalten_inhalt);
                                }); 
                            }
                            doc_verwalten_inhalt();
                            
                            dok_uebersicht_1(fn200, doc_verwalten_inhalt);
                            
                            $(fn200).find("button.inc_documents").remove();
                        }
                        dokumente_start();
                    });
                }
            });
        });
    }
    
    $.post('inc_communication.php', {
        index: 'n616',
        rel: $(inhalt).find("input[name=kid]").val()
    }, function(data){ logincheck(data);
        $(doc_container).html(data).siblings("div.loadme").hide();
        doks();
    });
    
    
    $(inhalt).find("input, select").on("keyup change", function(){
        if($(sb).css("display") == "none") 
            $(sb).show("blind", 500);
    });
    
    $(sb).find("button:first").off().on("click", function(e){
        e.preventDefault();
        $("#fn615 p.close").trigger("click");
    });
    
    $(sb).find("button:last").off().on("click", function(e){ 
        e.preventDefault();
        
        $.post('inc_communication.php', {
            index: 'n617',
            rel: $(inhalt).find("input[name=kid]").val(),
            titel: $(inhalt).find("input[name=titel]").val(),
            template: $(inhalt).find("select[name=template]").val(),
            sort: $(doc_container).sortable("serialize")
        }, function(data){ logincheck(data); 
            newsletter_laden();
            
            if($(inhalt).find("input[name=kid]").val() != 0){
                $("#fn615 p.close").trigger("click");
            } else {
                $(inhalt).find("input[name=kid]").val(data);
                $(inhalt).find("div.v_struk").fadeIn();
            }
        });
    });
    
    var bigv = $("#big_vorschau");
    var mainO = $("#main, body");
    var qvorschau = $(inhalt).find("div.nlpreview a");
    $(qvorschau).off("click").on("click", function(){
        $(mainO).addClass("mit_vorschau");
        $(bigv).show().html('<br /><br /><br /><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
        
        $.post('inc_documents.php', {
            index : 'n200_quick_preview',
            td: $(inhalt).find("select[name=template]").val(),
            newsletter: $(doc_container).sortable("serialize")
        }, function(data){
            $(bigv).html(data);
            $(bigv).find("select").remove();
            
            var close = $(bigv).find("a.close");
            var iframe = $(bigv).find("iframe");
            
            $(close, iframe).off("click").on("click", function(){
                $(mainO).removeClass("mit_vorschau");
                $(bigv).hide().html('');
            }); 
            
            $(iframe).height($(window).height() - 33);
        });
    });
}


if($("#fn620")[0] && lastindex == 'n620'){ // Newsletter versenden
    var inhalt = $("#fn620 div.inhalt");
    var send_status = $(inhalt).find("#send_status");
    
    $(inhalt).find("input#is_cc").off("click").on("click", function(){
        var ptr = $(this).parent("td").parent("tr");
        var pele = $(ptr).find("td.gocopy input");
        
        if($(this).is(":checked")){
            $(ptr).removeClass("deakt");
            $(pele).removeAttr("disabled");
        } else {
            $(ptr).addClass("deakt");
            $(pele).attr("disabled", "disabled");
        }
    });
    
    var bigv = $("#big_vorschau");
    var mainO = $("#main, body");
    var qvorschau = $(inhalt).find("div.nlpreview a");
    
    $(qvorschau).off("click").on("click", function(){
        $(mainO).addClass("mit_vorschau");
        $(bigv).show().html('<br /><br /><br /><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
        
        $.post('inc_documents.php', {
            index : 'n200_quick_preview',
            td: $(inhalt).find("input[name=template]").val(),
            newsletter2: $(inhalt).find("input[name=doks]").val()
        }, function(data){
            $(bigv).html(data);
            $(bigv).find("select").remove();
            
            var close = $(bigv).find("a.close");
            var iframe = $(bigv).find("iframe");
            
            $(close, iframe).off("click").on("click", function(){
                $(mainO).removeClass("mit_vorschau");
                $(bigv).hide().html('');
            }); 
            
            $(iframe).height($(window).height() - 37);
        });
    });
     
    var progresssbar = $(send_status).find("#progresssbar"); 
    $(progresssbar).progressbar({
        value: 0
    });
    
    var to_empf_count = $(inhalt).find("span.anz_empf");
    $(inhalt).find("textarea").off("change blur").on("change blur", function(){
        var empfT = $(this).val();
        
        $.post('inc_communication.php', {
            index : 'n622',
            empf: empfT
        }, function(data){
            $(to_empf_count).html(data);
        }); 
    });
    
    
    $(inhalt).find("div.sendnl div.a button").off("click").on("click", function(e){
        e.preventDefault();
        var button = $(this);
        
        $(button).html('wird verschickt...').attr("disabled", "disabled");
                
        $.post('inc_communication.php', {
            index : 'n621',
            f: $(inhalt).find("form").serialize(),
            newsletter: $(inhalt).find("input[name=doks]").val(),
            count: 0,
            test: 'test'
        }, function(data){
            $(button).html('Test-Email senden').removeAttr("disabled");
        });
    });
    
    $(inhalt).find("div.sendnl div.b button").off("click").on("click", function(e){
        e.preventDefault();
        
        var count = 0;
        var serialized = $(inhalt).find("form").serialize(); 
        var doks = $(inhalt).find("input[name=doks]").val();
        var ges_empf = $(send_status).find("span.ges_empf");
        var alle_empf = parseInt($(send_status).find("span.anz_empf").html());
        
        $(inhalt).find("div.sendnl").slideUp().remove();
        $(send_status).slideDown();
        $(inhalt).find("input, textarea").attr("disabled", "disabled");
        $(inhalt).find("a.add_empf").remove();
        
        function send_mail(){        
            $.post('inc_communication.php', {
                index : 'n621',
                f: serialized,
                newsletter: doks,
                count: count
            }, function(data){  
                if(data != 'ende' && data > count){
                    count = parseInt(data);
                    
                    var fortschritt = (count / alle_empf * 100);
                    $(ges_empf).html(count);
                    $(progresssbar).progressbar("value", fortschritt); 
                    
                    setTimeout(function(){
                        send_mail();
                    }, 1000);
                } else {
                    $(send_status).find("h2").html('Emails wurden erfolgreich verschickt');
                }
            });
        }
        send_mail();
    });
    
    
    $(inhalt).find("a.add_empf").off("click").on("click", function(e){
        clicked = $(this);
        
        fenster({
            id: 'n623',
            blackscreen: '',
            width: 750,
            cb: function(neww, inhaltW){
        
                $.post('inc_communication.php', {
                    index: 'n623',
                    cempf: $(inhalt).find("textarea").val()
                }, function(data){ 
                    logincheck(data);
                    
                    $(inhaltW).html(data);
                    setFocus(neww);
                    
                    var thetable = $(inhaltW).find("form.ergebnis table");
                    var nform = $(inhaltW).find("form.auswahl");
                    var sb = $(inhaltW).find("div.box_save"); 
                    
                    var alle = $(inhaltW).find("button.alle"); 
                    var none = $(inhaltW).find("button.none"); 
                    
                    var cempf = parseInt($(inhalt).find("span.anz_empf").text());
                                
                    $(sb).find("button:first").off("click").on("click", function(e){
                        e.preventDefault();
                        $(neww).find("p.close").trigger("click");
                    });
                    
                    $(sb).find("button:last").off("click").on("click", function(e){
                        e.preventDefault();
                        
                        var insert = '';
                        $(thetable).find("input:checked").each(function(index){
                            insert += (!index && cempf < 1?'':', ')+$(this).val();
                        });
                        
                        var new_text = $(inhalt).find("textarea").val() + insert;
                        $(inhalt).find("textarea").val(new_text);
                        
                        $.post('inc_communication.php', {
                            index : 'n622',
                            empf: new_text
                        }, function(data){
                            $(to_empf_count).html(data);
                        }); 
                        
                        $(neww).find("p.close").trigger("click");
                    });
                    
                    $(alle).off("click").on("click", function(e){
                        e.preventDefault();
                        $(thetable).find("input.yes").attr("checked", "checked");
                        
                        if($(none).css("display") == "none") 
                            $(none).fadeIn();
                        if($(sb).css("display") == "none") 
                            $(sb).show("blind", 500);
                    });
                    
                    $(none).off("click").on("click", function(e){
                        e.preventDefault();
                        $(thetable).find("input.yes").removeAttr("checked");
                    });
                                
                    function aktualisieren(){
                        $(thetable).html('<tr><td><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>');
                        $(none).stop(true).fadeOut();
                        
                        $.post('inc_communication.php', {
                            index: 'n624',
                            f: $(nform).serialize()
                        }, function(data){
                            $(thetable).html(data);
                            
                            $(thetable).find("input").off("click").on("click", function(){
                                if($(sb).css("display") == "none") 
                                    $(sb).show("blind", 500);
                                if($(none).css("display") == "none") 
                                    $(none).fadeIn();
                            });
                        });
                    }
                    aktualisieren();
                    
                    $(nform).find("div.a input").off("click").on("click", function(){
                        aktualisieren();
                    });
                    
                    $(nform).find("div.b input").off("keyup").on("keyup", function(){
                        aktualisieren();
                    });
                    
                    $(nform).find("#fstati_aus").off("click").on("click", function(){
                        aktualisieren();
                    });
                });
            }
        });
    });    
}



if($("#fn630")[0] && lastindex == 'n630'){
    var kanaele = $("#fn630 #kkanaele");
    
    var kbut = $(kanaele).find("a.rbutton");
    var kopen = $(kanaele).find("table.forms");
    rbutton(kbut, kopen, 'öffnen', 'schließen');
    
    $(kanaele).find("a.goaway").off("click").on("click", function(){
        var metask = $(this).data('task');
        
        if(metask == 'kommentare')
            var clickdummi = $('<a id="n631" class="inc_communication" rel="comments"></a>');
        else if(metask == 'suche')
            var clickdummi = $('<a id="n631" class="inc_communication" rel="suche"></a>');
            
        if(clickdummi)
            neu($(clickdummi));
    });
    
    $(kanaele).find("a.inc_communication").off().on("click", function(){
        neu($(this));
    });
    
    $(kanaele).find("tr.empty a").off().on("click", function(){
        $(this).parents("tr.empty").remove();
        $(kanaele).find("tr.is_empty td").show();
    });
}



function kanal_start(){
    var kk_search = '';
    var kk_opt = '';
    var kk_sortA = '';
    var kk_sortB = '';  
    
    if($("#fn631")[0]){
        var dinhalt = $("#fn631").find("div.inhalt");
        
        var type = $(dinhalt).find("input[name=type]").val();
        var vid = $(dinhalt).find("input[name=vid]").val();
        
        function kanal_verwalten_inhalt(){
            kk_opt = '';
            $(dinhalt).find("div.opt input").each(function(){
                if($(this).is(":checked"))
                    kk_opt += $(this).val()+'+';
            });
            
            $.get('inc_communication.php', {
                index: 'n632',
                q: kk_search,
                opt: kk_opt,
                sortA: kk_sortA,
                sortB: kk_sortB,
                vid: vid,
                type: type
            }, function(data){ logincheck(data); 
                var table = $(dinhalt).find("table#form_auflistung");
                $(table).html(data); 
                
                $(table).find("#headline td").off("click").on("click", function(){
                    if($(this).attr("id") == undefined)
                        return false;

                    var sort = $(this).attr("id").replace('kk_', ''); 
                    if(sort == kk_sortA) kk_sortC ++;
                    else kk_sortC = 0;
                    kk_sortB = (kk_sortC % 2 == 0?"ASC":"DESC");
                    kk_sortA = sort; 
                    
                    kanal_verwalten_inhalt();
                });


                // mass options for comments
                function getCheckedItems(){
                    var ids = new Array();
                    $(table).find("input[name=multi]").filter(":checked").each(function(){
                        ids.push($(this).val());
                    });
                    return ids;
                }

                var multiopt = $(dinhalt).find("div.multiopt");
                $(table).find("input[name=multi]").off().on("click", function(){
                    if($(table).find("input[name=multi]").filter(":checked")[0])
                        $(multiopt).slideDown();
                    else
                        $(multiopt).slideUp();
                });

                $(multiopt).find("a.del").off().on("click", function(){
                    sfrage_show('Wollen Sie diese Kommentare wirklich unwiederruflich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $(multiopt).slideUp();

                        $.get('inc_communication.php', {
                            index: 'n635',
                            ids: getCheckedItems()
                        }, function(data){
                            kanal_verwalten_inhalt();
                        });
                    });
                });

                $(multiopt).find("a.close").off().on("click", function(){
                    $(multiopt).slideUp();

                    $.get('inc_communication.php', {
                        index: 'n634',
                        action: 'close',
                        ids: getCheckedItems()
                    }, function(data){
                        kanal_verwalten_inhalt();
                    });
                });

                $(multiopt).find("a.open").off().on("click", function(){
                    $(multiopt).slideUp();

                    $.get('inc_communication.php', {
                        index: 'n634',
                        action: 'open',
                        ids: getCheckedItems()
                    }, function(data){
                        kanal_verwalten_inhalt();
                    });
                });
                //


                $(table).find("a.cfreisperr").off("click").on("click", function(){
                    var re = $(this).attr("rel");
                    $(this).fadeOut().remove();
                    
                    $.get('inc_communication.php', {
                        index: 'n634',
                        id: re
                    }, function(data){
                        kanal_verwalten_inhalt();
                    });
                });
                
                $(table).find("a.cdel").off("click").on("click", function(){
                    var re = $(this).attr("rel");
                    var me = $(this);
                    
                    sfrage_show('Wollen Sie diesen Kommentar wirklich unwiederruflich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $(me).fadeOut().remove();
                        
                        $.get('inc_communication.php', {
                            index: 'n635',
                            id: re
                        }, function(data){
                            kanal_verwalten_inhalt();
                        });
                    });
                });
                
                $(table).find("a.delete").off("click").on("click", function(){
                    var re = $(this).data("id");
                    var me = $(this);
                    
                    sfrage_show('Wollen Sie diesen Datensatz wirklich unwiederruflich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $(me).fadeOut().remove();
                        
                        $.get('inc_communication.php', {
                            index: 'n635',
                            id: re,
                            type: 'ds'
                        }, function(data){
                            kanal_verwalten_inhalt();
                        });
                    });
                });
            }); 
        }
        kanal_verwalten_inhalt();
        
        $(dinhalt).find("#form_search").val(kk_search).off("keyup change").on("keyup change", function(){
            kk_search = $(this).val(); 
            kanal_verwalten_inhalt();
        });
        
        $(dinhalt).find(".opt input").off("click").on("click", function(){
            kanal_verwalten_inhalt();
        });
        
        $(dinhalt).find("a.more_opt").off("click").on("click", function(){
            var so = $(this).siblings(".opt");
            
            if($(so).css("display") == "none") {
                $(so).slideDown();
                $(this).html("M&ouml;gliche Felder ausblenden").css('background', '#fff url(images/rpfeil_oben.png) no-repeat 2px center');
            } else {
                $(so).slideUp();
                $(this).html("M&ouml;gliche Felder einblenden").css('background', '#fff url(images/rpfeil_unten.png) no-repeat 2px center');
            }
        });
    }
}

if($("#fn631")[0] && lastindex == 'n631') {
    kanal_start();
}


var pn = null;
var pnL = null;
var pnR = null;

function pn_start() {
    pn = $("#fn640 #pn");
    pnL = $(pn).children("#pnL");
    pnR = $(pn).children("#pnR");
    
    pn_neu_reload();
    pn_content_reload();
    pn_anzahl_reload();
    
    $(pnL).find("input#suche_empf").off("keyup").on("keyup", function(){
        pn_empf_reload();
    });
    
    $("#fn640 p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        pn_neu_reload();
        pn_content_reload();
        pn_anzahl_reload();
    });
}

function pn_content_reload() {
    $.get('inc_communication.php', {
        index: 'n641',
        limit: pn_limit,
        b: pn_aktiv
    }, function(data){ logincheck(data);
        $(pnR).html(data);
        
        $(pnR).find("#new_msg").off().on("click", function(){
            var neubutton = $('<a id="n645" class="inc_communication" rel="'+$(this).attr("rel")+'"></a>');
            neu(neubutton);
        });
        
        $(pnR).find("a.titel, a.show_msg").off().on("click", function(){
            var par = $(this).parents("div.pnC");
            var pnA = $(par).siblings("div.pnA");
            var vorschau = $(par).children("div.vorschau");
            var msg = $(this).attr("rel");
            
            if($(par).children("p.text")[0]){
                $(par).children("p.text").remove();
                $(vorschau).fadeIn();
                $(par).find("a.show_msg").html('Nachricht anzeigen');
            } else {
                var text = $('<p class="text"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></p>');
                $(vorschau).hide().after(text);
                
                $(par).find("a.show_msg").html('Nachricht schlie&szlig;en');
                $(par).find("a.gelesen, span.s_gelesen").remove();
                $(par).find("a.unread").removeClass("unread");
                $(pnA).children("img").attr("src", "images/mail.png");
                pn_neu_reload();
                
                $.get('inc_communication.php', {
                    index: 'n642',
                    msg: msg
                }, function(data){ logincheck(data); 
                    $(text).html(data);
                });
            }
        });   
        
        $(pnR).find("a.gelesen").off().on("click", function(){
            var par = $(this).parents("div.pnC");
            var pnA = $(par).siblings("div.pnA");
            var msg = $(this).attr("rel");
            
            $(par).find("a.gelesen, span.s_gelesen").remove();
            $(par).find("a.unread").removeClass("unread");
            $(pnA).children("img").attr("src", "images/mail.png");
            pn_neu_reload();
            
            $.get('inc_communication.php', {
                index: 'n642',
                msg: msg
            });
        }); 
        
        $(pnR).find("a.answer").off().on("click", function(){
            var msg = $(this).attr("rel");
            
            var neubutton = $('<a id="n645" class="inc_communication" rel="answer|'+msg+'"></a>');
            neu(neubutton);
        });    
    });
}

function pn_neu_reload() {
    var pnn = $(pnL).find("#neue_pn");
    
    $.get('inc_communication.php', {
        index: 'n643',
        aktiv: pn_aktiv
    }, function(data){ logincheck(data);
        $(pnn).html(data);
        
        $(pnn).find("a").off("click").on("click", function(){
            $(pnR).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
    
            pn_aktiv = $(this).attr("rel");
            
            pn_limit = 10;
            pn_content_reload();
            pn_anzahl_reload();
            
            $(pnn).find("a.aktiv").removeClass("aktiv");
            $(this).addClass("aktiv");
        });
    });
    
    clearTimeout(pn_timer);
    pn_timer = setTimeout(function(){
        if($(pn)[0])
            pn_neu_reload();
    }, 15000);
}

function pn_empf_reload() { 
    var empf = $(pnL).find("#suche_empf").val();
    var le = $(pnL).find("#last_empf");
    var checkT = $(le).find("input");
    
    var achecked = '';
    $(checkT).each(function(){
        if($(this).is(":checked")){
            achecked = achecked + $(this).val()+"_"; 
        }
    }); 
    
    $.get('inc_communication.php', {
        index: 'n644',
        empf: empf,
        checked: achecked
    }, function(data){ logincheck(data);
        $(le).html(data);
        
        var check = $(le).find("input");
        var bp = $(le).find("button");
        
        if($(check).filter(":checked")[0])
            $(bp).fadeIn();
        
        $(check).off("click").on("click", function(){
            if($(check).is(":checked"))
                $(bp).fadeIn();
        });
        
        $(bp).off("click").on("click", function(e){
            e.preventDefault();
            
            var neubutton = $('<a id="n645" class="inc_communication" rel="'+$(pnL).find("#last_empf_form").serialize()+'"></a>');
            neu(neubutton);
        });
    });
}

function pn_anzahl_reload() {
    var mehr = $("#fn640 #pn_mehr");
    var plus = 10;
    
    $.get('inc_communication.php', {
        index: 'n647',
        limit: pn_limit,
        b: pn_aktiv
    }, function(data){ logincheck(data);
        $(mehr).html(data);
        
        $(mehr).children("a").off().on("click", function(){
            pn_limit += plus;
            
            pn_start();
            pn_anzahl_reload();
        });
    });
}

if($("#fn640")[0] && lastindex == 'n640'){
    pn_aktiv = 0;
    pn_limit = 10;
    pn_timer = null;
    
    pn_start();
    pn_empf_reload();
}


function pn_neu() {
    var fn645 = $("#fn645");
    var neupn = $(fn645).find("#neu_pn");
    var pntitel = $(neupn).find("#msg_titel");
    var pnta = $(neupn).find("#msg_text");
    var pntempfI = $(neupn).find("#msg_empf");
    var pnempf = $(neupn).find("div.msg_empf");
    var pnclose = $(fn645).find("p.close");
    var sb = $(fn645).find("div.box_save"); 
    
    var my_pn_ckeditor = null;
    
    $(pnta).ckeditor(function() { 
        my_pn_ckeditor = $(pnta).ckeditorGet();
                                    
        load_fck_button(this); 
    }, ckconfig);
    
    $(pnclose).off("click").on("click", function(e){
        if(my_pn_ckeditor != null)
            my_pn_ckeditor.destroy(true);
            
        $(fn645).hide("slide", {}, 300, function(){  
            $(this).remove();
        });
    });
    
    $(pnempf).find("a.inc_users").off().on("click", function(){
        neu($(this));
    });
    
    $(pntitel).focus();
                            
    $(sb).find("input:first").off().on("click", function(){
        $(pnclose).trigger("click");
    });
    
    $(sb).find("input:last").off().on("click", function(){
        $(this).replaceWith('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
        
        $.post('inc_communication.php', {
            index: 'n646',
            titel: $(pntitel).val(),
            text: $(pnta).val(),
            empf: $(pntempfI).val()
        }, function(data){ logincheck(data); 
            pn_start();
            $(pnclose).trigger("click");
        });
    });
}

if($("#fn645")[0] && lastindex == 'n645'){
    pn_neu();
}


var livetalk_timer;
var livetalk_limit = 8;

function livetalk_start(){
    var olivetalk = $("#fn650 #livetalk");
    var newlt = $("#fn650 #new_livetalk");
    var lload = $(newlt).find("img.ladebalken");
    
    function livetalk_update(get_last){  
        clearTimeout(livetalk_timer);
        
        if($("#fn650")[0]){
            if(!$(olivetalk).find(".talk:last")[0] || get_last == true)
                var last = 0;
            else
                var last = split_id($(olivetalk).find(".talk:last").attr("id"));
                
            $(lload).show();
            
            $.get('inc_communication.php', {
                index: 'n651',
                last: last,
                limit: livetalk_limit
            }, function(data){ logincheck(data);
                $(lload).hide();
                
                if(data){
                    if(get_last == true)
                        $(olivetalk).find("div.talk").remove();
                    
                    $(olivetalk).append(data);
                    $(olivetalk).find("div.talk:first").prevAll("div.sperre").remove();
        
                    $(olivetalk).find("div.talk div.LL a").off().on("click", function(e){
                        neu($(this));
                    });
                    
                    var anzahl = $(olivetalk).find("div.talk").length; 
                    if(anzahl > livetalk_limit){
                        for(var x=0; x<(anzahl-livetalk_limit); x++)
                            $(olivetalk).find("div.talk").eq(x).remove();
                    }
                        
                    $(olivetalk).find("div.talk .LL").css("visibility", "visible");
                    
                    var lastuser = 0;
                    var lasttime = 0;
                    $(olivetalk).find("div.talk").each(function(){
                        var thisuser = $(this).children(".userhidden").val();
                        var thistime = $(this).children(".tstamp").val();
                        
                        if(lastuser != thisuser || lasttime < thistime - 300){
                            lastuser = thisuser;
                            lasttime = thistime;
                        } else { 
                            $(this).find("div.LL").css("visibility", "hidden");
                            $(this).prev("div.sperre").remove();
                        }
                        
                        var lid = $(this).attr("id");
                        $('#'+lid+', #'+lid).eq(1).remove();
                    }); 
                }
                
                livetalk_timer = setTimeout(function(){
                    livetalk_update();
                }, 4000);
            });
        }
    }
    livetalk_update();
    
    $("#fn650 p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        livetalk_update();
    }); 
    
    $(olivetalk).find(".alt a").off().on("click", function(e){
        e.preventDefault();
        
        livetalk_limit += 8;
        livetalk_update(true);
    }); 
        
    function send_lifetalk(){
        $.post('inc_communication.php', {
            index: 'n652',
            text: $(newlt).find("textarea").val()
        }, function(data){ 
            livetalk_update();
        });
        
        $(newlt).find("textarea").val('');
        $(newlt).find("button").hide();
    }
    
    $(newlt).find("button").hide().off().on("click", function(e){
        e.preventDefault();
        send_lifetalk();
    });
    
    $(newlt).find("textarea").off("keyup change").on("keyup change", function(e){
        if($(this).val() != '')
            $(newlt).find("button").fadeIn();
        else
            $(newlt).find("button").hide();
            
    });
    $(newlt).find("textarea").on("keyup", function(e){
        if(e.keyCode == 13 && $(this).val() != '') {
            e.preventDefault();
            send_lifetalk();
        }
    });
}

if($("#fn650")[0] && lastindex == 'n650') {
    livetalk_start();
}




if($("#fn660")[0] && lastindex == 'n660') { 
    var pinnwand = $("#fn660 #pinnwand");
    var edita = $(pinnwand).find("textarea");  
    var pinnwand_ckeditor = null;
    
    var newheight = ($(window).height() - 400);
    if(newheight < 200) newheight = 200;
    
    var nckconfig = jQuery.extend(true, {}, ckconfig);
    nckconfig.enterMode = CKEDITOR.ENTER_P;
    nckconfig.extraPlugins = 'resize,scayt';
    nckconfig.removePlugins = 'autogrow';
    nckconfig.scayt_sLang = 'de_DE';
    nckconfig.height =  newheight + 'px';
    nckconfig.toolbar_Full = [
        ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'],
        ['NumberedList', 'BulletedList'], 
        ['Undo', 'Redo'], 
        ['Scayt'], 
        ['Source'],
        ['Maximize']
    ];
    
    var sb = $(pinnwand).find("div.box_save").show();
    var saveb = $(sb).find("input.bs2");
    var stimer = null;
    
    $(sb).find("input.bs1").off().on("click", function(){
        if(pinnwand_ckeditor != null)
            pinnwand_ckeditor.destroy();
        $("#fn660 p.close").trigger("click");
    });
    
    $(saveb).off().on("click", function(){
        clearTimeout(stimer);
        
        $(saveb).attr("disabled", "disabled");
        
        $.post('inc_communication.php', {
            index: 'n661',
            t: $(edita).val()
        }, function(data){
            logincheck(data);
            
            $(saveb).val('gespeichert');
            
            if(data != ''){
                $(saveb).val('speichern').removeAttr("disabled");
                alert("Pinnwand-Eintrag konnte nicht gespeichert werden: "+data+" arbeitet ebenfalls gerade an der Pinnwand.");
            }
        });
    });
    
    function activate_button(){
        clearTimeout(stimer);
        $(saveb).val('speichern').removeAttr("disabled");  
        
        stimer = setTimeout(function(){
            $(saveb).trigger("click");
        }, 3000);
    }
    
    $(edita).ckeditor(function() { 
        pinnwand_ckeditor = $(edita).ckeditorGet(); 
        
        pinnwand_ckeditor.on('change', activate_button);
        pinnwand_ckeditor.on('key', activate_button);
        pinnwand_ckeditor.on('setData', activate_button);
        pinnwand_ckeditor.on('insertHtml', activate_button);
        pinnwand_ckeditor.getCommand('undo').on('afterUndo', activate_button);
        pinnwand_ckeditor.getCommand('redo').on('afterRedo', activate_button);
    }, nckconfig); 
    
}