function init_strukturelement(fen){
    var se = $(fen).find("#strukturelement");
    if(!$(se)[0])
        return false;
        
    var sprachen = $(se).find("div.sprache");
    var sd = $(se).find("#struk_doks");
    var sid = $(se).find("input[name=sid]").val();
    var klasse = $(se).find("input[name=klasse]").val();
    var sb = $(se).find("div.box_save");
    
    $(se).find("input, textarea, select").off();
    
    if(klasse != "true"){
        strukturelement_sprachoptionen(se);
    } else {
        $(se).find("div.sprachbox").remove();
    }
    
    strukturelement_strukdok(fen, sd, sid);
    
    // Element direkt freigeben
    $(se).find("a.freigabe").off().on("click", function(e){
        $(se).find("div.fehlerbox").slideUp();
        
        $.post('inc_structure.php', {
            index: 'n140release',
            sid: sid
        }, function(data){
            logincheck(data);
            
            struktur_start({
                open: sid
            });
        });
    });
    
    // Speichern Felder einblenden
    $(se).find("input, textarea").on("keyup change", function(){
        $(sb).fadeIn();
    });
    $(se).find("select").on("change", function(){
        $(sb).fadeIn();
    });
    
    // Strukturelement speichern
    $(fen).find("input.bs2").off().on("click", function(e){
        e.preventDefault();
        var mebut = $(this);
        $(mebut).attr("disabled", "disabled");
        
        $.post('inc_structure.php', {
            index: 'n145',
            sid: sid,
            f: $(se).serialize()
        }, function(data){
            logincheck(data);
            
            if($(mebut).data('close') == true)
                $(fen).find("p.close").trigger("click");
                
            $(mebut).removeAttr("disabled");
            
            struktur_start({
                open: sid
            });
        });
    });
    
    // Strukturelement Einstellungen oeffnen
    $(se).find("#se_open_duebersicht").off().on("click", function(){
        var clickdumme = $('<a class="inc_documents" id="n290" rel="'+$(this).data('id')+'"></a>');
        neu(clickdumme);
    });
    
    // Strukturelement Einstellungen oeffnen
    $(se).find("#se_open_seopt").off().on("click", function(){
        fenster({
            id: 'n150',
            blackscreen: '',
            width: 600,
            cb: function(twin, tinhalt){
                $.post('inc_structure.php', {
                    index: 'n150',
                    sid: sid
                }, function(data){
                    logincheck(data);
                    
                    $(tinhalt).html(data); 
                    
                    var sab = $(tinhalt).find("div.box_save");
                    save_button($(twin));  
                    
                    $(tinhalt).find("#is_in_navi").off().on("click", function(){
                        var naviO = $(tinhalt).find("tr.more div.navi");
                        
                        if($(this).is(":checked")){
                            $(naviO).fadeOut(300, function(){
                                set_button(twin, sab);
                            });
                        } else {
                            $(naviO).fadeIn(300, function(){
                                set_button(twin, sab);
                            });
                        }
                    });
                    
                    $(tinhalt).find("#nurrollen").off().on("click", function(){
                        var rollenO = $(tinhalt).find("tr.more div.rollen");
                        
                        if($(this).is(":checked")){
                            $(rollenO).fadeIn(300, function(){
                                set_button(twin, sab);
                            });
                        } else {
                            $(rollenO).fadeOut(300, function(){
                                set_button(twin, sab);
                            }).find("input[type=checkbox]").not(":disabled").removeAttr("checked");
                        }
                    });
                    
                    calcDatePicker(tinhalt);
    
                    $(tinhalt).find("tr.more input.vonbis").off().on("click", function(){
                        var pare = $(this).parent("td");
                        var nextone = $(pare).next("td");
                        var timetd = $(pare).siblings("td.time");
                        var ele = $(nextone).nextAll("td").find("input, select");
                        
                        if($(this).is(":checked")){
                            $(nextone).removeClass("notaktiv");
                            $(timetd).removeClass("notaktiv");
                            $(ele).removeAttr("disabled");
                        } else {
                            $(nextone).addClass("notaktiv");
                            $(timetd).addClass("notaktiv");
                            $(ele).attr("disabled", "disabled").val("");
                        }
                    });   
                    
                    $(sab).find("input.bs2").off().on("click", function(){
                        $(this).attr("disabled", "disabled");
                        
                        if($(tinhalt).find("input[name=frei]").filter(":checked").val() != '1'){
                            $(se).find("div.fehlerbox").show();
                        } else {
                            $(se).find("div.fehlerbox").hide();
                        }
                        
                        $.post('inc_structure.php', {
                            index: 'n151',
                            sid: sid,
                            f: $(tinhalt).find("form").serialize()
                        }, function(data){
                            $(twin).find("p.close").trigger("click");
                            
                            struktur_start({
                                open: sid
                            });
                        });
                    });                            
                });
            }
        });
    });
    
    // DK-Zuordnung oeffnen
    $(se).find("#se_open_dk").off().on("click", function(){
        fenster({
            id: 'n155',
            blackscreen: '',
            width: 560,
            cb: function(twin, tinhalt){
                $.post('inc_structure.php', {
                    index: 'n155',
                    sid: sid
                }, function(data){
                    logincheck(data);
                    
                    $(tinhalt).html(data);  
                    
                    var sab = $(tinhalt).find("div.box_save");
                    save_button($(twin));  
                    
                    $(tinhalt).find("input[name=dklasse]").off().on("click", function(){
                        var so = $(tinhalt).find("#struk_dok_table2B");
                        if($(this).val() == '1') {
                            $(so).fadeIn(300, function(){
                                set_button(twin, sab);
                            });
                        } else {
                            $(so).fadeOut(300, function(){
                                set_button(twin, sab);
                            });
                        }
                    });               
                    
                    $(tinhalt).find("input.dk_type").off().on("click", function(){
                        if($(tinhalt).find("input.dk_type").is(":checked"))
                            $(tinhalt).find("#dk_more_opt").fadeIn();
                        else
                            $(tinhalt).find("#dk_more_opt").fadeOut();
                    }); 
                    
                    $(sab).find("input.bs2").off().on("click", function(){
                        $(this).attr("disabled", "disabled");
                        
                        $.post('inc_structure.php', {
                            index: 'n156',
                            sid: sid,
                            f: $(tinhalt).find("form").serialize()
                        }, function(data){
                            var open_id = parseInt(data);
                            $(twin).find("p.close").trigger("click");
                            
                            strukturelement_strukdok(fen, sd, sid);
                            
                            struktur_start({
                                open: (open_id?open_id:sid)
                            });
                        });
                    });                       
                });
            }
        });
    });
    
    // Strukturelement Slots oeffnen
    $(se).find("#se_slots").off().on("click", function(){
        fenster({
            id: 'n160',
            blackscreen: '',
            width: 730,
            cb: function(twin, tinhalt){
                $.post('inc_structure.php', {
                    index: 'n160',
                    sid: sid
                }, function(data){
                    $(tinhalt).html(data);
                    
                    save_button($(twin));
                
                    $(tinhalt).find("#vslots input[type=checkbox]").off().on("click", function(){
                        $(this).attr("disabled", true);
                        
                        var slot = $(this).data('id'); 
                        var open = ($(this).is(":checked")?1:0);
                        var me_cb = $(this);
                        
                        if(open == 0){
                            $(this).parents("p:first").addClass("inactive").find("label").show().siblings("a").hide();
                        } else {
                            $(this).parents("p:first").removeClass("inactive").find("a").show().siblings("label").hide();
                        }
                        
                        $.post('inc_structure.php', {
                            index: 'n161',
                            element: sid,
                            slot: slot,
                            open: open
                        }, function(data){
                            logincheck(data);
                            $(me_cb).attr("disabled", false);
                        });
                    });
                    
                    $(tinhalt).find("#vslots a").off().on("click", function(){
                        var slot = $(this).data('id');
                        
                        fenster({
                            id: 'n148',
                            width: 600,
                            blackscreen: '2',
                            cb: function(mf, inhalt){
                                $.post('inc_structure.php', {
                                    index: 'n140',
                                    slot: slot,
                                    sid: sid,
                                }, function(data){
                                    logincheck(data);
                                    $(inhalt).html(data);
                                    
                                    var sd = $(mf).find("#struk_doks");
                                    $(inhalt).find("div.sprachbox").remove();
                                    
                                    strukturelement_strukdok(mf, sd, sid, slot);
                                });
                            }
                        });
                    });
                });
            }
        });
    });        
}

function open_strukturelement(sid, neu){
    fenster({
        'id': 'n140',
        'width': 600,
        'blackscreen': 'none',
        'reload': true,
        'cb': function(mf, inhalt){
            $.post('inc_structure.php', {
                index: 'n140',
                sid: sid,
                neu: neu
            }, function(data){
                logincheck(data);
                $(inhalt).html(data);
                
                init_strukturelement(mf);
                save_button($(mf));
            });
        }
    });
}

function extern_strukturelement_strukdok(){
    var fen = $("#fn140");
    var fen_slots = $("#fn148");
    var fen_error = $("#fn149");
    
    if($(fen)[0]){
        var sd = $(fen).find("#struk_doks");
        var sid = $(fen).find("input[name=sid]").val();
        
        strukturelement_strukdok(fen, sd, sid);
    }
    if($(fen_slots)[0]){
        var sd = $(fen_slots).find("#struk_doks");
        var slot = $(fen_slots).find("input[name=slot]").val();
        var sid = $(fen).find("input[name=sid]").val();
        
        strukturelement_strukdok(fen_slots, sd, sid, slot);
    }
    if($(fen_error)[0]){
        var sd = $(fen_error).find("#struk_doks");
        var error = $(fen_error).find("input[name=error]").val();
        
        strukturelement_strukdok(fen_error, sd, 0, '', error);
    }
}

function strukturelement_strukdok(fen, sd, sid, slot, error){
    var darea = $(sd).find("div.dokumente");
    var slotdk = $(fen).find("input[name=slot_dclass]").val();
    var loadme = $(sd).find("div.loadme");
    
    if(error == undefined)
        var error = '';
        
    var sid_slot = (sid && slot?true:false);
    
    // Dokumente neu laden
    function reload_dks(){
        $(loadme).show();
        
        $.post('inc_structure.php', {
            index: 'n141',
            sid: sid,
            slot: slot,
            error: error
        }, function(data){
            logincheck(data);
            $(darea).html(data);
            
            $(loadme).hide();
            
            var doks = $(darea).children("article");
            
            function calcHeight(){      
                var max_height = 0;
                $(doks).each(function(){                
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
            $(doks).find("img").on("load", function(){ 
                vorgeladen ++; 
                
                if(vorgeladen == $(doks).find("img").length){
                    calcHeight();
                }
            });
            
            $(doks).find("a.titel").off().on("click", function(){
                if(!$(this).hasClass("duebersicht"))
                    var clickdumme = $('<a class="inc_documents" id="n250" rel="'+$(this).data('id')+'"></a>');
                else
                    var clickdumme = $('<a class="inc_documents" id="n290" rel="'+$(this).data('id')+'"></a>');
                    
                if(sid_slot){
                    $("#fn148 p.close").trigger("click");
                    $("#fn160 p.close").trigger("click"); 
                }
                    
                neu(clickdumme);
            });
            
            $(doks).find("a.del").off().on("click", function(){
                var delid = $(this).data('id');
                
                sfrage_show('Möchten Sie die Zuordnung zwischen diesem Dokument und Strukturelement wirklich entfernen?');
                $("#sfrage button:last").on("click", function(){
                    $.post('inc_structure.php', {
                        index: 'n142',
                        task: 'delete',
                        sd: delid,
                        sid: sid,
                        slot: slot,
                        error: error
                    }, function(data){
                        logincheck(data);
                        reload_dks();
                    });
                });
            });
            
            var containm = $(fen).find("#strukturelement");
            
            $(darea).sortable({
                items: 'article',
                axis: 'y',
                handle: 'div.drag',
                containment: containm,
                stop: function(){
                    var neworder = '';
                    $(darea).children("article").each(function(){
                        neworder += (neworder?',':'')+$(this).data('sd');
                    });
                    
                    $.post('inc_structure.php', {
                        index: 'n142',
                        task: 'sort',
                        neworder: neworder,
                        sid: sid,
                        slot: slot,
                        error: error
                    }, function(data){
                        logincheck(data);
                    });
                }
            });
        });
    }
    reload_dks();
    
    // reload
    $(fen).find("p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        reload_dks();
    });
    
    // Neues Dokument anlegen
    $(sd).find("button.insert-new").off().on("click", function(e){
        e.preventDefault();
        
        var relquery = (slot?slot:(error?'fks_error_'+error:sid));
        if(slotdk != '') relquery = 'fks_slot_dk_'+slot+'_xx_xx_'+slotdk;
        
        if(sid_slot){
            if(slotdk != ''){
                relquery = 'fks_slot_sid_dk_'+slot+'_xx_xx_'+slotdk+'_xx_xx_'+sid;
            } else {
                relquery = 'fks_slot_sid_'+slot+'_xx_'+sid;
            }
        }
        
        fenster({
            id: 'n210',
            width: 530,
            blackscreen: (!sid_slot?'none':'3'),
            cb: function(nwin, ninhalt){
                $.post('inc_documents.php', {
                    index: 'n210',
                    rel: relquery
                }, function(data){ 
                    logincheck(data);
                
                    $(ninhalt).html(data);
                    setFocus(nwin);
                    
                    newDocumentScript();
                });
            }
        });
    });
    
    // Bestehndes Dokument einfuegen
    $(sd).find("button.insert").off().on("click", function(e){
        e.preventDefault();
        
        if(slotdk == ''){ // normal document
            fenster({
                id: 'n142',
                width: 964,
                blackscreen: (!sid_slot?'':'3'),
                cb: function(nwin, ninhalt){
                    $.post('inc_documents.php', {
                        index: 'n200',
                        rel: 0
                    }, function(data){ 
                        logincheck(data);
                    
                        $(ninhalt).html(data);
                        setFocus(nwin);
                               
                        function dokumente_start() {
                            var fn200 = ninhalt;
                            
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
                                        $(this).off("click");
                                        $(nwin).find("p.close").trigger("click");
                                        
                                        $.post('inc_structure.php', {
                                            index: 'n142',
                                            task: 'add',
                                            dok: selected,
                                            sid: sid,
                                            slot: slot,
                                            error: error
                                        }, function(data){
                                            reload_dks();
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
                        dokumente_start();
                    });
                }
            });
        } else {
            fenster({
                id: 'n295',
                width: 960,
                blackscreen: (!sid_slot?'':'3'),
                cb: function(fn295, nfinhalt){
                    $.post('inc_documents.php', {
                        index: 'n290',
                        rel: slotdk,
                        choose: true
                    }, function(data){
                        logincheck(data);
                        $(nfinhalt).html(data);
                        setFocus(fn295);
                        
                        dko_start(function(fn290){
                            $(fn290).find("button.new_doc").remove();  
                            var barea = $(fn290).find("div.boxedarea");
                            
                            $(barea).hide().find("button.takeit").off().on("click", function(e){
                                e.preventDefault();
                                $(this).attr("disabled", "disabled").html('bitte warten...');
                                
                                $(barea).find("li").each(function(ccc){
                                    var selected = $(this).data('id');
                                    
                                    $.post('inc_structure.php', {
                                        index: 'n142',
                                        task: 'add',
                                        dok: selected,
                                        sid: sid,
                                        slot: slot,
                                        error: error
                                    }, function(data){
                                        if(ccc + 1 >= $(barea).find("li").length)
                                            reload_dks();
                                    });
                                });
                                
                                $("#fn295 p.close").trigger("click");
                            });                                                                
                        }, function(fn290, mtable){
                            var rlinks = $(mtable).find("td").not(".more_results").find("a").not(".choose");
                            $(rlinks).each(function(){
                                $(this).replaceWith($(this).contents());
                            });
                            
                            $(mtable).find("a.choose").off().on("click", function(){
                                if(!$(fn290).find("li.listitem_"+$(this).data('id'))[0]){
                                    var add_li = $('<li data-id="'+$(this).data('id')+'" class="listitem_'+$(this).data('id')+'"><span>'+$(this).data('titel')+'</span><a></a></li>');
                                    $(fn290).find("div.boxedarea").show().find("ul").append(add_li);
                                    
                                    $(add_li).children("a").off().on("click", function(){
                                        $(this).parent("li").remove();
                                    });
                                    
                                    set_button(fn295);
                                }
                            });
                        });
                    });
                }
            });
        }
    });
}

function strukturelement_sprachoptionen(inhalt){
    // Sprachen ein und ausklappen
    $(inhalt).find("td.more a.rbutton").each(function(){
        var parent = $(this).parents("div.sprache:first");
        var tds = $(parent).find("tr").not(".main").children("td");
        rbutton($(this), $(tds), 'Details', 'zurück', function(){
            $(parent).css('background', '#ffffff');
        }, function(){
            $(parent).css('background', 'none');
        });
    });
    
    $(inhalt).find("td.gcsnippet a.rbutton").each(function(){
        var cont = $(this).siblings("div.gsnippet");
        rbutton($(this), cont, 'anzeigen', 'schließen');
    });
    
    $(inhalt).find("input[name=aktiv]").off("click").on("click", function(){
        var tpar = $(this).parents("table.element_sprachen");
        var trpar = $(tpar).find("input, textarea, select").not("input[name=aktiv], input.ht2, input.url2");
        
        if($(this).is(":checked")){
            $(tpar).removeClass("inaktiv").addClass("aktiv");
            $(trpar).removeAttr("disabled");
        } else {
            $(tpar).addClass("inaktiv").removeClass("inaktiv");
            $(trpar).attr("disabled", "disabled");
        }
    });
            
    
    function anzahl_zeichen(obj, ta){
        var anzahl = $(ta).val().length;
        $(obj).find("span").text(anzahl);
    }
    $(inhalt).find("textarea.html_desc").each(function(){
        var me = $(this);
        var apz = $(this).parents("tr:first");
        var pare = $(me).parents("div.sprache");
        
        anzahl_zeichen(apz, me);
        $(this).off("keyup").on("keyup", function(){
            anzahl_zeichen(apz, me);
            $(pare).find("div.gsnippet p.s_desc span").html(kuerzen($(me).val(), 150));
        });
    });
    
    $(inhalt).find("input.ht1").off("keyup").on("keyup", function(){
        var pare = $(this).parents("div.sprache");
        var meval = $(this).val();
        
        if(!$(pare).find("input.autotitle").is(":checked"))
            $(pare).find("div.gsnippet p.s_titel").html(kuerzen(meval, 55));
    });
    
    $(inhalt).find("input.url1").off("keyup").on("keyup", function(){
        var pare = $(this).parents("div.sprache");
        var meval = $(this).val();
        
        if(!$(pare).find("input.autourl").is(":checked"))
            $(pare).find("div.gsnippet p.s_url span").html(kuerzen(meval, 5));
    });
    
    $(inhalt).find("input.autotitle").off("click").on("click", function(){
        var mepar = $(this).parents("td:first");
        var autotitle1 = $(mepar).children("input.ht1");
        var autotitle2 = $(mepar).children("input.ht2");
        var pare = $(mepar).parents("div.sprache");
        
        if($(this).is(':checked')){
            $(autotitle1).hide().val(''); 
            $(autotitle2).show();
            $(pare).find("div.gsnippet p.s_titel").html(kuerzen($(autotitle2).val(), 55));
        }
        else {
            $(autotitle1).show().val($(autotitle2).val()); 
            $(autotitle2).hide();
            $(pare).find("div.gsnippet p.s_titel").html(kuerzen($(autotitle2).val(), 55));
        }
    });
    
    $(inhalt).find("input.autourl").off("click").on("click", function(){
        var mepar = $(this).parents("td:first");
        var url1 = $(mepar).children("input.url1");
        var url2 = $(mepar).children("input.url2");
        var pare = $(mepar).parents("div.sprache");
        
        if($(this).is(':checked')){
            var elt = $(pare).find("input.ntitle");
            var tit = $(elt).val();
            auto_url(tit, elt);
            
            $(url1).hide().val('');  
            $(url2).show();
        }
        else {
            $(url1).show().val($(url2).val()); 
            $(url2).hide();
            $(pare).find("div.gsnippet p.s_url span").html(kuerzen($(url2).val(), 50));
        }
    });
    
    $(inhalt).find("input.url1").off("blur").on("blur", function(){
        var tit = $(this).val();
        var el = $(this);
        var pare = $(el).parents("div.sprache");
        
        $.post('inc_structure.php', {
            index: 'n140au',
            url: tit
        }, function(data){
            logincheck(data);
            $(el).val(data);
            
            if(!$(pare).find("input.autourl").is(":checked"))
                $(pare).find("div.gsnippet p.s_url span").html(kuerzen(data, 50));
        });
    });
    
    $(inhalt).find("input.ntitle").off("keyup change").on("keyup change", function(){
        var langu = $(this).data('lan');
        var tit = $(this).val();
        var el = $(this);
        
        auto_titel(langu, tit, el);
        auto_url(tit, el);
    });
    
    var mtitle = $(inhalt).find("input[name=titel]");
    var leere_titel = new Array();
    $(mtitle).off("focus").on("focus", function(){
        leere_titel = new Array();
        
        $(inhalt).find("input.ntitle").each(function(){
            var cbs = $(this).parent("td").siblings("td.auswahl").children("input[name=aktiv]");
            if($(this).val() == '' && ($(cbs).is(":checked") || !$(cbs)[0])){
                leere_titel.push($(this).attr("id"));
            }
        });
    }).off("keyup change").on("keyup change", function(){
        var valt = $(this).val();
        
        for(var d=0; d<leere_titel.length; d++){
            var el = $(inhalt).find('#'+leere_titel[d]);
            var langu = $(el).data('lan');
            
            $(el).val(valt);
            auto_titel(langu, valt, el);
            auto_url(valt, el);
        }
    });
    
    $(mtitle).focus();
    
    function auto_titel(langu, tit, el){
        var pare = $(el).parents("div.sprache");
            
        $.post('inc_structure.php', {
            index: 'n140at',
            titel: tit,
            lan: langu
        }, function(data){
            logincheck(data);
            $(pare).find("input.ht2").val(data);
            
            if($(pare).find("input.autotitle").is(":checked"))
                $(pare).find("div.gsnippet p.s_titel").html(kuerzen(data, 55));
        });
    }
    
    function auto_url(url, el){
        var pare = $(el).parents("div.sprache");
            
        $.post('inc_structure.php', {
            index: 'n140au',
            url: url
        }, function(data){
            logincheck(data);
            $(pare).find("input.url2").val(data);
            
            if($(pare).find("input.autourl").is(":checked"))
                $(pare).find("div.gsnippet p.s_url span").html(kuerzen(data, 50));
        });
    }
}