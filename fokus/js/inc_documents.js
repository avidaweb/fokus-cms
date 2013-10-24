/// DOKUMENTE VERWALTEN

function dokumente_start(){
    if($("#fn200")[0]){
        var fn200 = $("#fn200 div.inhalt"); 
        
        function doc_verwalten_inhalt(){
            var table = $(fn200).find("#docs_auflistung");
        
            dok_uebersicht_2(fn200); 
            
            $.get('inc_documents.php', {
                index: 'n201',
                q: d_search,
                opt: d_opt,
                dklassen: d_dklassen,
                sortA: d_sortA,
                sortB: d_sortB,
                limit: $(fn200).find("input#akt_limit").val(),
                rel: $("#fn200 #is_freigabe").val(),
                real_doc_admin: 'true'
            }, function(data){ logincheck(data);  
                $(table).html(data);
                
                $(table).find("td a").off("click").on("click", function(){
                    var d = $(this);
                    
                    if($(this).attr("class") == 'freigeben'){
                        $.get('inc_documents.php', {
                            index: 'n202',
                            v: $(d).attr("rel")
                        }, function(data){ logincheck(data); 
                            $(d).replaceWith('Freigegeben');
                            
                            $.get('inc_documents.php', {
                                index: 'n202_searchengines',
                                v: $(d).attr("rel")
                            });
                        });
                    } else {
                        neu(d);
                    }
                });
                
                dok_uebersicht_3(fn200, table, doc_verwalten_inhalt);
                dok_uebersicht_4(fn200, table, doc_verwalten_inhalt);
            }); 
        }
        doc_verwalten_inhalt();
        
        dok_uebersicht_1(fn200, doc_verwalten_inhalt);
        
        $(fn200).find("button.inc_documents").off("click").on("click", function(){
            neu($(this));
        });
    }
}

if($("#fn200")[0] && lastindex == 'n200') {
    dokumente_start(); 
}





if($("#fn290")[0] && lastindex == 'n290') {
    dko_start(); 
}


/// EIN DOKUMENT BEARBEITEN
if($("#fn250")[0] && lastindex == 'n250') { 
    var qsprachen = $("#fn250 #quick_sprachen");
    var qvorschau = $("#fn250 #quick_preview");
    var aktiv_block = 0;
    var ausgewaehltes_dokument = 0;
    var auswahl = 0;
    
    ausgewaehltes_dokument = rel;
    
    // Schnelles Wechseln der verfuegbaren Sprachen
    function sprache_laden(){
        $.get('inc_documents.php', {
            index : 'n250l',
            id : ausgewaehltes_dokument
        }, function(data){ 
            logincheck(data);
            
            qsprachen = $("#fn250 #quick_sprachen");
            $(qsprachen).html(data);
            
            var sprv = $(qsprachen).parents("#sprache_vorschau:first");
            if($(qsprachen).children("a").length > 1)
                $(sprv).show();
            else
                $(sprv).hide();
        
            $(qsprachen).find("a").off().on("click", function(){
                clicklan = $(this).attr("rel");
                
                $.post('inc_documents.php', {
                    index : 'n252a',
                    lan : clicklan,
                    id : ausgewaehltes_dokument
                }, function(data){
                    tab_clicked(auswahl);
                    sprache_laden();
                });
            });
        });
    }
    sprache_laden();
    
    // Schnell-Vorschau oeffnen
    var bigv = $("#big_vorschau");
    var mainO = $("#main, body");
    $(qvorschau).off("click").on("click", function(){
        $(mainO).addClass("mit_vorschau");
        $(bigv).show().html('<br /><br /><br /><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
        
        $.post('inc_documents.php', {
            index : 'n200_quick_preview',
            id : ausgewaehltes_dokument
        }, function(data){
            $(bigv).html(data);
            
            var close = $(bigv).find("a.close");
            var iframe = $(bigv).find("iframe");
            
            $(close, iframe).off("click").on("click", function(){
                $(mainO).removeClass("mit_vorschau");
                $(bigv).hide().html('');
            });
            
            $(bigv).find("a.newtab").off("click").on("click", function(){
                var iurl = $(iframe).attr("src");
                $(this).attr("href", iurl);
                
                $(mainO).removeClass("mit_vorschau");
                $(bigv).hide().html('');
            }); 
            
            $(iframe).height($(window).height() - 33);
            
            var ttd = '', tbereich = '';
            function akt(){ 
                $.post('inc_documents.php', {
                    index : 'n200_quick_preview',
                    id : ausgewaehltes_dokument,
                    td: ttd,
                    bereich: tbereich,
                    justframe: true
                }, function(data){ 
                    $(iframe).attr("src", data);
                });
            }
            
            $(bigv).find("select[name=td]").off("change").on("change", function(){ 
               ttd = $(this).val();
               akt(); 
            });
            
            $(bigv).find("select[name=bereich]").off("change").on("change", function(){
               tbereich = $(this).val();
               akt(); 
            });
        });
    });
    
    
    // Freigabe-Menue
    function zurfreigabe(first_load){
        $.get('inc_documents.php', {
            index : 'n250f',
            id : ausgewaehltes_dokument
        }, function(data){ logincheck(data); 
            var o_zurfreigabe = $("#fn250 #zurfreigabe");
            
            if(data != '') {
                $(o_zurfreigabe).html(data);
                
                if(first_load) {
                    $(o_zurfreigabe).show();
                } else {
                    $(o_zurfreigabe).slideDown();
                    extern_strukturelement_strukdok();
                }
                
                var o_fmore = $(o_zurfreigabe).find("div.fmore");
                
                $(o_zurfreigabe).find("a.flink").off().on("click", function(){
                    $(o_fmore).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                    
                    $.get('inc_documents.php', {
                        index : 'n250f',
                        a: 1,
                        id : ausgewaehltes_dokument
                    }, function(data){ logincheck(data); 
                        dokumente_start();
                        dko_start();
                        zurfreigabe();
                        tab_clicked(auswahl);
                        extern_strukturelement_strukdok();
                    });        
                });
                
                $(o_zurfreigabe).find("div.fopt").off().on("click", function(e){
                    e.stopPropagation();
                    $(o_fmore).show();
                });
                
                $(o_fmore).off().on("mouseleave", function(){
                    $(o_fmore).hide();
                });
                
                $(o_zurfreigabe).find("a.clink").off().on("click", function(e){
                    e.stopPropagation();
                    $(o_fmore).hide();
                });
                
                $(o_zurfreigabe).find("a.flink2").off().on("click", function(e){ 
                    $(o_fmore).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                    e.stopPropagation();
                    
                    var d = $(this);
                    $.get('inc_documents.php', {
                        index: 'n202',
                        a: 1,
                        v: $(d).attr("rel")
                    }, function(data){ logincheck(data); 
                        dokumente_start();
                        dko_start();
                        zurfreigabe();
                        tab_clicked(auswahl);
                        extern_strukturelement_strukdok();
                        
                        $.get('inc_documents.php', {
                            index: 'n202_searchengines',
                            v: $(d).attr("rel")
                        });
                    });       
                });
                
                $(o_zurfreigabe).find("a.vlink").off().on("click", function(e){
                    e.stopPropagation();
                    
                    sfrage_show('Wollen Sie wirklich alle ungespeicherten &Auml;nderungen verwerfen?');
                    $("#sfrage button:last").on("click", function(){
                        $(o_fmore).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                        
                        $.get('inc_documents.php', {
                            index : 'n203',
                            dv: -1,
                            id : ausgewaehltes_dokument
                        }, function(data){ logincheck(data); 
                            dokumente_start();
                            dko_start();
                            zurfreigabe();
                            tab_clicked(auswahl);
                            extern_strukturelement_strukdok();
                        });
                    });        
                });
                
                $(o_zurfreigabe).find("a.wlink").off().on("click", function(e){                     
                    fenster({
                        id: 'n204',
                        blackscreen: '',
                        width: 600,
                        cb: function(neww, inhalt){
                            $.post('inc_documents.php', {
                                index: 'n204',
                                id: ausgewaehltes_dokument,
                                a: 'get'
                            }, function(data){ logincheck(data);
                                $(inhalt).html(data);
                        
                                setFocus(neww);
                                
                                var sb = $(inhalt).find("div.box_save"); 
                                
                                $(inhalt).find("textarea").ckeditor(function() { 
                                    my_w_ckeditor = $(inhalt).find("textarea").ckeditorGet();
                                                                
                                    load_fck_button(this); 
                                    set_button(neww);
                                }, ckconfig);
                                
                                
                                $(sb).find("button:first").off("click").on("click", function(e){
                                    e.preventDefault();
                                    $(neww).find("p.close").trigger("click");
                                });
                                
                                $(sb).find("button:last").off().on("click", function(e){
                                    e.preventDefault();
                                    
                                    $.post('inc_documents.php', {
                                        index: 'n204',
                                        id: ausgewaehltes_dokument,
                                        a: 'set',
                                        b: $(inhalt).find("select#bweiter").val(), 
                                        text: $(inhalt).find("textarea").val()
                                    }, function(data){ logincheck(data);
                                        $(neww).find("p.close").trigger("click");
                                        $("#fn250 p.close").trigger("click");
                                        dokumente_start();
                                        dko_start();
                                    });
                                    
                                    $(inhalt).html('<div class="inhalt"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></div>');
                                });
                            });
                        }
                    });     
                });
            } else {
                $(o_zurfreigabe).hide();
            }
        });
    }
    
    function tab_clicked(auswahl){
        var fn250 = $("#fn250");
        
        var sprv = $(fn250).find("div#sprache_vorschau");
        if($(sprv).find("#quick_sprachen a").length > 1)
            $(sprv).show();
        else
            $(sprv).hide();
        
        $.get('inc_documents.php', {
            index : 'n251',
            a : auswahl,
            id : ausgewaehltes_dokument
        }, function(data){ logincheck(data); 
            if(auswahl == 3) {
                $(fn250).css("width", "700px").find("p.move").width(536);
            } else if(auswahl == 2) {
                $(fn250).css("width", "935px").find("p.move").width(771);
            } else if(auswahl == 4) {
                $(fn250).css("width", "1010px").find("p.move").width(846);
            } else if(auswahl == 5) {
                $(fn250).css("width", "935px").find("p.move").width(771);
            } else {
                $(fn250).css("width", "935px").find("p.move").width(771);
            }
            
            if(auswahl != 3)
                $(fn250).css('left', ($("#main").width() / 2 - $(fn250).width() / 2) + 'px');
            else
                $(fn250).css('left', ($("#main").width() / 2 - ($(fn250).width() + 235) / 2) + 'px');
            
            zurfreigabe(true);
            
            $("#fn254").remove();
            $("#mehrfachauswahl").fadeOut();
            var docO = $(fn250).find("#doc");
            
            if(auswahl == 3)
                $(docO).addClass("extend");
            else
                $(docO).removeClass("extend");                
                
            eltern = $(docO).find("div.docC").eq($(docO).tabs('option', 'selected'));
            $(docO).find("div.docC").html('');
            $(eltern).html(data); 
            
            if(auswahl == 1)
                tab_doc_1();
            else if(auswahl == 2)
                tab_doc_2();
            else if(auswahl == 3)
                tab_doc_3();
            else if(auswahl == 4)
                tab_doc_4();
            else if(auswahl == 5)
                tab_doc_5();
                
            if($(fn250).find("div.box_save").css("display") == "block") 
                $(fn250).find("div.box_save").hide();
        });
    }
    
    var pre_tab = 0;
    
    function dokument_reload(){ 
        if($("#fn250 #quick_sprachen")[0]){ // Wenn das Dokument nicht gesperrt ist
            
            // Dokument gesperrt halten
            var dok_closed_timer = null;
            
            function dok_close(){
                clearTimeout(dok_closed_timer);
                
                if($("#fn250")[0] && $("#fn250 #open_dok_id").val() == ausgewaehltes_dokument){
                    $.get('inc_documents.php', {
                        index : 'n250_close',
                        id : ausgewaehltes_dokument
                    }, function(data){ 
                        logincheck(data);
                        
                        dok_closed_timer = setTimeout(function(){
                            dok_close();
                        }, 10000);
                    });
                }
            }
            dok_close();
            
            if(pre_tab != -1)
                pre_tab = parseInt($("#fn250 #indiv_tab").val());
            
            $("#fn250 #doc").tabs({ 
                tabTemplate: '<li><a href="#{href}">#{label}</a></li>',
                selected: pre_tab,
                show: function(event, ui){ 
                    auswahl = ((pre_tab > 0?pre_tab:$(this).tabs('option', 'selected')) + 1); 
                    laden($('#doc'+auswahl), true, false, '_white');
                    pre_tab = -1;
                    
                    tab_clicked(auswahl);
                },
                create: function(){
                    $("#fn250 #docN").css({
                        "height": "auto",
                        "overflow": "inherit"
                    });
                    sprache_laden();
                }
            });  
        } else { // Falls das Dokument gesperrt ist
            var dcO = $("#fn250 #dok_closed");
            
            $(dcO).find("a.inc_communication").off("click").on("click", function(){ // PN an Bearbeiter senden
                neu($(this));
            });
            
            $(dcO).find("a.get_dokument").off("click").on("click", function(){ // Arbeitskopie des Dokuments uebernehmen
                
                $.get('inc_documents.php', {
                    index: 'n250_takeover',
                    id: ausgewaehltes_dokument
                }, function(data){ 
                    logincheck(data); 
                    
                    $.get('inc_documents.php', {
                        index: 'n250',
                        id: ausgewaehltes_dokument
                    }, function(data){ 
                        logincheck(data); 
                        
                        $("#fn250 div.inhalt").html(data);
                        
                        dokument_reload();
                        dokumente_start();
                        dko_start();
                    });
                });
            });
            
            $(dcO).find("a.dfreigabe").off("click").on("click", function(){ // Dokument freigeben
                var d = $(this);
                
                $.get('inc_documents.php', {
                    index: 'n202',
                    a: 1,
                    v: $(d).attr("rel")
                }, function(data){ 
                    logincheck(data); 
                    
                    $.get('inc_documents.php', {
                        index: 'n250',
                        id: ausgewaehltes_dokument
                    }, function(data){ 
                        logincheck(data); 
                        
                        $("#fn250 div.inhalt").html(data);
                        
                        dokument_reload();
                        dokumente_start();
                        dko_start();
                    });
                      
                    $.get('inc_documents.php', {
                        index: 'n202_searchengines',
                        v: $(d).attr("rel")
                    });
                });
            });
        }
    }
    dokument_reload();
    
    function tab_doc_1(){
        var doc1O = $("#fn250 #doc1");
        var doc_id = $(doc1O).data('id');
              
        
        function saveAction(nwin, task){
            save_button(nwin);
            
            $(nwin).find("input, select, textarea").on("change keyup click", function(){  
                $(nwin).find("div.box_save").slideDown();
            });
            
            $(nwin).find("div.box_save input.bs2").off().on("click", function(e){
                e.preventDefault();
                $(this).attr("disabled", true);
                
                $.post('inc_documents.php', {
                    index: 'n251_1_save',
                    id: doc_id,
                    task: task,
                    f: $(nwin).find("form").serialize()
                }, function(data){
                    $(nwin).find("p.close").trigger("click");
                    dokument_reload();
                });
            });
        }
        
        
        // open format
        $(doc1O).find("a.format").off().on("click", function(){
            fenster({
                id: 'n251_1_format',
                width: 800,
                blackscreen: '',
                cb: function(nwin, content){
                    $.post('inc_documents.php', {
                        index: 'n251_1_format',
                        id: doc_id
                    }, function(data){
                        $(content).html(data);
                        saveAction(nwin, 'format'); 
                    });
                }
            });
        });
        
        
        // open custom fields
        $(doc1O).find("a.custom_fields").off().on("click", function(){
            fenster({
                id: 'n251_1_custom_fields',
                width: 800,
                blackscreen: '',
                cb: function(nwin, content){
                    $.post('inc_documents.php', {
                        index: 'n251_1_custom_fields',
                        id: doc_id
                    }, function(data){
                        $(content).html(data);
                        saveAction(nwin, 'custom_fields'); 
                    });
                }
            });
        });
        
        
        // open categories
        $(doc1O).find("a.categories").off().on("click", function(){
            fenster({
                id: 'n251_1_categories',
                width: 800,
                blackscreen: '',
                cb: function(nwin, content){
                    $.post('inc_documents.php', {
                        index: 'n251_1_categories',
                        id: doc_id
                    }, function(data){
                        $(content).html(data);
                        saveAction(nwin, 'categories'); 
                        
                        $(content).find("a.add").off().on("click", function(e){ 
                            e.stopPropagation();
                            
                            fenster({
                                clicked: $(this),
                                id: 'n198',
                                width: 740,
                                blackscreen: '2',
                                cb: function(mywin, myinhalt){                    
                                    $.get('inc_structure.php', {
                                        index : 'n190',
                                        rel : 0,
                                        just_select: true
                                    }, function(data){
                                        $(myinhalt).html(data);
                                
                                        $.getScript('js/inc_structure.js'); 
                                        
                                        setFocus(mywin);
                                    });
                                }
                            });
                            
                            $(nwin).find("div.box_save").slideDown();
                        });
                        
                        $(content).find("ul li a").off("click").on("click", function(){
                            var me_li = $(this).parent("li");
                            
                            sfrage_show('Wollen Sie diese Kategorie-Zuordnung wirklich entfernen?');
                            $("#sfrage button:last").on("click", function(){
                                $(me_li).remove();
                                $(nwin).find("div.box_save").slideDown();
                            });
                        });
                    });
                }
            });
        });
        
        
        // open options
        $(doc1O).find("a.options").off().on("click", function(){
            fenster({
                id: 'n251_1_options',
                width: 800,
                blackscreen: '',
                cb: function(nwin, content){
                    
                    function loadDocumentOptions(){
                        $(content).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                        
                        $.post('inc_documents.php', {
                            index: 'n251_1_options',
                            id: doc_id
                        }, function(data){
                            $(content).html(data);
                            saveAction(nwin, 'options'); 
                            
                            var sb = $(nwin).find("div.box_save"); 
                    
                            calcDatePicker(content);
                            
                            $(content).find("input.vonbis").off().on("click", function(){
                                if($(this).is(":checked")){
                                    $(this).parent("td").next("td").removeClass("notaktiv").nextAll("td").find("input, select").removeAttr("disabled");
                                } else {
                                    $(this).parent("td").next("td").addClass("notaktiv").nextAll("td").find("input, select").attr("disabled", "disabled").val("");
                                }
                                $(sb).show();
                            });
                        
                            $(content).find("a.delete_document").off().on("click", function(event){
                                sfrage_show('Wollen Sie dieses Dokument wirklich in den Papierkorb verschieben?');
                                $("#sfrage button:last").on("click", function(){
                                    $.post('inc_documents.php', {
                                        index: 'n258',
                                        a: 'del',
                                        id : doc_id
                                    }, function(data){ logincheck(data);
                                        $(nwin).find("p.close").trigger("click");
                                        $("#fn250 p.close").trigger("click");
                                        dokumente_start();
                                        dko_start();
                                    }); 
                                });
                            });
                        
                            $(content).find("a.close_document").off().on("click", function(event){
                                var addinginfo = '';
                                if($(sb).css("display") != "none")
                                    addinginfo = ' Alle gerade getätigten, ungespeicherten Einstellungen gehen dabei verloren!';
                                
                                sfrage_show('Wollen Sie dieses Dokument wirklich sperren?'+addinginfo);
                                $("#sfrage button:last").on("click", function(){
                                    $.post('inc_documents.php', {
                                        index: 'n258',
                                        a: 'sperr',
                                        id : doc_id
                                    }, function(data){ logincheck(data);
                                        loadDocumentOptions();
                                        tab_clicked(1);
                                        dokumente_start();
                                        dko_start();
                                    }); 
                                });
                            });
                        
                            $(content).find("a.open_document").off().on("click", function(event){ 
                                $.post('inc_documents.php', {
                                    index: 'n258',
                                    a: 'entsperr',
                                    id : doc_id
                                }, function(data){ logincheck(data);
                                    loadDocumentOptions();
                                    tab_clicked(1);
                                    dokumente_start();
                                    dko_start();
                                });
                            });
                        
                            $(content).find("a.delete_document_draft").off().on("click", function(e){
                                $.post('inc_documents.php', {
                                    index: 'n258',
                                    a: 'del_vorlage',
                                    id : doc_id
                                }, function(data){ logincheck(data);
                                    loadDocumentOptions();
                                    tab_clicked(1);
                                });
                            });
                        
                            $(content).find("a.document_draft").off().on("click", function(e){
                                fenster({
                                    id: 'n218',
                                    blackscreen: '2',
                                    width: 500,
                                    cb: function(nwin, ninhalt){
                                        $.post('inc_documents.php', {
                                            index: 'n210',
                                            vorlage: doc_id,
                                            rel: 0
                                        }, function(data){ logincheck(data);
                                            $(ninhalt).html(data);
                                            
                                            setFocus(nwin);
                                            
                                            var sbT = $(nwin).find("div.box_save"); 
                                            $(nwin).find("#doc_neu input, #doc_neu select").off().on("keyup change", function(){
                                                if($(sbT).css("display") == "none"){
                                                    $(sbT).show("blind", 500);
                                                    set_button(nwin, sb);
                                                }
                                            });
                                            
                                            $(sbT).find("input:last").off().on("click", function(){
                                                e.preventDefault();
                                                $(this).attr("disabled", "disabled");
                                                
                                                $.post('inc_documents.php', {
                                                    index: 'n258',
                                                    a: 'vorlage',
                                                    id : doc_id,
                                                    titel: $(nwin).find("input.titel").val()
                                                }, function(data){ logincheck(data);
                                                    $(nwin).find("p.close").trigger("click");
                                                    loadDocumentOptions();
                                                    tab_clicked(1);
                                                });
                                            });
                                            
                                            $(ninhalt).find("tr.dokument_title input").focus().off("keypress").on("keypress", function(e){
                                                if(e.keyCode == 13) {
                                                    $(sbT).find("input:last").trigger("click");
                                                    e.preventDefault();
                                                }
                                            });
                                        });
                                    }
                                });
                            });
                        });
                    }
                    
                    loadDocumentOptions();
                }
            });
        });
        
        
        // language settings
        tab_doc_1_language(doc1O);
        
        
        // save
        var sb = $("#fn250 div.box_save");

        $(doc1O).find("input, select, textarea").on("change keyup click", function(){
            $(sb).slideDown();
        });
        
        $(sb).find("input:last").off().on("click", function(){ 
            var selfbutton = $(this);
            $(selfbutton).attr("disabled", "disabled");
            
            $.post('inc_documents.php', { 
                index: 'n252',
                id : doc_id,
                f: $(doc1O).find("form.language_dialog").serialize()
            }, function(data){ 
                logincheck(data);
                $(selfbutton).removeAttr("disabled");
                
                tab_clicked(1);
                zurfreigabe();
                dokumente_start();
                dko_start();
                sprache_laden();
            }); 
        });       
    }
    
    
    function tab_doc_1_language(inhalt){
        var sb = $("#fn250 div.box_save"); 
        
        $(inhalt).find("td.more a.rbutton").each(function(){
            var parent = $(this).parents("div.sprache:first");
            var tds = $(parent).find("tr").not(".main").children("td");
            rbutton($(this), $(tds), 'Details', 'zurück', function(){
                $(parent).addClass("bordered");
            }, function(){
                $(parent).removeClass("bordered");
            });
        });
        
        $(inhalt).find("td.gcsnippet a.rbutton").each(function(){
            var cont = $(this).siblings("div.gsnippet");
            rbutton($(this), cont, 'anzeigen', 'schließen');
        });
        
        $(inhalt).find("input.activate_lang").off("click").on("click", function(){
            var tpar = $(this).parents("table.element_sprachen");
            var trpar = $(tpar).find("input, textarea, select").not("input.activate_lang, input.ht2, input.url2");
            var take_content = $(this).siblings("input[type=hidden]");
            
            if($(this).is(":checked")){
                $(tpar).removeClass("inaktiv").addClass("aktiv");
                $(trpar).removeAttr("disabled");
                
                var titleinp = $(tpar).find("input.ntitle");
                if($(titleinp).val() == ''){
                    $(titleinp).val(function(){
                        return $(inhalt).find("input[name=titel]").val();
                    });
                }
                $(titleinp).select().focus().trigger("keyup");
                
                var mom_aktiv = $(inhalt).find("form.language_dialog").data('active');
        		$(take_content).val(''); 
        		
        		sfrage_show('Möchten Sie die Inhalte aus der momentan aktiven Sprache ('+mom_aktiv+') übernehmen?');
        		$("#sfrage button:last").on("click", function(){
        			$(take_content).val(mom_aktiv);
                    $(titleinp).select().focus();
        		});
            } else {
                $(tpar).addClass("inaktiv").removeClass("inaktiv");
                $(trpar).attr("disabled", "disabled");
            }
            
            $(sb).show();
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
                $(sb).show();
            });
        });
        
        $(inhalt).find("input.ht1").off("keyup").on("keyup", function(){
            var pare = $(this).parents("div.sprache");
            var meval = $(this).val();
            
            if(!$(pare).find("input.autotitle").is(":checked"))
                $(pare).find("div.gsnippet p.s_titel").html(kuerzen(meval, 55));
            $(sb).show();
        });
        
        $(inhalt).find("input.url1").off("keyup").on("keyup", function(){
            var pare = $(this).parents("div.sprache");
            var meval = $(this).val();
            
            if(!$(pare).find("input.autourl").is(":checked"))
                $(pare).find("div.gsnippet p.s_url span").html(kuerzen(meval, 5));
            $(sb).show();
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
            
            $(sb).show();
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
            
            $(sb).show();
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
            $(sb).show();
        });
        
        var mtitle = $(inhalt).find("input[name=titel]");
        var leere_titel = new Array();
        $(mtitle).off("focus").on("focus", function(){
            leere_titel = new Array();
            
            $(inhalt).find("input.ntitle").each(function(){
                var cbs = $(this).parent("td").siblings("td.auswahl").children("input.activate_lang");
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
            
            $(sb).show();
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
    
    
    function tab_doc_2(){
        var doc2O = $("#fn250 #doc2");
        
        spalten_events();
        
        function refresh_spalten(){
            $.get('inc_documents.php', {
                index : 'n251',
                a : 2,
                id : ausgewaehltes_dokument
           }, function(data){ logincheck(data); 
                $(doc2O).html(data);
                zurfreigabe();
                spalten_events();
           });
        }
        
        function spalten_events(){
            $(doc2O).find("p.column-options").children("a").off().on("click", function(e){
                $(this).siblings("span").addClass("active").off().on("mouseleave", function(){
                    $(this).removeClass("active");
                });
            });
                
            $(doc2O).find("a.format").off().on("click", function(e){
                e.stopPropagation();
                var spalte = $(this).data('id');
                
                $.post('inc_documents.php', {
                    index: 'n251S',
                    id : ausgewaehltes_dokument,
                    spalte: spalte
                }, function(data){ 
                    $(doc2O).append(data); 
                    
                    var exto = $(doc2O).find("#extoL2S");
                    var mc0 = $(exto).find("#mcss_0");
                    var mc1 = $(exto).find("#mcss_1");
                    
                    var estr0 = $(exto).find("#ecss_0 tr");
                    var estr1 = $(exto).find("#ecss_1 tr");
                    var estr1nk = $(estr1).not("tr.kopf");
                    var estr0nk = $(estr0).not("tr.kopf");
                    
                    $(exto).fadeIn();
                    
                    $(mc0).off().on("click", function(){
                        if($(this).is(":checked")){
                            $(estr0nk).removeClass("inaktiv");
                        } else {
                            $(estr0nk).addClass("inaktiv");
                        }
                    });
                    $(mc1).off().on("click", function(){
                        if($(this).is(":checked")){
                            $(estr1nk).removeClass("inaktiv");
                            $(estr1nk).find("input[type=checkbox]").removeAttr("disabled");
                        } else {
                            $(estr1nk).addClass("inaktiv");
                            $(estr1nk).find("select, input").attr("disabled", "disabled");
                            $(estr1nk).find("input[type=checkbox]").removeAttr("checked");
                        }
                    });
                    
                    $(estr1nk).find("input[type=checkbox]").off().on("click", function(){
                        var tpar = $(this).parents("tr:first");
                        
                        if($(this).is(":checked")){
                            $(tpar).find("input[type=text], select").removeAttr("disabled");
                        } else {
                            $(tpar).find("input[type=text], select").attr("disabled", "disabled");
                        }
                    }); 
                    
                    $(estr1).find(".colorSelector").each(function(){
                        var aktivcp = $(this);
                        if($(aktivcp).attr("id") == "cS1") var tcolor = $(exto).find("input[name=color]").val();
                        if($(aktivcp).attr("id") == "cS2") var tcolor = $(exto).find("input[name=bordercolor]").val(); 
                        if($(aktivcp).attr("id") == "cS3") var tcolor = $(exto).find("input[name=bgcolor]").val();
                        
                        $(aktivcp).ColorPicker({
                        	color: tcolor,
                        	onShow: function (colpkr) { 
                        		$(colpkr).fadeIn(500);
                        		return false;
                        	},
                        	onHide: function (colpkr) {
                        		$(colpkr).fadeOut(500);
                        		return false;
                        	},
                        	onChange: function (hsb, hex, rgb) {
                        		$(aktivcp).css('backgroundColor', '#' + hex);
                                if($(aktivcp).attr("id") == "cS1") $(exto).find("input[name=color]").val('#'+hex);
                                if($(aktivcp).attr("id") == "cS2") $(exto).find("input[name=bordercolor]").val('#'+hex);
                                if($(aktivcp).attr("id") == "cS3") $(exto).find("input[name=bgcolor]").val('#'+hex);
                        	}
                        });
                    });
                    
                    $(exto).find("button:first").off().on("click", function(e){
                        e.preventDefault();
                        var mebut = $(this);
                        $(mebut).attr("disabled", true);
                                                        
                        $.post('inc_documents.php', {
                            index: 'n251S2',
                            id : ausgewaehltes_dokument,
                            spalte : spalte,
                            f: $(doc2O).find("#extoL2S form").serialize()
                        }, function(data){ 
                            logincheck(data);
                            $(mebut).removeAttr("disabled");
                            
                            zurfreigabe();
                            $(exto).fadeOut(function(){
                                $(exto).remove();
                            }); 
                        });  
                    });
                    
                    $(exto).find("button:last").off().on("click", function(e){
                        e.preventDefault();
                        $(exto).fadeOut(function(){
                            $(exto).remove();
                        });
                    });
            
                }); 
            }); 
            
            
            $(doc2O).find("#spalten").sortable({
                items: 'div.spalte',
                handle: 'p.move',
                axis: 'x',
                start: function(e, ui){
                    $(doc2O).find("div.add div.neu").remove();
                },
                stop: function(e, ui){
                    var c_ids = new Array();
                    $(doc2O).find("div.spalte").each(function(){
                        c_ids.push($(this).data('id')); 
                    });
                    
                    $.post('inc_documents.php', {
                        index: 'n253',
                        id : ausgewaehltes_dokument,
                        task: '9',
                        ids: c_ids
                    }, function(data){ logincheck(data);
                        zurfreigabe();
                        refresh_spalten();
                    });  
                } 
    		});
            
            
            $(doc2O).find("div.neu").off().on("click", function(e){
                e.preventDefault();
                var c_obj = $(this);
                
                $.post('inc_documents.php', {
                    index: 'n253',
                    id : ausgewaehltes_dokument,
                    task: '1',
                    pos: $(c_obj).parent(".add").prevAll('.spalte').length
                }, function(data){ logincheck(data);
                    zurfreigabe();
                    refresh_spalten();
                });  
            }).on("mouseover", function(){
                $(this).addClass("lcolor");
            }).on("mouseout", function(){
                $(this).removeClass("lcolor");
            });
        
            $(doc2O).find("div.spalte a.delete").off().on("click", function(e){
                e.preventDefault();
                var c_id = $(this).data('id');
                
                sfrage_show('Diese Spalte wirklich l&ouml;schen? Jeglicher Inhalt geht verloren!');
                $("#sfrage button:last").on("click", function(){
                    $.post('inc_documents.php', {
                        index: 'n253',
                        id : ausgewaehltes_dokument,
                        loesch: c_id,
                        task: '2'
                    }, function(data){ logincheck(data);
                        zurfreigabe();
                        refresh_spalten();
                    }); 
                }); 
            });
        
            $(doc2O).find("div.spalte strong").off().on("click", function(){
                $(this).hide().parent().find("input").show().focus();
            });
        
            $(doc2O).find("div.spalte input").off().on("blur", function(){
                $(this).hide().parent().find("strong").show();
            }).on("blur keyup", function(event){
                var valw = $(this).val(); 
                var c_id = $(this).data('id');
                
                $.post('inc_documents.php', {
                    index: 'n253',
                    id : ausgewaehltes_dokument,
                    spalte: c_id,
                    width: valw,
                    task: '6'
                }, function(data){ 
                    logincheck(data); 
                    if(data) alert(data);
                    
                    zurfreigabe();
                    if(event.type == 'blur')
                        refresh_spalten();
                });
            });
        
            $(doc2O).find("div.spalte a.plus").off("click").on("click", function(){
                var c_id = $(this).data('id');
                
                $.post('inc_documents.php', {
                    index: 'n253',
                    id : ausgewaehltes_dokument,
                    spalte: c_id,
                    task: '4'
                }, function(data){ logincheck(data); 
                    zurfreigabe();
                    refresh_spalten();
                });
            });
        
            $(doc2O).find("div.spalte a.minus").off("click").on("click", function(){
                var c_id = $(this).data('id');
                
                $.post('inc_documents.php', {
                    index: 'n253',
                    id : ausgewaehltes_dokument,
                    spalte: c_id,
                    task: '5'
                }, function(data){ logincheck(data); 
                    zurfreigabe();
                    refresh_spalten();
                });
            });
        
            $(doc2O).find("div.spalte a.close").off("click").on("click", function(){
                var c_id = $(this).data('id');
                
                $.post('inc_documents.php', {
                    index: 'n253',
                    id : ausgewaehltes_dokument,
                    spalte: c_id,
                    task: '8'
                }, function(data){ logincheck(data); 
                    zurfreigabe();
                    refresh_spalten();
                });
            });
            
            $(doc2O).find("#all_same_size").off("click").on("click", function(e){
                e.preventDefault();
                
                $.post('inc_documents.php', {
                    index: 'n253',
                    id : ausgewaehltes_dokument,
                    task: '7'
                }, function(data){ logincheck(data); 
                    zurfreigabe();
                    refresh_spalten();
                });
            });
            
            var hvalues = new Array();
            var counter = 0;
            $(doc2O).find("input.hspalte").each(function(){
                var tval = $(this).val();
                counter += parseInt(tval);
                
                hvalues.push(counter);
            });
            
            var nss = $(doc2O).find("#new_spalten_size");
            var nsss = $(nss).children("span");
            
            var ss = $(doc2O).find("#spalten_slider");
            $(ss).slider({
                values: hvalues,
                stop: function(event, ui) {
                    var val = ui.values;
                    
                    $.post('inc_documents.php', {
                        index: 'n253',
                        id : ausgewaehltes_dokument,
                        width: val,
                        task: '3'
                    }, function(data){
                        logincheck(data); 
                        zurfreigabe();
                        refresh_spalten();
                    }); 
                    
                    $(nss).hide();
                },
                start: function(){
                    $(nss).show();
                    $(nsss).html('??');
                },
                slide: function(event, ui) { 
                    var thisval = ui.value;
                    var ele = ui.handle;
                    
                    if(hvalues.length > 1){ 
                        var befor = $(ele).prevAll("a").length;
                        if(befor > 0){
                            var newval = $(ss).slider("values", (befor - 1));
                            var thisval = (thisval - newval);
                        }
                    }
                    
                    $(nsss).html(thisval);
                }
            });
        }
    }
    
    function tab_doc_3(){
        var fn250 = $("#fn250");
        var doc3O = $(fn250).find("#doc3");
        
        var loadme = $(doc3O).find("div.loadme");
        
        var lfs = $(doc3O).find("input#spaltennr").val();
        var load_first_spalte = $(doc3O).find(".spalte").eq(lfs);
        
        if($(load_first_spalte)[0] && lfs > 0)
            setAktiv($(load_first_spalte));
        else
            setAktiv($(doc3O).find(".spalte:first"));
        
        if(!$(fn250).find("#is_dklasse")[0] || $(fn250).find("#is_inhaltsbereich")[0]){
            
            var newwp = $('<table id="fn254"></table>');
            $(newwp).appendTo("#main").html('<tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="BB2"></td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>').addClass("fenster").css({
                'top': $(fn250).offset().top + 'px',
                'left': $(fn250).offset().left + $(fn250).outerWidth(true) - 5 + 'px',
                'width': '240px',
                'z-index': zindex
            });
            var mainTD = $(newwp).find("td.BB2"); 
            var offenPopup = '';
    
            $(window).on("scroll", function(){
                if($(fn250)[0]){
                    scroll_doks = true;
                    
                    var abstand = ($(window).scrollTop() - $(fn250).offset().top + 60); 
                    if(abstand > 0){ 
                        if($(newwp).offset().top + $(newwp).outerHeight(true) < $(fn250).offset().top + $(fn250).outerHeight(true))
                            $(newwp).css("top", ($(window).scrollTop() + 70) + "px");
                        
                        if($(newwp).offset().top + $(newwp).outerHeight(true) >= $(fn250).offset().top + $(fn250).outerHeight(true))
                            $(newwp).css("top", ($(fn250).offset().top + $(fn250).outerHeight(true) - $(newwp).outerHeight(true) - 5) + "px");
                    } else {
                        $(newwp).css("top", $(fn250).offset().top + 'px');
                    }
                }
            });
        
            function show_popup(){    
                $.get('inc_documents.php', {
                    index: 'n254',
                    offen: offenPopup,
                    klasse: ($(fn250).find("#is_dklasse")[0]?'true':'')
                }, function(data){ logincheck(data);
                    
                    $(mainTD).html(data);
                    var dpop = $("#docPop");
                    var popC = $(dpop).find("div.docPopC"); 
                    var blockz = $(popC).find("div.blockz"); 
            
                    $(popC).find("a").off().on("click", function(){
                        var aktiv = $(dpop).find("a.aktiv");
                        
                        $(blockz).stop(true, true).slideUp(250);
                        $(popC).find("a").removeClass("aktiv");
                        
                        if($(this).parent(popC).attr("id") != $(aktiv).parent(popC).attr("id")){
                            $(this).addClass("aktiv").next(blockz).stop(true, true).slideDown(250);
                            var b = $(this).next(blockz);
                            $(b).height($(b).height());
                            
                            offenPopup = $(this).parent(popC).attr("id");
                        }
                    });
                    
                    var oparent = $(dpop).offset().top;
                    $(blockz).sortable({
                        connectWith: (!$(fn250).find("#is_dklasse")[0]?'#aktiv':'#aktiv .inhaltsbereich'),
                        items: '.doc_inhalt_popup',
                        revert: 1,
                        forcePlaceholderSize: true,
                        placeholder: 'doc_inhalt_popup_ph',
                        zindex: 99999999,
                        handle: 'span.a',
                        sort: function(e, ui){
                            var helper = ui.helper;
                            $(helper).css("top", (ui.offset.top - oparent));
                        },
                        start: function(event, ui) {
                            oparent = $(dpop).offset().top;
                            $(dpop).find("div.doc_inhalt_popup:hidden").show();
                        },
                        stop: function(event, ui) {
                            show_popup();
                        }                       
                    });
                    
                    // Inhaltselemente auch bei Klick einfuegen
                    $(blockz).find("span.b").off().on("click", function(){
                    
                        var nblock = $(this).parent("div").attr("id").split("_");
                        var copy = nblock[2];
                        var extb = $(this).parent("div").data('extension'); 
                            
                        if(nblock[0] == "b"){  
                            if(!$("#fn250 #is_dklasse")[0]){
                                $.get('inc_documents.php', {
                                    index : 'n256',
                                    aktiv : $("#aktiv input:first").val(),
                                    id : ausgewaehltes_dokument,
                                    block: nblock[1],
                                    copy: copy,
                                    last: true,
                                    extb: extb
                                }, function(data){ 
                                    logincheck(data); 
                                    zurfreigabe();
                                    
                                    load_bloecke();
                                });
                            } else if($("#fn250 .inhaltsbereich")[0]){  
                                var ib = $("#fn250 .inhaltsbereich").filter(":first"); 
                                
                                $.get('inc_documents.php', {
                                    index : 'n256_dk',
                                    id : ausgewaehltes_dokument,
                                    block: nblock[1],
                                    copy: copy,
                                    last: true,
                                    extb: extb,
                                    ibid: $(ib).find("input.ib_id").val()
                                }, function(data){ 
                                    logincheck(data); 
                                    zurfreigabe();
                                    
                                    load_bloecke();
                                });
                            } 
                        }      
                    });
                    
                    
                    // insert blocks by shortcode
                    function insertBlockByShortcode(nr){
                        if(!nr) return false;
                        $(dpop).find("#docP1 div.block").eq((nr - 1)).children("span.b").trigger("click");
                    }   
                    
                    $(document).off('keydown.f1').on('keydown.f1', function(e){ e.preventDefault(); insertBlockByShortcode(1); });
                    $(document).off('keydown.f2').on('keydown.f2', function(e){ e.preventDefault(); insertBlockByShortcode(2); });
                    $(document).off('keydown.f3').on('keydown.f3', function(e){ e.preventDefault(); insertBlockByShortcode(3); });
                    $(document).off('keydown.f4').on('keydown.f4', function(e){ e.preventDefault(); insertBlockByShortcode(4); });
                    $(document).off('keydown.f5').on('keydown.f5', function(e){ e.preventDefault(); insertBlockByShortcode(5); });
                    $(document).off('keydown.f6').on('keydown.f6', function(e){ e.preventDefault(); insertBlockByShortcode(6); });
                    $(document).off('keydown.f7').on('keydown.f7', function(e){ e.preventDefault(); insertBlockByShortcode(7); });
                    $(document).off('keydown.f8').on('keydown.f8', function(e){ e.preventDefault(); insertBlockByShortcode(8); });
                    $(document).off('keydown.f9').on('keydown.f9', function(e){ e.preventDefault(); insertBlockByShortcode(9); });
                });
            }
            show_popup();
        }
            
        
        var alt = null;
        function setAktiv(obj){
            $(loadme).show();
            
            alt = $("#aktiv");
            $(alt).removeAttr("id");
            $(obj).attr("id", "aktiv");
            
            load_bloecke(); 
            
            
            var spaltenwahl = $(doc3O).find("div.spaltenwahl");            
            if($(spaltenwahl)[0]){
                var beforea = $(obj).prevAll("div.spalte").length; 
                var chooser = $(spaltenwahl).find("div.choose a");
                var sp_choosen = $(spaltenwahl).find("div.sp_choosen span");
                
                $(chooser).removeClass("aktiv");
                $(chooser).eq(beforea).addClass("aktiv");
                
                $(sp_choosen).html($(chooser).eq(beforea).data('titel'));
                
                $(chooser).not(".aktiv").off().on("click", function(){
                    var choosesp = $(doc3O).find('div.spalte_'+$(this).data('id'));
                    $(obj).find("#block_form").remove();
                    $(sp_choosen).html($(this).data('titel'));
                    setAktiv(choosesp);
                });
            }
            
            
            var mfa = $("#fn250 #mehrfachauswahl");
            var mfaA = $(mfa).find("a.mfa");
            var mfaAS = $(mfa).find("a.mfaS");
            
            $(mfa).fadeIn().find("a.mfaS").hide().off().on("click", function(){ 
                var form = $(obj).find("#block_form").serialize();
                var aufg = $(this).attr("rel");    
               
                if(aufg == 'mfa_del'){
                    sfrage_show('Wollen Sie wirklich alle markierten Inhalts-Elemente entfernen?');
                    $("#sfrage button:last").on("click", do_mfa_task);
                } else {
                    do_mfa_task();
                }
                
                function do_mfa_task(){
                    $(loadme).show();
                    
                    $.post('inc_documents.php', {
                        index : 'n268',
                        form : form,
                        id : ausgewaehltes_dokument,
                        a: aufg
                    }, function(data){
                        logincheck(data);
                        close_mfa();
                        load_bloecke(); 
                        
                        $(loadme).hide();
                        
                        if(aufg == 'mfa_ablage'){
                            show_popup();
                        }
                    });
                }
            });
            
            $(mfaA).off().on("click", function(){
                if($(mfaAS).css("display") == "none"){
                    $(mfaAS).fadeIn();
                    $(this).html("Mehrfachauswahl &amp; Optionen schlie&szlig;en").css('background', '#fff url(images/rpfeil_oben.png) no-repeat 2px center');
                    
                    $("#docPop div.blockz").sortable("disable");
                    $(obj).sortable("disable");
                    
                    $(obj).find("div.blockO input").fadeIn();
                } else {
                    close_mfa();
                }
            });
            
            function close_mfa(){
                $(mfaAS).fadeOut();
                $(mfaA).html("Mehrfachauswahl &amp; Optionen &ouml;ffnen").css('background', '#fff url(images/rpfeil_unten.png) no-repeat 2px center');
                
                $("#docPop div.blockz").sortable("enable");
                $(obj).sortable("enable");
                
                $(obj).find("div.blockO input").fadeOut();
            }
            close_mfa();
        }
        
        function load_bloecke(){
            var aktivO = $("#fn250 #aktiv");
            $(loadme).show();
            
            $.get('inc_documents.php', {
                index : 'n255',
                aktiv : $(aktivO).find("input:first").val(),
                id : ausgewaehltes_dokument,
                spaltenr: $("#aktiv").prevAll("div.spalte").length
           }, function(data){ logincheck(data); 
                $(aktivO).html(data); 
                zurfreigabe();
                
                calcDatePicker(aktivO);
                
                $(loadme).hide();
                
                var doks = $(aktivO).find("div.block");
            
                function calcHeight(){      
                    var max_height = 0;
                    $(doks).each(function(){ 
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
                
                if(!$("#fn250 #is_dklasse")[0]){ // Bloecke Sortieren "Normal"
                    $("#fn250 #aktiv").sortable({
                        connectWith: '#aktiv',
                        forcePlaceholderSize: true,
                        scroll: false,
                        items: 'div.block, .doc_inhalt_popup',
                        revert: true,
                        handle: 'div.drag',
                        axis: 'y',
                        receive: function(event, ui){ 
                            var nblock = ui.item.attr("id").split("_");
                            var copy = nblock[2];
                            var extb = ui.item.data('extension'); 
                            ui.item.attr("id", nblock[0]+"_"+nblock[1]);
                            
                            if(nblock[0] == "b"){  
                                $.get('inc_documents.php', {
                                    index : 'n256',
                                    aktiv : $("#aktiv input:first").val(),
                                    id : ausgewaehltes_dokument,
                                    block: nblock[1],
                                    copy: copy,
                                    sort: $("#aktiv").sortable("serialize"),
                                    extb: extb
                                }, function(data){ 
                                    logincheck(data); 
                                    if(data) alert(data);
                                    zurfreigabe();
                                    
                                    load_bloecke();
                                });
                            }
                        },
                        update: function(event, ui){ 
                            $.get('inc_documents.php', {
                                index : 'n257',
                                aktiv : $("#aktiv input:first").val(),
                                id : ausgewaehltes_dokument,
                                sort: $("#aktiv").sortable("serialize")
                            }, function(data){ logincheck(data);
                                if(data) alert(data);
                                zurfreigabe();
                                load_bloecke();
                            });
                        }
            		});
                } else if($("#fn250 .inhaltsbereich")[0]){ // Bloecke Sortieren "Dokumentenklassen Inhaltsbereiche"
                    $("#fn250 #aktiv .inhaltsbereich").each(function(){
                        var ib = $(this);
                        
                        $(ib).sortable({
                            connectWith: $(ib),
                            forcePlaceholderSize: true,
                            scroll: false,
                            items: 'div.block, .doc_inhalt_popup',
                            revert: true,
                            handle: 'div.drag',
                            axis: 'y',
                            receive: function(event, ui){ 
                                var nblock = ui.item.attr("id").split("_");
                                var copy = nblock[2];
                                var extb = ui.item.data('extension'); 
                                ui.item.attr("id", nblock[0]+"_"+nblock[1]);
                                
                                if(nblock[0] == "b"){  
                                    $.get('inc_documents.php', {
                                        index : 'n256_dk',
                                        id : ausgewaehltes_dokument,
                                        block: nblock[1],
                                        copy: copy,
                                        sort: $(ib).sortable("serialize"),
                                        ibid: $(ib).find("input.ib_id").val(),
                                        extb: extb
                                    }, function(data){ 
                                        logincheck(data); 
                                        zurfreigabe();
                                        console.log(data);
                                        
                                        load_bloecke();
                                    });
                                }
                            },
                            update: function(event, ui){ 
                                $.get('inc_documents.php', {
                                    index : 'n257_dk',
                                    aktiv : $("#aktiv input:first").val(),
                                    id : ausgewaehltes_dokument,
                                    sort: $(ib).sortable("serialize"),
                                    ibid: $(ib).find("input.ib_id").val()
                                }, function(data){ logincheck(data);
                                    zurfreigabe();
                                    load_bloecke();
                                });
                            }
                		});
                    });
                }
                
                //// Nur Werte editieren - Speichern und Verwerfen
                var wb_inhalte = new Array();
                var wertblock = $(aktivO).find("div.wertblock");
                var wertgruppe_openable = $(wertblock).filter(".openable");
                
                if($(wertblock)[0]){
                    $(wertgruppe_openable).each(function(){
                        rbutton($(this).find("a.rbutton"), $(this).find("tr.show_werte td"), 'öffnen', 'schließen');
                    });
                    
                    function getWBinhalte(){
                        $(wertblock).find("input[type=text], select").each(function(){
                            var tid = $(this).attr("id");
                            wb_inhalte[tid] = $(this).val();
                        });
                        
                        $(wertblock).find("input[type=checkbox]").each(function(){
                            var tid = $(this).data("id");
                            var nr = $(this).data("nr");
                            
                            if(!wb_inhalte[tid])
                                wb_inhalte[tid] = new Array();
                            
                            wb_inhalte[tid][nr] = ($(this).is(":checked")?true:false);
                        });
                    }
                    getWBinhalte();
                    
                    $(wertblock).find("input[type=text]").off("keyup").on("keyup", function(e){
                        var tid = $(this).attr("id");
                        if(e.keyCode != 13 && $(this).val() != wb_inhalte[tid]) { 
                            $(this).parents("div.wertblock").children("div.speichern").show();
                        }
                    });
                    
                    $(wertblock).find("a.save").off("click").on("click", function(e){
                        e.preventDefault();
                        
                        var wb = $(this).parents("div.wertblock");
                        var wbform = $(wb).children("form");
                        
                        $(wb).find("div.speichern").hide();
                        getWBinhalte();
                        
                        $.post('inc_documents.php', {
                            index: 'n261wb',
                            id : ausgewaehltes_dokument,
                            html: $(wbform).serialize()
                        }, function(data){ logincheck(data);
                            zurfreigabe();  
                        }); 
                    });
                    
                    $(wertblock).find("input[type=text]").off("keypress").on("keypress", function(e){
                        if(e.keyCode == 13) {
                            $(this).parents("div.wertblock").find("a.save").trigger("click");
                            e.preventDefault(); 
                        }
                    });
                    
                    $(wertblock).find("input.datepicker").off("change").on("change", function(){
                        $(this).parents("div.wertblock").children("div.speichern").show();
                    });
                    
                    $(wertblock).find("select").off("change").on("change", function(){
                        $(this).parents("div.wertblock").children("div.speichern").show();
                    });
                    
                    $(wertblock).find("input[type=checkbox]").off("click").on("click", function(){
                        $(this).parents("div.wertblock").children("div.speichern").show();
                    });
                    
                    $(wertblock).find("a.verw").off("click").on("click", function(){
                        var wb = $(this).parents("div.wertblock");
                        $(wb).find("div.speichern").hide(); 
                         
                        $(wb).find("input[type=text], select").each(function(){
                            var tid = $(this).attr("id"); 
                            $(this).val(wb_inhalte[tid]);
                        });
                        
                        $(wb).find("input[type=checkbox]").each(function(){
                            var tid = $(this).data("id");
                            var nr = $(this).data("nr");
                            
                            $(this).attr('checked', (wb_inhalte[tid][nr]?true:false));
                        });
                    });
                }
                
                ///////// QUICK EDIT START
                $(aktivO).find("div.blockUE").off("click").on("click", function(event){ 
                    var alink = $(this).parents("div.block").find("a.bearbeiten");
                    $(alink).trigger("click");
                });
                
                $(aktivO).find("a.quickedit").off("click").on("click", function(event){ 
                    var aclick = $(this);
                    var bid = $(this).attr("rel");
                    var tblock = $(this).parents("div.block");
                    var u1 = $(tblock).find("div.blockU1");
                    var u2 = $(tblock).find("div.blockU2");
                    var ta = $(u2).find("textarea");
                    
                    var altes_html = $(u1).html();
                    var altes_htmlT = $(ta).val();
                    
                    if($(u2).css("display") == "none"){
                        $(aclick).html('Editor schlie&szlig;en');
                        
                        $(u1).hide();
                        $(u2).show();   
                        
                        $(aktivO).sortable("disable");
                        
                        var nckconfig = jQuery.extend(true, {}, ckconfig);
                        nckconfig.toolbar_Full = [
                            ['Bold', 'Italic', '-', 'RemoveFormat']
                        ];
                        
                        $(ta).ckeditor(function() { 
                            load_fck_button(this); 
                        }, nckconfig);
                        
                        $(u2).find("button.verw").off().on("click", function(e){    
                            e.preventDefault();
                            close_erease();
                        });   
                        
                        $(u2).find("button.save").off().on("click", function(e){    
                            e.preventDefault();
                            
                            $(aclick).html('Nur Text &Auml;ndern');
                            $(u2).hide();
                            $(u1).show().html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');    
                            
                            $.post('inc_documents.php', {
                                index: 'n261qe',
                                id : ausgewaehltes_dokument,
                                block : bid,
                                html: $(ta).val()
                            }, function(data){ logincheck(data);
                                zurfreigabe();  
                                $(aktivO).sortable("enable"); 
                                
                                $(u1).html(data); 
                            }); 
                        });
                    } else {
                        close_erease();
                    }
                    
                    function close_erease(){
                        $(aclick).html('Nur Text &Auml;ndern');
                        $(u2).hide();
                        $(u1).show().html(altes_html); 
                        $(ta).val(altes_htmlT);   
                        $(aktivO).sortable("enable"); 
                    }
                });
                /////// ENDE QUICK EDIT
                
                // //// Bloeck loeschen geht auch
                $(aktivO).find("a.del").off("click").on("click", function(e){
                    e.preventDefault();
                    var arel = $(this).attr("rel");
                    
                    sfrage_show('Wollen Sie dieses Inhaltselement wirklich entfernen?');
                    $("#sfrage button:last").on("click", function(){
                        $.get('inc_documents.php', {
                            index : 'n257_del',
                            aktiv : $("#aktiv input:first").val(),
                            id : ausgewaehltes_dokument,
                            ib: arel
                        }, function(data){ logincheck(data);
                            if(data) alert(data);
                            zurfreigabe();
                            load_bloecke();
                        });
                    });
                });
                
                // Ein Block wird geladen
                //////// Hier laden wir den Editor bzw die Module 
                $(aktivO).find("div.block a.edit").off("click").on("click", function(event){                    
                    event.stopPropagation();
                    
                    var ab2 = $(this).parents("div.block").attr("id").split("_");
                    aktiv_block = ab2[1];
                    aktiv_type_dk = ab2[2]; 
                    var realname = $(this).parents("div.block").data('name');
                    var ext_block = $(this).parents("div.block").data('ext_block');
                    
                    var index = "n260";
                    var value = "inc_documents"; 
                    clicked = this;
                    
                    fenster({
                        id: 'n260',
                        blackscreen: '',
                        width: 680,
                        cb: function(neww, inhalt){
                    
                            var ibid = 0;
                            var blockindex = 0;
                            if($(clicked).parents(".inhaltsbereich")[0]){
                                ibid = $(clicked).parents(".inhaltsbereich").find("input.ib_id").val();
                                blockindex = $(clicked).parents("div.block").find("input.block_index").val();
                            }
                            
                            block_inhalt();
                    
                            function block_inhalt(){
                                $.get(value+'.php', {
                                    index : 'n260',
                                    id : ausgewaehltes_dokument,
                                    block : aktiv_block,
                                    type: aktiv_type_dk,
                                    ibid: ibid,
                                    blockindex: blockindex,
                                    dkdatei: $("#aktiv input[name=dk_datei]").val(),
                                    realname: realname,
                                    ext_block: ext_block
                                }, function(data){ 
                                    logincheck(data); 
                                    $(inhalt).html(data);
                                    save_button(neww);
                                    
                                    zurfreigabe();
                                    
                                    var kopfleiste = $(neww).find("div.kopfleiste");
                                    var close = $(kopfleiste).find("p.close");
                                    var drag = $(kopfleiste).find("p.move");
                                    $(drag).css('width', 'auto');
                                    
                                    if($(inhalt).find("#teaser_inhalt")[0]){
                                        $(neww).width(920);
                                    } else if($(inhalt).find("#extension_block")[0]){
                                        var extension = true;
                                        var extension_block = $(inhalt).find("#extension_block");
                                        var nwidth = parseInt($(extension_block).data('width'));
                                        var extjscallback = $(extension_block).data('jscallback');
                                        var extcsscallback = $(extension_block).data('csscallback');
                                        
                                        if(nwidth > 0)
                                            $(neww).width(nwidth);
                                    } else if($(inhalt).find("#struk_teaser_more")[0]){
                                        $(neww).width(660);
                                    } else if($(inhalt).find("#formular")[0]){
                                        $(neww).width(880);
                                    } else if($(inhalt).find("#listen_form")[0]){
                                        $(neww).width(810);
                                    } else if($(inhalt).find("#ctabelle")[0]){
                                        $(neww).width(970);
                                    } else if($(inhalt).find("#dreference")[0]){
                                        $(neww).width(740);
                                    } else if($(inhalt).find("#relationsform")[0]){
                                        $(neww).width(740);
                                    }  else if($(inhalt).find("#editor textarea.text_15")[0]){
                                        $(neww).width(900);
                                    } else {
                                        $(neww).width(680);
                                    } 
                                    
                                    $(drag).css("width", ($(kopfleiste).width() - 71) + 'px');
                                    $(neww).css('left', ($("#main").width() / 2 - $(neww).width() / 2) + 'px');
                                    
                                    if(!extension){
                                        var textO = $(neww).find("#text");
                                        
                                        var my_ckeditor = null;
                                        var nckconfig = ckconfig;
                                        var edita = $("#editor textarea");
                                        
                                        if($(edita).hasClass('text_10'))
                                            nckconfig.height = '86px';
                                        else if($(edita).hasClass('text_11'))
                                            nckconfig.height = '126px';
                                        else if($(edita).hasClass('text_12'))
                                            nckconfig.height = '126px';
                                        else if($(edita).hasClass('text_15'))
                                            nckconfig.height = '400px';
                                        else if($(edita).hasClass('text_18'))
                                            nckconfig.height = '250px';
                                        else if($(edita).hasClass('text_20'))
                                            nckconfig.height = '300px';
                                        
                                        if($(textO).attr("class") != "text_24" && $(textO).attr("class") != "nur_wert" && $(textO)[0]){
                                            $(textO).ckeditor(function() { 
                                                my_ckeditor = $(textO).ckeditorGet();
                                                load_fck_button(this); 
                                                
                                                set_button(neww);
                                                
                                                // split elements
                                                if($(neww).find("div.lots_of_p")[0]){ 
                                                    $(neww).find("div.lots_of_p a").off().on("click", function(){
                                                        $(neww).find("a#e_opt_aufteilen").trigger("click"); 
                                                    });
                                                    
                                                    var oldval = '';
                                                    
                                                    var splitTimer = setInterval(function(){ 
                                                        if(!$(textO)[0] || my_ckeditor == null){
                                                            clearInterval(splitTimer);
                                                            return false;
                                                        } 
                                                        
                                                        if(oldval != $(textO).val()){ 
                                                            $.post('inc_documents.php', {
                                                                index: 'n260_split_check',
                                                                html: $(textO).val()
                                                            }, function(data){
                                                                if(data == 'true')
                                                                    $(neww).find("div.lots_of_p").slideDown();
                                                                else
                                                                    $(neww).find("div.lots_of_p").slideUp(); 
                                                            });
                                                        }
                                                        
                                                        oldval = $(textO).val();
                                                    }, 5000);
                                                }
                                            }, nckconfig); 
                                        }
                                    } 
                                    
                                    $(close).off("click").on("click", function(e){
                                        if($(textO)[0] && my_ckeditor != null)
                                            my_ckeditor.destroy();
                                            
                                        $(neww).hide("slide", {}, 300, function(){  
                                            $(this).remove();
                                            
                                            $('#blackscreen').remove();
                                            $("#footer a").css("z-index", "9000");
                                            $("#navigationO").css("z-index", "9000");
                                            $("#widget-dashboard").css("z-index", "9500");
                                            $("body").removeClass("mit_vorschau");
                                        });
                                    });
                                    
                                    var sb = $("#fn260 div.box_save"); 
                                    
                                    $(sb).find("input:first").off().on("click", function(){
                                        $("#fn260 p.close").trigger("click");
                                    });
                                    
                                    $(sb).find("input:last").off().on("click", function(){ 
                                        var selfbutton = $(this);
                                        $(selfbutton).attr("disabled", "disabled");
                
                                        if($(textO)[0]) {
                                            var html = $(textO).val(); 
                                        }
                                        
                                        $.post('inc_documents.php', {
                                            index: 'n261',
                                            id : ausgewaehltes_dokument,
                                            block : aktiv_block,
                                            ibid: ibid,
                                            blockindex: blockindex,
                                            html: html,
                                            all: $("#add_pic_form").serialize(),
                                            type: aktiv_type_dk
                                        }, function(data){ 
                                            logincheck(data);
                                            $(selfbutton).removeAttr("disabled");
                                            
                                            zurfreigabe();
                                            load_bloecke(); 
                                        }); 
                                        
                                        $(neww).find("p.close").trigger("click");
                                    });
                                    
                                    ///// BILD WaeHLEN ///
                                    if($(inhalt).find("div.text_bild")[0]){  
                                            
                                        var cb = $(inhalt).find("div.choosebild1");
                                        var cb2 = $(inhalt).find("div.choosebild2");
                                        var bild_verlinken = $(inhalt).find("div.bild_verlinken");
                                        var preview_picture = $("#preview_picture");
                                        var pic_delete = $(inhalt).find("a.del-link");
                                        
                                        $(inhalt).find("#textbild_no").off("click").on("click", function(){
                                            if($(cb).css("display") == "block") 
                                                $(cb).slideUp();
                                            $(preview_picture).hide();
                                            $(bild_verlinken).hide();
                                        });
                                        $(inhalt).find("#textbild_yes").off("click").on("click", function(){
                                            if($(cb).css("display") == "none")
                                                $(cb).slideDown();
                                            if($(cb2).css("display") == "block")
                                                $(cb2).slideUp();
                                            $(preview_picture).show();
                                            $(bild_verlinken).show();
                                        });
                                        $(inhalt).find("#textbild_extern").off("click").on("click", function(){
                                            if($(cb2).css("display") == "none")
                                                $(cb2).slideDown();
                                            if($(cb).css("display") == "block")
                                                $(cb).slideUp();
                                            $(preview_picture).show();
                                            $(bild_verlinken).show();
                                        });
                                        
                                        $(inhalt).find("#bild_extern").off("change keyup").on("change keyup", function(){
                                            $("#preview_picture").attr("src", $(this).val());
                                        });

                                        $(pic_delete).off("click").on("click", function(e){
                                            e.preventDefault();

                                            $(inhalt).find("#ins_bild_id").val(0);
                                            $(inhalt).find("#ins_bild_titel").html('');

                                            $(inhalt).find("span.bildgr").html('');

                                            $(inhalt).find("#preview_picture").hide().attr("src", "");
                                            $(inhalt).find("button.edit_current_pic").hide().data('file', 0);

                                            $(pic_delete).hide();

                                            zurfreigabe();
                                        });
                                        
                                        // Bild verlinken
                                        $(inhalt).find("#piclinkit").off("click").on("click", function(){
                                            var thep = $(this).siblings("p");
                                            
                                            if($(this).is(":checked")){
                                                $(thep).slideDown();
                                            } else {
                                                $(thep).slideUp();
                                            }
                                        });
                                        
                                        $(inhalt).find("#linkoptionen").off("click").on("click", function(e){
                                            e.preventDefault();
                                            
                                            fck_link('', '', true);
                                        });
                                        ///
                                        
                                        $(inhalt).find("select.bildwts").off("change").on("change", function(){
                                            var wt = $(this);
                                            var elt = $(this).parents("table:first");
                                            
                                            if($(wt).val() == 1){
                                                $(elt).find("input.bild_h").val('0').hide();
                                                $(elt).find("input.bild_w").val('100').show();
                                                $(elt).find("td.t2 span").show();
                                            } else if($(wt).val() == 2){
                                                $(elt).find("input.bild_h, input.bild_w").hide();
                                                $(elt).find("td.t2 span").hide();
                                            } else {
                                                $(elt).find("input.bild_h, input.bild_w").show();
                                                $(elt).find("td.t2 span").show();
                                            }
                                        });
                                        
                                        // BILDAUSWAHL LADEN    
                                        $(cb).find("button#getoldpic").off("click").on("click", function(e){ 
                                            e.preventDefault();
                                            
                                            bildauswahl(this, false);

                                            $(pic_delete).show();
                                        });
                                        
                                        // Neues Bild hochladen   
                                        var old_beschr = '';
                                        var nbutton = $(cb).find("button#getnewpic");
                                        
                                        if($(nbutton)[0]){
                                            $(nbutton).off().on("click", function(e){
                                                e.preventDefault();
                                                
                                                startUpload({
                                                    dir: 0,
                                                    images: true,
                                                    blackscreen: '2',
                                                    hide_edit: true,
                                                    limit: 1,
                                                    refresh: function(neww, data){
                                                        var file = data[0];
                                                        if(!file)
                                                            return false;
                                                        
                                                        $(inhalt).find("#ins_bild_id").val(file.id);
                                                        $(inhalt).find("#ins_bild_titel").html(file.name);

                                                        $(pic_delete).show();
                                                        
                                                        var text_bild = $(inhalt).find("div.text_bild");
                                                        
                                                        $(text_bild).find("span.bildgr").html('Original: '+file.width+'x'+file.height+'px');
                                                        
                                                        if($(text_bild).find("input.bild_h").css("display") != "none"){
                                                            $(text_bild).find("input.bild_w").not(":disabled").val(file.width);
                                                            $(text_bild).find("input.bild_h").not(":disabled").val(file.height);
                                                        }
                                                        
                                                        $(inhalt).find("#preview_picture").attr("src", file.thumbnail_url_160).fadeIn();
                                                        $(inhalt).find("button.edit_current_pic").show().data('file', file.id);
                                                        
                                                        zurfreigabe();
                                                        $(neww).find("p.close").trigger("click");
                                                    }
                                                });
                                            }); 
                                        }
                                        
                                        // Bild editieren
                                        var ebutton = $(cb).find("button.edit_current_pic");
                                        
                                        if($(ebutton)[0]){
                                            $(ebutton).off("click").on("click", function(e){
                                                e.preventDefault();
                                                
                                                var the_file = $(this).data('file');
                                                
                                                startImageEdit({
                                                    blackscreen: '2',
                                                    file: the_file,
                                                    file_version: 0,
                                                    callback: function(){
                                                        var old_src = $(inhalt).find("#preview_picture").attr("src")+'?random='+Math.random();
                                                        $(inhalt).find("#preview_picture").attr("src", old_src);
                                                    }
                                                });
                                            });
                                        }
                                        
                                    }
                                    /// BILD WaeHLEN ENDE ///
                                    
                                    // LISTEN //
                                    if($(inhalt).find("#listen_form")[0]){  
                                            
                                        var lf = $(inhalt).find("#listen_form"); 
                                        var editoren = new Array();
                                        
                                        var listen_ckconfig = ckconfig;
                                        listen_ckconfig.height = '80px';
                                        
                                        $(lf).find("textarea").each(function(){
                                            var metextarea = $(this);
                                            
                                            $(this).ckeditor(function() { 
                                                editoren.push($(metextarea).ckeditorGet());
                                                load_fck_button(this); 
                                                set_button(neww);
                                            }, listen_ckconfig); 
                                        });
                                        
                                        function initDel(){
                                            $(lf).find("div.liste a.del").off("click").on("click", function(){
                                                var klick = $(this);
                                                    
                                                sfrage_show('Wollen Sie diesen Listenpunkt wirklich entfernen?');
                                                $("#sfrage button:last").on("click", function(){
                                                    $(klick).parents("div.liste").remove();
                                                    initSort();
                                                });
                                            });
                                        }
                                        initDel();
                                        
                                        function initSort(){
                                            $(lf).sortable({
                                                items: 'div.liste',
                                                containment: 'parent',
                                                handle: 'p.move',
                                                start: function(e, ui){
                                                    var self = ui.item;
                                                    $(self).find("textarea").ckeditorGet().destroy();
                                                    $(self).find("div.area").hide();
                                                },
                                                stop: function(e, ui){
                                                    var self = ui.item;
                                                    $(self).find("div.area").show();
                                                    $(self).find("textarea").ckeditor(function() { 
                                                        editoren.push($(self).find("textarea").ckeditorGet());
                                                        load_fck_button(this); 
                                                    }, ckconfig);
                                                } 
                                    		});
                                        }
                                        initSort();
                                        
                                        $(lf).find("button.new").off("click").on("click", function(e){
                                            e.preventDefault();
                                            
                                            var newid = 'liste_'+uniqid();
                                            $(this).before('<div class="liste"><div class="area"><textarea name="liste[]" id="'+newid+'"></textarea></div><p class="move"></p><a class="del">Listenpunkt entfernen</a></div>');
                                            
                                            $("#"+newid).ckeditor(function() { 
                                                editoren.push($("#"+newid).ckeditorGet());
                                                load_fck_button(this); 
                                                set_button(neww);
                                            }, ckconfig); 
                                            
                                            initDel();
                                            initSort();
                                        });
                                        
                                        var sb = $("#fn260 div.box_save").show();                                
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
                                            
                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $("#fn260").find("#block_ibid").val(),
                                                blockindex: $("#fn260").find("#blockindex").val(),
                                                f: $(lf).serialize(),
                                                liste: 'true'
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke(); 
                                                $(selfbutton).removeAttr("disabled");
                                                
                                                $("#fn260 p.close").trigger("click");
                                            }); 
                                        });
                                    }
                                    /// LISTEN ENDE //
                                    
                                    
                                    // TABELLEN //
                                    if($(inhalt).find("#ctabelle")[0]){  
                                        var ta = $(inhalt).find("#ctabelle"); 
                                        var ttabs = $(ta).find("#tabellentabs"); 
                                        
                                        var inp_x = $(ta).children("input[name=x]");
                                        var inp_y = $(ta).children("input[name=y]");
                                        var inp_value = $(ta).children("input[name=value]");
                                        
                                        // Layout der Tabelle
                                        function tabellen_layout(){
                                            var lay = $(ttabs).find("#ctabelle_layout");
                                            $(lay).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                                            
                                            $.post('inc_documents.php', {
                                                index : 'n260_table_layout',
                                                f: $(ta).serialize()
                                            }, function(data){ logincheck(data);
                                                $(lay).html(data);
                                                set_button($("#fn260"));
                                                
                                                var zellen = $(lay).find("div.auswahl span");
                                                var hx = $(lay).find("h3.spalten span");
                                                var hy = $(lay).find("h3.zeilen span");
                                                
                                                $(zellen).off().on("click", function(){
                                                    var mx = $(this).data('x');
                                                    var my = $(this).data('y');
                                                    
                                                    $(hx).html(mx);
                                                    $(hy).html(my);
                                                    
                                                    $(inp_x).val(mx);
                                                    $(inp_y).val(my);
                                                    
                                                    $(zellen).removeClass("aktiv").filter(function() { 
                                                        return ($(this).data("x") <= mx && $(this).data("y") <= my?true:false)
                                                    }).addClass("aktiv");
                                                });
                                            });
                                        }
                                        
                                        // Inhalt der Tabelle
                                        function tabellen_inhalt(){
                                            var inh = $(ttabs).find("#ctabelle_inhalt");
                                            $(inh).html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                                            
                                            $.post('inc_documents.php', {
                                                index : 'n260_table_content',
                                                f: $(ta).serialize()
                                            }, function(data){ logincheck(data);
                                                $(inh).html(data);
                                                set_button($("#fn260"));
                                                
                                                var zellen = $(inh).find("td");
                                                
                                                $(zellen).off().on("click", function(){
                                                    var mx = $(this).data('x');
                                                    var my = $(this).data('y');
                                                    
                                                    fenster({
                                                        id: 'n260_tabelle',
                                                        width: 680,
                                                        blackscreen: '2',
                                                        cb: function(nwin, ninhalt){
                                                            $.post('inc_documents.php', {
                                                                index : 'n260_table_editor',
                                                                f: $(ta).serialize(),
                                                                x: mx,
                                                                y: my
                                                            }, function(data){ logincheck(data);
                                                                $(ninhalt).html(data);
                                                                save_button(nwin);
                                                                
                                                                $(ninhalt).find("textarea").ckeditor(function() {         
                                                                    load_fck_button(this); 
                                                                    set_button(nwin);
                                                                }, ckconfig);
                                                                
                                                                $(ninhalt).find("div.box_save input.bs2").off().on("click", function(e){
                                                                    e.preventDefault();
                                                                    $(this).attr("disabled", "disabled");
                                                                    
                                                                    $.post('inc_documents.php', {
                                                                        index : 'n260_table_editor_save',
                                                                        f: $(ta).serialize(),
                                                                        x: mx,
                                                                        y: my,
                                                                        text: $(ninhalt).find("textarea").val()
                                                                    }, function(data){ logincheck(data);
                                                                        if(data)
                                                                            $(inp_value).val(data);
                                                                          
                                                                        tabellen_inhalt();  
                                                                        $(nwin).find("p.close").trigger("click");
                                                                    });
                                                                });
                                                            });
                                                        } 
                                                    });
                                                });
                                            });
                                        }
                                        
                                        // Tabellen-Tabs initialisieren
                                        var alr_loaded = ($(inp_x).val() > 0?true:false);
                                        
                                        $(ttabs).tabs({ 
                                            tabTemplate: '<li><a href="#{href}">#{label}</a></li>',
                                            selected: (alr_loaded?1:0),
                                            show: function(event, ui){ 
                                                var tabed = ui.panel; 
                                                var auswahl = $(tabed).data('kat');
                                                
                                                $(ttabs).find("div.ctab").html('');
                                                
                                                if(auswahl == 'layout')
                                                    tabellen_layout();
                                                if(auswahl == 'inhalt')
                                                    tabellen_inhalt();
                                            },
                                            create: function(){
                                                $(ttabs).children("#tabellentabs_navi").css({
                                                    "height": "auto",
                                                    "overflow": "inherit"
                                                });
                                            }
                                        }); 
                                        
                                        // Tabelle speichern
                                        var sb = $(inhalt).find("div.box_save").show();                                
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
                                            
                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(ta).serialize(),
                                                a2db: 'true'
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke(); 
                                                $(selfbutton).removeAttr("disabled");
                                                
                                                $("#fn260 p.close").trigger("click");
                                            }); 
                                        });
                                    }                                
                                    // TABELLEN ENDE ///
                                                                
                                    
                                    ///// GALERIE ///
                                    if($(inhalt).find("#dgalerie")[0]){  
                                            
                                        var cb = $(inhalt).find("#dgalerie"); 
                                        
                                        $(cb).find("a.bild_del").off().on("click", function(){
                                            var klick = $(this);
                                                
                                            sfrage_show('Wollen Sie dieses Element wirklich aus der Galerie entfernen?');
                                            $("#sfrage button:last").on("click", function(){
                                                $.get('inc_documents.php', {
                                                    index : 'n273',
                                                    pid: $(klick).attr("rel"),
                                                    block: $("#fn260").find("#block_id").val(),
                                                    ibid: $("#fn260").find("#block_ibid").val(),
                                                    blockindex: $("#fn260").find("#blockindex").val(),
                                                    id : ausgewaehltes_dokument
                                                }, function(data){ logincheck(data);
                                                    zurfreigabe();
                                                    block_inhalt();
                                                });
                                            });
                                        });
                                        
                                        
                                        var oparent = $(cb).offset().top;
                                        
                                        $(cb).sortable({
                                            items: 'table.bild',
                                            axis: 'y',
                                            containment: $("#fn260"),
                                            handle: 'td.mover'
                                		});
                                        
                                        var sb = $("#fn260 div.box_save").show();
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
                                            
                                            $.post('inc_documents.php', {
                                                index : 'n274',
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                id : ausgewaehltes_dokument,
                                                sort: $("#dgalerie_form").serialize()
                                            }, function(data){ logincheck(data); 
                                                $(selfbutton).removeAttr("disabled");
                                                
                                                zurfreigabe();
                                                block_inhalt();
                                                load_bloecke();
                                                $("#fn260 p.close").trigger("click");
                                            }); 
                                        });
                                        
                                        // BILDAUSWAHL LADEN    
                                        $(cb).find("button.choose").off("click").on("click", function(e){      
                                            e.preventDefault();
                                            
                                            bildauswahl(this, true, block_inhalt);
                                        });
                                        
                                        // upload
                                        var nbutton = $(cb).find("button.upload");
                                        
                                        if($(nbutton)[0]){
                                            $(nbutton).off().on("click", function(e){
                                                e.preventDefault();
                                                
                                                startUpload({
                                                    dir: 0,
                                                    images: true,
                                                    blackscreen: '2',
                                                    hide_edit: true,
                                                    limit: 999,
                                                    refresh: function(neww, data){ console.log(data);
                                                        if(data.length > 0){ 
                                                            var scho = '';
                                                            for(var q = 0; q < data.length; q++){
                                                                if(data[q].status != 'finished')
                                                                    continue;
                                                                    
                                                                scho += (q == 0?'':'_')+data[q].id;
                                                            }
                                                            
                                                            $.get('inc_documents.php', {
                                                                index : 'n272',
                                                                pid: scho,
                                                                block: $("#fn260").find("#block_id").val(),
                                                                ibid: $("#fn260").find("#block_ibid").val(),
                                                                blockindex: $("#fn260").find("#blockindex").val(),
                                                                id : ausgewaehltes_dokument
                                                            }, function(dataT){
                                                                $(neww).find("p.close").trigger("click");
                                                                block_inhalt();
                                                            });
                                                        }
                                                    }
                                                });
                                            });
                                        }
                                    }
                                    /// GALERIE ENDE ///
                                    
                                    /// TEASER START
                                    if($(inhalt).find("div.teaser_inhalt")[0]){     
                                        
                                        // Teaser Einstellungen
                                        $(inhalt).find("td.dk_filterO input.vonbis").off("click").on("click", function(){
                                            var si = $(this).parent("p").find("input[type=text]");
                                            
                                            if($(this).is(":checked")){
                                                $(si).removeAttr("disabled").val('');
                                            } else {
                                                $(si).attr("disabled", "disabled").val('');
                                            }
                                        });
                                        
                                        var tkats = $("#struk_teaser_kats");
                                        var tkatsa = $(tkats).find("a.rbutton");
                                        var tkatsc = $(tkats).find("div.kats");
                                        rbutton(tkatsa, tkatsc, 'anzeigen', 'ausblenden');
                                        
                                        $(inhalt).find("div.teaseAR button").off().on("click", function(e){
                                            e.preventDefault();
                                            
                                            fenster({
                                                id: 'n130',
                                                blackscreen: '2',
                                                width: 750,
                                                cb: function(nwin, ninhalt){
                                                     $.get('inc_structure.php', {
                                                        index : 'n120',
                                                        just_select: true
                                                    }, function(data){ logincheck(data); 
                                                        
                                                        $(ninhalt).html(data);
                                                        save_button(nwin);
                                                        
                                                        struktur_start({
                                                            just_select: true,
                                                            select_cb: function(zid, ztitel, zelement){
                                                                $(inhalt).find("#aktueller_teaser").html(ztitel);
                                                                $(inhalt).find("#t_strele").val(zid);
                                                                
                                                                $(inhalt).find("div.box").show();
                                                                
                                                                teaser_inhalt();
                                                                
                                                                $(nwin).find("p.close").trigger("click");
                                                            }
                                                        });
                                                    });
                                                }
                                            });
                                        });
                                                    
                                        function teaser_inhalt(){
                                            var teasertype = $(inhalt).find("#teasertype").val();
                                            
                                            if(teasertype == 1){
                                                if($(inhalt).find("#t_strele").val() > 0)
                                                    $(inhalt).find("#teaser_inhalt").fadeIn(150);
                                            } else {
                                                $(inhalt).find(".teaseA2 input[name=auflistung]").off("click").on("click", function(){
                                                    if($(this).val() == '2'){
                                                        $(inhalt).find("#struk_teaser_more").slideUp(299);
                                                        $(inhalt).find("#struk_teaser_more2").slideDown(300, function(){
                                                            set_button($("#fn260"));
                                                        });
                                                    } else if($(this).val() == '1'){
                                                        $(inhalt).find("#struk_teaser_more2").slideUp(299);
                                                        $(inhalt).find("#struk_teaser_more").slideDown(300, function(){
                                                            set_button($("#fn260"));
                                                        }).find("div.t_blockR div.t_optA input").attr("checked", true);
                                                    } else {
                                                        $(inhalt).find("#struk_teaser_more").slideUp(299, function(){
                                                            set_button($("#fn260"));
                                                        });
                                                        $(inhalt).find("#struk_teaser_more2").slideUp(300, function(){
                                                            set_button($("#fn260"));
                                                        });
                                                    }
                                                });
                                            }
                                            
                                            $("#struk_teaser_more .t_blockLB input").on("change click", function(){
                                                var inp = $(this);
                                                var par = $(inp).parents(".t_block").children(".t_blockR");
                                                
                                                if($(inp).is(":checked")){
                                                    $(par).fadeOut();
                                                } else {
                                                    $(par).fadeIn();
                                                }
                                            });
                                            
                                            
                                            var trss = $(inhalt).find("#teaser_rss");
                                            $(trss).find("#i_is_rss").off().on("click", function(){
                                                var tshow = $(trss).find("div.extra");
                                                
                                                if($(this).is(":checked")){
                                                    $(tshow).show();
                                                } else {
                                                    $(tshow).hide();                          
                                                }
                                                
                                                set_button($("#fn260"));
                                            });
                                            
                                                
                                            var strinh = $(inhalt).find("#struk_teaser_inhalt");
                                            var strinhele = $(strinh).find("div.elemente");
                                            var strinhst = $(strinh).find("div.select_type");
                                            
                                            $(strinhst).find("input").off("click").on("click", function(){
                                                get_teaser_str_elements();
                                            });
                                            
                                            function get_teaser_str_elements(){
                                                $.get('inc_documents.php', {
                                                    index : 'n266',
                                                    a: teasertype,
                                                    id: $(inhalt).find("#block_id").val(),
                                                    element: $(inhalt).find("#t_strele").val(),
                                                    st: $(strinhst).find("input:checked").val()
                                                }, function(data){
                                                    logincheck(data); 
                                                    $(strinhele).html(data);
                                                    
                                                    if(!$(strinhele).find("div.stt")[0] && $(inhalt).find("#t_strele").val() > 0){
                                                        sfrage_show('Das gewählte Strukturelement verfügt über keinerlei Kindelemente. Möchten Sie ein anderes Strukturelement wählen?');
                                                        $("#sfrage button:last").on("click", function(){
                                                            $(inhalt).find("div.teaseAR button").trigger("click");
                                                        });
                                                    }
                                                    
                                                    
                                                    var sb = $("#fn260 div.box_save");
                                                    set_button($("#fn260"), sb); 
                                                       
                                                    $(sb).find("input:last").off().on("click", function(){
                                                        var selfbutton = $(this);
                                                        $(selfbutton).attr("disabled", "disabled");
                                                        
                                                        $.post('inc_documents.php', {
                                                            index : 'n267',
                                                            id : ausgewaehltes_dokument,
                                                            block: $(inhalt).find("#block_id").val(),
                                                            ibid: $(inhalt).find("#block_ibid").val(),
                                                            blockindex: $(inhalt).find("#blockindex").val(),
                                                            f: $(inhalt).find("#teaser_form").serialize()
                                                        }, function(data){
                                                            logincheck(data);
                                                            $(selfbutton).removeAttr("disabled");
                                                            load_bloecke(); 
                                                            
                                                            zurfreigabe();
                                                            $("#fn260 p.close").trigger("click");
                                                        });
                                                    });
                                                    
                                                    $(inhalt).find(".teaser_inhalt input.teaser_auszug").off("click").on("click", function(){
                                                        if($(this).val() == '2'){
                                                            $(this).parents(".t_optB").find("table").fadeIn(300, function(){
                                                                set_button($("#fn260"));
                                                            });
                                                        } else {
                                                            $(this).parents(".t_optB").find("table").fadeOut(300, function(){
                                                                set_button($("#fn260"));
                                                            });
                                                        }
                                                    });
                                                });
                                            }
                                            get_teaser_str_elements();
                                        }
                                        teaser_inhalt();            
                                    }
                                    /// TEASER ENDE
                                    
                                    /// SITEMAP START
                                    if($(inhalt).find("#n_sitemap")[0]){ 
                                        
                                        function sitemap_laden(){ 
                                            var hide = ($("#sitemap_form #isitemap2").is(":checked")?'true':'false');
                                            
                                            $.get('inc_documents.php', {
                                                index: 'n265sm',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                r: $("#sitemap_form #sm_simulate").val(),
                                                hide: hide
                                            }, function(data){ logincheck(data); 
                                                $(inhalt).find("#n_sitemap").html(data);
                                            });    
                                        }
                                        sitemap_laden();
                                        
                                        $("#sitemap_form #sm_simulate").off().on("change", sitemap_laden);
                                        $("#sitemap_form input").off().on("change", sitemap_laden);
                                        
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
                                            
                                            $.post('inc_documents.php', {
                                                index : 'n261',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $("#fn260").find("#block_ibid").val(),
                                                blockindex: $("#fn260").find("#blockindex").val(),
                                                sm: $(inhalt).find("#sitemap_form").serialize()
                                            }, function(data){
                                                $(selfbutton).removeAttr("disabled");
                                                
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke(); 
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });
                                    }
                                    /// SITEMAP ENDE
                                    
                                    /// FORMULAR START
                                    if($(inhalt).find("#formular")[0]){
                                        
                                        var formularO = $(inhalt).find("#formular");
                                        
                                        $(inhalt).find("#newf_block button").off().on("click", function(e){
                                            e.preventDefault();
                                            var buttonclass = $(this).attr("class");
                                            var index = "n269";
                                                  
                                            fenster({
                                                id: 'n269',
                                                blackscreen: '2',
                                                width: 540,
                                                cb: function(neww, inhalt){
                                                    $.post('inc_documents.php', {
                                                        index : "n269",
                                                        a: 0,
                                                        type: buttonclass
                                                    }, function(data){ logincheck(data);
                                                        $(inhalt).html(data);
                                                        save_button(neww);
                                                        
                                                        $(inhalt).find("input.name").focus();
                                                        
                                                        if(buttonclass != 'inputR'){
                                                            $(inhalt).find("textarea.text").ckeditor(function() { 
                                                                load_fck_button(this); 
                                                                set_button(neww);
                                                            }, ckconfig);
                                                        } else {
                                                            $(inhalt).find(".name").off("change keyup").on("change keyup", function(){
                                                                $(inhalt).find(".box:last").slideDown();
                                                            });
                                                        }
                                                        
                                                        $(inhalt).find("button").off().on("click", function(e){
                                                            e.preventDefault();
                                                            var text = (buttonclass != 'inputR'?$(inhalt).find("textarea.text").val():$(inhalt).find(".name").val());
                                                            
                                                            $.post(value+'.php', {
                                                                index : index,
                                                                a: 1,
                                                                type: $(this).attr("class"),
                                                                ort: $("#neuesformelement_type").val(),
                                                                name: text
                                                            }, function(data){ logincheck(data);
                                                                $(formularO).append(data);
                                                                $("#fn269 p.close").trigger("click");
                                                                formular_bloecke();
                                                                
                                                                set_button($("#fn260"));
                                                            });
                                                            
                                                            $(inhalt).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                                                        });
                                                    });
                                                }
                                            });
                                        });
                                        
                                        
                                        function formular_bloecke(){
                                            var sblockO = $(formularO).find("div.sblock");
                                            
                                            var new_del = new Array();
                                            var count_del = 0;
                                            // Leere Bloecke entfernen
                                            $(sblockO).each(function(){
                                                if(!$(this).find(".feld")[0])
                                                    new_del.push($(this));
                                            });
                                            
                                            // Leeren Block an Anfang und Ende stellen
                                            $.post('inc_documents.php', {
                                                index : 'n269',
                                                a: 2
                                            }, function(data){ logincheck(data);
                                                $(formularO).append(data);
                                                del_and_set();
                                            });
                                            $.post('inc_documents.php', {
                                                index : 'n269',
                                                a: 2
                                            }, function(data){ logincheck(data);
                                                $(formularO).prepend(data);
                                                del_and_set();
                                            });
                                            
                                            function del_and_set(){
                                                count_del ++;
                                                
                                                if(count_del == 2){
                                                    for(var x=0; x < new_del.length; x++)
                                                        new_del[x].remove();
                                                    
                                                    formular_sortierung();
                                                    formular_optionen();
                                                }
                                            }
                                            
                                            set_button($("#fn260"));
                                        }
                                        
                                        function formular_sortierung(){
                                            var sblockO = $(formularO).find("div.sblock");
                                            
                                            // Bloecke sortierbar machen                                 
                                            $(sblockO).find("div.bR, div.bL").sortable({
                                                items: $(sblockO).find("div.feld"),
                                                placeholder: "ui-state-highlight",
                                                forcePlaceholderSize: true,
                                                connectWith: $(sblockO).find("div.bR, div.bL"),
                                                start: function(e, ui){
                                                    $(sblockO).find("div.bR, div.bL").addClass("mitRand");
                                                    
                                                    if(ui.item.find(".ftype").val() != 'string')
                                                        $(sblockO).find("div.bL").addClass("bLH"); 
                                                }, stop: function(e, ui){
                                                    $(sblockO).find("div.bR, div.bL").removeClass("mitRand");
                                                    formular_bloecke();
                                                    
                                                    if(ui.item.find(".ftype").val() != 'string')
                                                        $(sblockO).find("div.bL").removeClass("bLH");
                                                    
                                                    var new_parent = ui.item.parents(".sblock:first").find(".bid").val();
                                                    ui.item.find(".fbid").val(new_parent);
                                                    
                                                    ui.item.find(".flinks").val((ui.item.parent(".bL")[0]?'1':'0'));
                                                }
                                            }).disableSelection(); 
                                                            
                                            var oparent = $(sblockO).offset().top;        
                                            $(sblockO).sortable({
                                                items: $(sblockO),
                                                forcePlaceholderSize: true,
                                                handle: '.anfasser',
                                                axis: 'y',
                                                start: function(){
                                                    oparent = $(sblockO).offset().top;
                                                    $(sblockO).find(".bR, .bL").addClass("mitRand");
                                                }, stop: function(e, ui){
                                                    $(sblockO).find(".bR, .bL").removeClass("mitRand");
                                                    formular_bloecke();
                                                },
                                                sort: function(event, ui) {
                                                    var helper = ui.helper;
                                                    $(helper).css("top", (ui.offset.top - oparent));
                                                }
                                            }).disableSelection();
                                        }
                                        formular_bloecke();
                                        
                                        // Strukturelement fuer Formular-OK-Seite waehlen
                                        $(inhalt).find("button.ele_choose").off().on("click", function(e){
                                            e.preventDefault();
                                            
                                            fenster({
                                                id: 'n130',
                                                blackscreen: '2',
                                                width: 750,
                                                cb: function(nwin, ninhalt){
                                                     $.get('inc_structure.php', {
                                                        index : 'n120',
                                                        just_select: true
                                                    }, function(data){ logincheck(data); 
                                                        
                                                        $(ninhalt).html(data);
                                                        save_button(nwin);
                                                        
                                                        struktur_start({
                                                            just_select: true,
                                                            select_cb: function(zid, ztitel, zelement){
                                                                $(inhalt).find("p.ele_choosen").html(ztitel);
                                                                $(inhalt).find("input[name=ziel]").val(zid);
                                                                
                                                                $(nwin).find("p.close").trigger("click");
                                                            }
                                                        });
                                                    });
                                                }
                                            });
                                        });
                                        
                                        // Feldzuordnungen-Buttons zeigen / verstecken
                                        $(inhalt).find("#form_feldzuordnungen input[type=checkbox]").off("click").on("click", function(e){
                                            var sbutton = $(this).parent("td").next("td").children("button");
                                            
                                            if($(this).is(":checked")){
                                                $(sbutton).fadeIn();
                                            } else {
                                                $(sbutton).fadeOut();
                                            }
                                        });
                                        
                                        // Feldzuordnungen-Fenster oeffnen
                                        $(inhalt).find("#form_feldzuordnungen button").off("click").on("click", function(e){
                                            e.preventDefault();
                                            
                                            var ztype = $(this).attr("class");
                                            
                                            if(ztype == 'fzo_1')
                                                var optO = $(inhalt).find("#form_feldzuordnungen input[name=zuordnung_benutzer]"); 
                                            if(ztype == 'fzo_2')
                                                var optO = $(inhalt).find("#form_feldzuordnungen input[name=zuordnung_dokument]"); 
                                            if(ztype == 'fzo_3')
                                                var optO = $(inhalt).find("#form_feldzuordnungen input[name=zuordnung_produkt]"); 
                                                
                                            if(ztype == 'fzo_1'){
                                                var ft_width = 850;
                                                var a_type = 5;
                                            } else {
                                                var ft_width = 400;
                                                var a_type = 7;
                                            }
                                            
                                            var clicked = $("#fn260");
                                            var index = "n269";                                                    
                                            
                                            fenster({
                                                id: 'n269',
                                                blackscreen: '2',
                                                width: ft_width,
                                                cb: function(neww, inhaltSL){
                                            
                                                    function zu_content_laden(){
                                                        $.post('inc_documents.php', {
                                                            index: index,
                                                            a: 5,
                                                            ztype: ztype,
                                                            id : ausgewaehltes_dokument,
                                                            block: $(inhalt).find("#block_id").val(),
                                                            f: $(inhalt).find("#formular_form").serialize(),
                                                            opt: $(optO).val()
                                                        }, function(data){ logincheck(data); 
                                                            $(inhaltSL).html(data);
                                                            setFocus(neww);
                                                            
                                                            var tzuordnung = $(inhaltSL).find("table.zuordnung");
                                                            
                                                            // Pfeil-Grafiken tauschen
                                                            $(tzuordnung).find("select").off("change").on("change", function(){
                                                                var trO = $(this).parents("tr:first").find("td img");
                                                                
                                                                if($(this).val() == ''){
                                                                    $(trO).attr('src', 'images/pfeil_weiss.png');
                                                                } else {
                                                                    $(trO).attr('src', 'images/pfeil_blau.png');
                                                                }
                                                            });
                                                            
                                                            // Pfeil-Grafiken bei Notizen tauschen
                                                            function pfeil_notiz(){
                                                                $(tzuordnung).find("tr.notiz input").off("blur").on("blur", function(){
                                                                    var trO = $(this).parents("tr:first").find("td img");
                                                                    
                                                                    if($(this).val() == ''){
                                                                        $(trO).attr('src', 'images/pfeil_weiss.png');
                                                                    } else {
                                                                        $(trO).attr('src', 'images/pfeil_blau.png');
                                                                    }
                                                                });
                                                            }
                                                            pfeil_notiz();
                                                                
                                                            // Notiz hinzufuegen
                                                            $(tzuordnung).find("#add_notiz").off("click").on("click", function(e){
                                                                e.preventDefault(); 
                                                                
                                                                var old = $(tzuordnung).find("tr.notiz")[0];
                                                                var new_auswahl = $(old).clone();  
                                                                
                                                                $(new_auswahl).find("select option:first").attr('selected', 'true');
                                                                $(new_auswahl).find("input").val('');
                                                                $(new_auswahl).find("img").attr('src', 'images/pfeil_weiss.png');
                                                                
                                                                $(tzuordnung).find("tr.last").before(new_auswahl); 
                                                                pfeil_notiz();
                                                            });
                                                            
                                                            if(ztype == 'fzo_1'){ // Nur fuer Benutzer anlegen
                                                                // Aktivierungslink / Weiterleitung freischalten/verstecken
                                                                $(inhaltSL).find("select[name=status]").off("change").on("change", function(){ 
                                                                    if($(this).val() == '1'){
                                                                        $(inhaltSL).find("div.zusatz p.akt_link").slideDown();
                                                                        $(inhaltSL).find("div.akt_wl").slideDown();
                                                                    } else {
                                                                        $(inhaltSL).find("div.zusatz p.akt_link").slideUp();
                                                                        $(inhaltSL).find("div.akt_wl").slideUp();
                                                                    } 
                                                                });
                                                                
                                                                // Email-Text
                                                                $(inhaltSL).find("input[name=mail]").off("click").on("click", function(){ 
                                                                    if($(this).val() == '0'){
                                                                        $(inhaltSL).find("div.textbox").hide();
                                                                    } else {
                                                                        $(inhaltSL).find("div.textbox").show();
                                                                    } 
                                                                });
                                                                
                                                                // CKEditor laden
                                                                var zeditor = $(inhaltSL).find("#zmailtext"); 
                                                                $(zeditor).ckeditor(function() { 
                                                                    my_zu_ckeditor = $(zeditor).ckeditorGet();
                                                                
                                                                    $(inhaltSL).find("div.zusatz a").off("click").on("click", function(){ 
                                                                        var cf = $(this).attr("rel");
                                                                        my_zu_ckeditor.insertHtml(cf);
                                                                    });
                                                                                                
                                                                    load_fck_button(this);
                                                                    set_button(neww); 
                                                                }, ckconfig);
                                                                
                                                                $(neww).find("p.close").on("click", function(){
                                                                    my_zu_ckeditor.destroy();
                                                                });
                                                                
                                                                // Strukturelement fuer Formular-OK-Seite waehlen
                                                                $(inhaltSL).find("button.ele_choose").off().on("click", function(e){
                                                                    e.preventDefault();
                                                                    var ppa = $(this).parent("div.ubR");
                                                                    
                                                                    fenster({
                                                                        id: 'n130',
                                                                        blackscreen: '3',
                                                                        width: 750,
                                                                        cb: function(nwin, ninhalt){
                                                                             $.get('inc_structure.php', {
                                                                                index : 'n120',
                                                                                just_select: true
                                                                            }, function(data){ logincheck(data); 
                                                                                
                                                                                $(ninhalt).html(data);
                                                                                save_button(nwin);
                                                                                
                                                                                struktur_start({
                                                                                    just_select: true,
                                                                                    select_cb: function(zid, ztitel, zelement){
                                                                                        $(ppa).find("p.ele_choosen").html(ztitel);
                                                                                        $(ppa).find("input[type=hidden]").val(zid);
                                                                                        
                                                                                        $(nwin).find("p.close").trigger("click");
                                                                                    }
                                                                                });
                                                                            });
                                                                        }
                                                                    });
                                                                });
                                                            } else { // Nur fuer Dokumente und Produkte anlegen
                                                                // Rewrite Target anzeigen oder verstecken
                                                                $(inhaltSL).find("select[name=status]").off("change").on("change", function(){ 
                                                                    if($(this).val() == '2'){
                                                                        $(inhaltSL).find("div.rewrite_taget").slideDown();
                                                                    } else {
                                                                        $(inhaltSL).find("div.rewrite_taget").slideUp().find("input[name=rewrite_taget]").attr("checked", false);
                                                                    } 
                                                                });
                                                            } 
                                                            
                                                            // Close
                                                            $(inhaltSL).find("input.bs1").off("click").on("click", function(){ 
                                                                $(neww).find("p.close").trigger("click");
                                                            });
                                                            
                                                            // Optionen speichern
                                                            $(inhaltSL).find("input.bs2").off("click").on("click", function(){ 
                                                                var opt = $(inhaltSL).find("#feld_zuordnungen").serialize();
                                                                
                                                                $.post('inc_documents.php', {
                                                                    index: 'n269',
                                                                    opt: opt,
                                                                    a: 6
                                                                }, function(data){  
                                                                    $(optO).val(data);                                       
                                                                    
                                                                    $(neww).find("p.close").trigger("click");
                                                                }); 
                                                                
                                                                if(ztype == 'fzo_2'){
                                                                    if($(inhaltSL).find("input[name=rewrite_taget]").is(":checked")){
                                                                        $(inhalt).find("div.rewrite_taget_no").hide().siblings("div.rewrite_taget_yes").show();
                                                                    } else {
                                                                        $(inhalt).find("div.rewrite_taget_no").show().siblings("div.rewrite_taget_yes").hide();
                                                                    }
                                                                }
                                                            });
                                                        });
                                                    }
                                                    
                                                    function choose2content(){
                                                        $(neww).width(850).css('left', ($("#main").width() / 2 - 425) + 'px');
                                                        $(drag).css("width", ($(kopfleiste).width() - 71) + 'px');
                                                        $(inhaltSL).html('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />');
                                                        zu_content_laden(); 
                                                    }
                                                    
                                                    function zu_choose_laden(){
                                                        $.post('inc_documents.php', {
                                                            index: index,
                                                            a: 7,
                                                            ztype: ztype,
                                                            opt: $(optO).val()
                                                        }, function(data){ logincheck(data); 
                                                            $(inhaltSL).html(data);
                                                            
                                                            // Close
                                                            $(inhaltSL).find("input.bs1").off("click").on("click", function(){ 
                                                                $(neww).find("p.close").trigger("click");
                                                            });
                                                            
                                                            // Optionen speichern
                                                            $(inhaltSL).find("input.bs2").off("click").on("click", function(){ 
                                                                var alte_klasse = $(inhaltSL).find("input[name=alte_klasse]").val();
                                                                var klasse = $(inhaltSL).find("select[name=klasse]").val();
                                                                
                                                                if(klasse != alte_klasse){
                                                                    $.post('inc_documents.php', {
                                                                        index: 'n269',
                                                                        klasse: klasse,
                                                                        a: 8
                                                                    }, function(data){  
                                                                        $(optO).val(data);  
                                                                        choose2content();                     
                                                                    }); 
                                                                } else {
                                                                    choose2content();
                                                                }
                                                            });
                                                        });
                                                    }                                    
                                                    
                                                    if(a_type == 5)
                                                        zu_content_laden();
                                                    else
                                                        zu_choose_laden();
                                                }
                                            });
                                        });
                                        
                                        
                                        // Formular speichern
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
                                            
                                            $.post('inc_documents.php', {
                                                index : 'n269',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $("#fn260").find("#block_ibid").val(),
                                                blockindex: $("#fn260").find("#blockindex").val(),
                                                f: $(inhalt).find("#formular_form").serialize(),
                                                a: 99
                                            }, function(data){
                                                logincheck(data);
                                                $(selfbutton).removeAttr("disabled");
                                                
                                                zurfreigabe();
                                                load_bloecke(); 
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });
                                        
                                        // Formularelement Optionen
                                        function formular_optionen(){
                                            $("#formular").find("a.name, a.opt").off("click").on("click", function(e){
                                                e.stopPropagation();
                                                e.preventDefault();
                                                
                                                clicked = $(this);
                                                t_parent = $(clicked).parent("div.feld");
                                                t_fid = $(clicked).siblings("input.fbid");
                                                t_name = $(clicked).siblings("input.fname");
                                                t_type = $(clicked).siblings("input.ftype");
                                                t_opt = $(clicked).siblings("input.fopt");
                                                                                                
                                                fenster({
                                                    id: 'n261',
                                                    blackscreen: '2',
                                                    width: 730,
                                                    cb: function(neww, inhalt_fopt){
                                                
                                                        $.post('inc_documents.php', {
                                                            index: 'n269',
                                                            a: 3,
                                                            id: ausgewaehltes_dokument,
                                                            fid: $(t_fid).val(),
                                                            name: $(t_name).val(),
                                                            type: $(t_type).val(),
                                                            opt: $(t_opt).val()
                                                        }, function(data){
                                                            $(inhalt_fopt).html(data);
                                                            setFocus(neww);
                                                            var feditO = $(inhalt_fopt).find("#fele_edit");
                                                                                                                        
                                                            // Editor
                                                            var my_w_ckeditor = null;
                                                            var the_editor = $(inhalt_fopt).find("textarea.text");
                                                            
                                                            if($(the_editor)[0]){
                                                                var ck_instance = CKEDITOR.instances['fname'];
                                                                if(ck_instance) CKEDITOR.remove(ck_instance);
                                                                
                                                                $(the_editor).ckeditor(function() { 
                                                                    my_w_ckeditor = $(the_editor).ckeditorGet();
                                                                    
                                                                    load_fck_button(this); 
                                                                    set_button(neww);
                                                                }, ckconfig);
                                                            }
                                                            
                                                            // Feld-Optionen schliessen
                                                            $(inhalt_fopt).find("input.bs1").off("click").on("click", function(){
                                                                if(my_w_ckeditor != null)
                                                                    my_w_ckeditor.destroy(true);
                                                                    
                                                                $(neww).find("p.close").trigger("click");
                                                            });
                                                            
                                                            // Pflicht-Bedingung ein/ausblenden
                                                            $(feditO).find("input[name=pflicht]").off("click").on("click", function(){
                                                                if($(this).val() == '0'){
                                                                    $(feditO).find("tr.pflicht_optional").fadeOut();
                                                                } else {
                                                                    $(feditO).find("tr.pflicht_optional").fadeIn();
                                                                }
                                                            });
                                                            
                                                            // Radiobutton Beschriftung entfernen
                                                            var tda = $(feditO).find("td.auswahl");
                                                            function radio_auswahl_entfernen(){
                                                                $(tda).find("a.del").off("click").on("click", function(e){
                                                                    e.preventDefault();
                                                                    var auswahl = $(this).parent("p");
                                                                    
                                                                    sfrage_show('M&ouml;chten Sie diese Auswahl wirklich entfernen?');
                                                                    $("#sfrage button:last").on("click", function(){
                                                                        $(auswahl).remove();
                                                                    });
                                                                });
                                                            }
                                                            radio_auswahl_entfernen();
                                                            
                                                            // Radiobutton Beschriftung hinzufuegen
                                                            $(tda).find("a.add").off("click").on("click", function(e){
                                                                e.preventDefault(); 
                                                                var old = $(tda).find("p.ignore");
                                                                var new_auswahl = $(old).clone();  
                                                                var counta = parseInt($(tda).find("p:last strong").html()) + 1;
                                                                
                                                                $(new_auswahl).removeClass("ignore").find("strong").html(counta);
                                                                $(tda).find("a.add").before(new_auswahl); 
                                                                
                                                                radio_auswahl_entfernen();
                                                            });
                                                            
                                                            // Radiobutton Beschriftung sortieren
                                                            $(tda).sortable({
                                                                handle: 'span.schieber',
                                                                items: 'p',
                                                                containment: 'parent',
                                                                axis: 'y'
                                                            });
                                                            
                                                            // Dateiupload Ordner
                                                            var upload_dir = $(feditO).find("td.upload_dir");
                                                            if($(upload_dir)[0]){
                                                                $(upload_dir).children("button.choose_dir").off().on("click", function(e){
                                                                    e.preventDefault();
                                                                    
                                                                    chooseDir({
                                                                        blackscreen: '3',
                                                                        active: $(upload_dir).children("input[name=dir]").val(),
                                                                        cb: function(did, dtitle){
                                                                            $(upload_dir).children("p.current_dir").html(dtitle);
                                                                            $(upload_dir).children("input[name=dir]").val(did);       
                                                                        }
                                                                    });
                                                                });
                                                            }
                                                            
                                                            // Feld entfernen
                                                            $(feditO).find("button#del_feld").off("click").on("click", function(e){
                                                                e.preventDefault();
                                                                
                                                                sfrage_show('M&ouml;chten Sie dieses Feld wirklich entfernen?');
                                                                $("#sfrage button:last").on("click", function(){
                                                                    $(t_parent).remove();
                                                                    formular_bloecke();
                                                                    $(neww).find("p.close").trigger("click");
                                                                });
                                                            });
                                                            
                                                            // Feld-Optionen speichern
                                                            $(inhalt_fopt).find("input.bs2").off("click").on("click", function(){ 
                                                                var feld_opt = $("#feld_optionen").serialize();
                                                                var name = $(feditO).find("input[name=fname]").val();
                                                                
                                                                if($(the_editor)[0])
                                                                    name = $(the_editor).val();
                                                                
                                                                $.post('inc_documents.php', {
                                                                    index: 'n269',
                                                                    feld_opt: feld_opt,
                                                                    feld_name: name,
                                                                    a: 4
                                                                }, function(data){  
                                                                    var dataA = data.split('{stop]');
                                                                    var opt = dataA[0];
                                                                    var tname = dataA[1];
                                                                    
                                                                    $(t_name).val(name);
                                                                    $(t_opt).val(opt); 
                                                                    $(t_parent).find("a.name").html(tname);                                           
                                                                    
                                                                    $(neww).find("p.close").trigger("click");
                                                                }); 
                                                                
                                                                if(my_w_ckeditor != null)
                                                                    my_w_ckeditor.destroy(true);
                                                            });
                                                        });
                                                    }
                                                });
                                            });
                                        }
                                    }
                                    /// FORMULAR ENDE
                                    
                                    
                                    /// REFERENCE START
                                    if($(inhalt).find("#dreference")[0]){
                                        
                                        var reference = $(inhalt).find("#dreference");
                                        var step_document = $(reference).find("div.step_document");
                                        var step_column = $(reference).find("div.step_column");
                                        var step_content = $(reference).find("div.step_content");
                                        
                                        var document_id = $(step_document).find("input[name=document]");
                                        var d_id = parseInt($(document_id).val());
                                        
                                        $(step_document).find("button.choose").off().on("click", function(e){
                                            e.preventDefault();
                                            
                                            chooseDocument({
                                                blackscreen: '2',
                                                cb: function(doc_id, doc_title){
                                                    $(document_id).val(doc_id);
                                                    $(step_document).find("p.choosen").html(doc_title);
                                                    
                                                    d_id = doc_id;
                                                    
                                                    reloadColumns();
                                                }
                                            });
                                        });
                                        
                                        function reloadColumns(){
                                            if(!d_id){
                                                $(step_column).hide();
                                                $(step_content).hide();
                                                return false;
                                            }
                                            
                                            $.post('inc_documents.php', {
                                                index : 'block-reference-columns',
                                                f: $(reference).serialize()
                                            }, function(data){ 
                                                logincheck(data);
                                                $(step_column).show().children("div.columns").html(data);                                                
                                                reloadBlocks();
                                                
                                                $(step_column).find("input[name=column]").off("click").on("click", reloadBlocks);
                                                
                                                set_button(neww, sb);
                                            });
                                            
                                            $(step_column).show().children("div.columns").html('<img src="images/loading.gif" alt="loading" class="ladebalken" />');
                                        }
                                        
                                        function reloadBlocks(){
                                            var checked_column = $(step_column).find("input[name=column]").filter(":checked");
                                            if(!$(checked_column)[0]){
                                                $(step_content).hide();
                                                return false;
                                            }
                                            
                                            $.post('inc_documents.php', {
                                                index : 'block-reference-blocks',
                                                f: $(reference).serialize()
                                            }, function(data){ 
                                                logincheck(data);                                            
                                                $(step_content).show().children("div.contents").html(data);
                                                
                                                $(step_content).find("div.show_hide input").off().on("click", function(){
                                                    if($(this).val() == 0){
                                                        $(step_content).find("span.is_hide").show().siblings("span.is_show").hide();
                                                    } else {
                                                        $(step_content).find("span.is_show").show().siblings("span.is_hide").hide();
                                                    }
                                                });
                                                
                                                set_button(neww, sb);
                                            });
                                            
                                            $(step_content).show().children("div.contents").html('<img src="images/loading.gif" alt="loading" class="ladebalken" />');
                                        }
                                        
                                        reloadColumns();
                                          
                                        
                                        // save reference
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
        
                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                a2db: true,
                                                f: $(reference).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                $(selfbutton).removeAttr("disabled");
                                                load_bloecke(); 
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });  
                                    }
                                    
                                    
                                    /// KOMMENTARE START
                                    if($(inhalt).find("#dcomments")[0]){
                                        
                                        var comments = $(inhalt).find("#dcomments");
                                        var fields = $(comments).find("fieldset.feld");
                                        
                                        $(comments).find("input").off("keyup");
                                        
                                        $(fields).find("legend input[type=checkbox]").off("click").on("click", function(){
                                            var pare = $(this).parents("fieldset.feld:first");
                                            
                                            if($(this).is(":checked")){
                                                $(pare).addClass("faktiv");
                                                $(pare).find("p input[type=checkbox]").removeAttr("disabled");
                                            } else {
                                                $(pare).removeClass("faktiv");
                                                $(pare).find("p input[type=checkbox]").attr("disabled", "disabled");
                                            }
                                        });
                                        
                                        $(comments).find("#komment_pn").off("click").on("click", function(){
                                            var pare = $(this).siblings("p.pn");
                                            
                                            if($(this).is(":checked")){
                                                $(pare).slideDown();
                                            } else {
                                                $(pare).slideUp();
                                            }
                                        });
                                        
                                        var loggedusers_force = $(comments).find("tr.loggedusers_force");
                                        $(comments).find("select[name=loggedusers]").off("change click").on("change click", function(){
                                            if($(this).val() == '0'){
                                                $(loggedusers_force).show();
                                            } else {
                                                $(loggedusers_force).hide();
                                            }
                                        });
                                          
                                        
                                        // Kommentar-Optionen speichern
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
        
                                            $.post('inc_documents.php', {
                                                index : 'n260_save_comments',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(comments).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                $(selfbutton).removeAttr("disabled");
                                                load_bloecke(); 
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });  
                                    }
                                    // KOMMENTARE ENDE   
                                    
                                    
                                    /// LOGIN START
                                    if($(inhalt).find("#dlogin")[0]){
                                        var dlogin = $(inhalt).find("#dlogin");  
                                        
                                        $(dlogin).find("input").off("keyup");   
                                        
                                        // Weiterleitungs-Optionen ein- und ausblenden
                                        $(dlogin).find("input[name=success_go]").off("click").on("click", function(){
                                            if($(this).val() == '0'){
                                                $(dlogin).find("tr.success_go_a").show();
                                                $(dlogin).find("tr.success_go_b").hide();
                                                
                                                $(dlogin).find("input[name=success_forwarding]").val('');
                                                $(dlogin).find("tr.success_go_b p.ele_choosen").html('<em>Kein Element gew&auml;hlt</em>');
                                            } else {
                                                $(dlogin).find("tr.success_go_a").hide();
                                                $(dlogin).find("tr.success_go_b").show();
                                            }
                                        });
                                        
                                        $(dlogin).find("input[name=success_logout_go]").off("click").on("click", function(){
                                            if($(this).val() == '0'){
                                                $(dlogin).find("tr.success_logout_go_a").show();
                                                $(dlogin).find("tr.success_logout_go_b").hide();
                                                
                                                $(dlogin).find("input[name=success_logout_forwarding]").val('');
                                                $(dlogin).find("tr.success_logout_go_b p.ele_choosen").html('<em>Kein Element gew&auml;hlt</em>');
                                            } else {
                                                $(dlogin).find("tr.success_logout_go_a").hide();
                                                $(dlogin).find("tr.success_logout_go_b").show();
                                            }
                                        });
                                        
                                        // Strukturelement fuer Eingeloggt-OK waehlen
                                        $(dlogin).find("button.ele_choose").off().on("click", function(e){
                                            e.preventDefault();
                                            var parenttd = $(this).parent("td");
                                            
                                            fenster({
                                                id: 'n130',
                                                blackscreen: '2',
                                                width: 750,
                                                cb: function(nwin, ninhalt){
                                                     $.get('inc_structure.php', {
                                                        index : 'n120',
                                                        just_select: true
                                                    }, function(data){ logincheck(data); 
                                                        
                                                        $(ninhalt).html(data);
                                                        save_button(nwin);
                                                        
                                                        struktur_start({
                                                            just_select: true,
                                                            select_cb: function(zid, ztitel, zelement){
                                                                $(parenttd).find("p.ele_choosen").html(ztitel);
                                                                $(parenttd).find("input[type=hidden]").val(zid);
                                                                
                                                                $(nwin).find("p.close").trigger("click");
                                                            }
                                                        });
                                                    });
                                                }
                                            });
                                        });                            
                                        
                                        // Optionen speichern
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
        
                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(dlogin).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                $(selfbutton).removeAttr("disabled");
                                                load_bloecke(); 
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });  
                                    }
                                    // LOGIN ENDE 
                                    
                                    
                                    /// GOOGLE MAPS START
                                    if($(inhalt).find("#googlemaps")[0]){
                                        var googlemaps = $(inhalt).find("#googlemaps");  
                                        var zoom_val = $(googlemaps).find("input[name=zoom]");
                                        
                                        $(googlemaps).find("div.zoomer").slider({
                                            min: 1,
                                            max: 19,
                                            value: $(zoom_val).val(),
                                            stop: function(event, ui){
                                                $(zoom_val).val(ui.value);
                                            }
                                        });
                                        
                                        $(googlemaps).find("#gmaps_marker").off().on("click", function(){
                                            var nextp = $(this).siblings("p");
                                            
                                            if($(this).is(":checked")){
                                                $(nextp).slideDown(500, function(){ set_button($("#fn260"), sb); });
                                            } else {
                                                $(nextp).slideUp(500, function(){ set_button($("#fn260"), sb); });
                                            }
                                        });
                                        
                                        $(googlemaps).find("select[name=width_typ]").off().on("click", function(){
                                            var nextp = $(this).siblings("input");
                                            
                                            if($(this).val() == 0)
                                                $(nextp).val(100);
                                            else if($(this).val() == 1)
                                                $(nextp).val(400);
                                        });
                                        
                                        $(googlemaps).find("a.goaway").off().on("click", function(){
                                            fenster({
                                                id: 'gmaps_pos',
                                                width: 450,
                                                blackscreen: '2',
                                                cb: function(nwin, ninhalt){
                                                    $.post('inc_documents.php', {
                                                        index: 'n260_gmaps_pos'
                                                    }, function(data){ 
                                                        logincheck(data);
                                                        
                                                        $(ninhalt).html(data);
                                                        save_button(nwin);
                                                        
                                                        var erg = $(ninhalt).find("p.erg");
                                                        var mlat = 0;
                                                        var mlong = 0;
                                                        
                                                        $(ninhalt).find("button").off().on("click", function(e){
                                                            e.preventDefault(); 
                                                            
                                                            $.post('inc_documents.php', {
                                                                index: 'n260_gmaps_pos_get',
                                                                address: $(ninhalt).find("input[name=addr]").val()
                                                            }, function(data){
                                                                if(!data){
                                                                    $(erg).html('Der gesuchte Ort wurde nicht gefunden. Bitte probieren Sie es mit einer anderen Schreibweise erneut.');
                                                                } else if(data == 'error'){
                                                                    $(erg).html('Es konnte nicht mit dem Google Maps Server kommuniziert werden. Entweder der Google Maps Server ist derzeit nicht erreichbar oder ihr Server ist nicht in der Lage, mit dem Google Maps Server zu kommunizieren.');
                                                                } else {
                                                                    $(ninhalt).find("div.box_save").slideDown();
                                                                    
                                                                    var coord = data.split('||');  
                                                                    mlat = coord[0];
                                                                    mlong = coord[1];
                                                                    
                                                                    $(erg).html('<strong>Ermittelte Koordinaten:</strong><br />Breitengrad: '+coord[0]+'<br />Längengrad: '+coord[1]);
                                                                }
                                                            });
                                                        });
                                                        
                                                        $(ninhalt).find("input[name=addr]").off("keypress").on("keypress", function(e){
                                                            if(e.keyCode == 13) {
                                                                $(ninhalt).find("button").trigger("click");
                                                                e.preventDefault();
                                                            }
                                                        });
                                                        
                                                        $(ninhalt).find("div.box_save input.bs2").off().on("click", function(e){
                                                            if(mlat && mlong){
                                                                $(googlemaps).find("input[name=lat]").val(mlat);
                                                                $(googlemaps).find("input[name=long]").val(mlong);
                                                            }
                                                            
                                                            $(nwin).find("p.close").trigger("click");
                                                        });
                                                    });
                                                }
                                            });
                                        });
                                        
                                        // Optionen speichern
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");
        
                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(googlemaps).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke(); 
                                                $(selfbutton).removeAttr("disabled");
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });  
                                    }
                                    // GOOGLE MAPS ENDE


                                    /// QR-CODE START
                                    if($(inhalt).find("#qrcode")[0]){
                                        var qrcode = $(inhalt).find("#qrcode");

                                        $(qrcode).find("input").off("keyup");

                                        // QR-Optionen speichern
                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");

                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(qrcode).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke();
                                                $(selfbutton).removeAttr("disabled");

                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });
                                    }
                                    // QR-CODE ENDE


                                    /// LINKPICKER START
                                    if($(inhalt).find("#urllinkpicker")[0]){
                                        var tpicker = $(inhalt).find("#urllinkpicker");

                                        var inputs = {
                                            href: $(tpicker).find("input[name=href]"),
                                            email: $(tpicker).find("input[name=email]"),
                                            element: $(tpicker).find("input[name=element]"),
                                            document: $(tpicker).find("input[name=document]"),
                                            file: $(tpicker).find("input[name=file]"),
                                            text: $(tpicker).find("input[name=text]"),
                                            title: $(tpicker).find("input[name=title]"),
                                            classes: $(tpicker).find("input[name=classes]"),
                                            target: $(tpicker).find("input[name=target]"),
                                            power: $(tpicker).find("input[name=power]")
                                        };

                                        $("#fn260").hide();

                                        $(sb).find("input:last").off().on("click", function(){
                                            var selfbutton = $(this);
                                            $(selfbutton).attr("disabled", "disabled");

                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(tpicker).serialize(),
                                                a2db: 1
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke();
                                                $(selfbutton).removeAttr("disabled");

                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });


                                        var options = {
                                            popup: 2,
                                            href: $(inputs.href).val(),
                                            email: $(inputs.email).val(),
                                            element: $(inputs.element).val(),
                                            document: $(inputs.document).val(),
                                            file: $(inputs.file).val(),
                                            text: $(inputs.text).val(),
                                            title: $(inputs.title).val(),
                                            classes: $(inputs.classes).val(),
                                            target: $(inputs.target).val(),
                                            power: $(inputs.power).val(),
                                            picked: function(robj){
                                                $(inputs.href).val(robj.href);
                                                $(inputs.email).val(robj.email);
                                                $(inputs.element).val(robj.element);
                                                $(inputs.document).val(robj.document);
                                                $(inputs.file).val(robj.file);
                                                $(inputs.text).val(robj.text);
                                                $(inputs.title).val(robj.title);
                                                $(inputs.classes).val(robj.classes);
                                                $(inputs.target).val(robj.target);
                                                $(inputs.power).val(robj.power);

                                                $("#fn260").show();
                                                $(sb).find("input:last").trigger("click");
                                            },
                                            closed: function(){
                                                $("#fn260").show().find("p.close").trigger("click");
                                            }
                                        }

                                        if($(tpicker).hasClass("urlpicker"))
                                            fks.openUrlPicker(options);
                                        else
                                            fks.openLinkPicker(options);
                                    }
                                    // LINKPICKER ENDE

                                    
                                    // EXTENSION START
                                    if(extension){
                                        var ef = $(inhalt).find("#extension_form");
                                        
                                        if(extjscallback)
                                            $.getScript(extjscallback);

                                        if(extcsscallback){
                                            $.get(extcsscallback, function(css){
                                                $('<style type="text/css"></style>').html(css).appendTo("head");
                                            });
                                        }

                                        
                                        // speichern
                                        $(sb).show().find("input:last").off().on("click", function(){
                                            $(this).attr("disabled", "disabled");
        
                                            $.post('inc_documents.php', {
                                                index : 'save_extension',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(ef).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                load_bloecke(); 
                                                zurfreigabe();
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });

                                        set_button($("#fn260"), sb);
                                    }
                                    // EXTENSION ENDE
                                    
                                    /// RELATIONSPLATZHALTER START
                                    if($(inhalt).find("#relationsform")[0]){
                                        var relation = $(inhalt).find("#relationsform");
                                        var baum = $(relation).find("div.baum");
                                        var related = $(relation).find("input[name=related]").val();
                                        var limit = parseInt($(relation).find("input[name=limit]").val());
                                        var sortinp = $(relation).find("input[name=sort]");
                                        
                                        // speichern
                                        $(sb).find("input:last").off().on("click", function(){
                                            $(this).attr("disabled", "disabled");
        
                                            $.post('inc_documents.php', {
                                                index : 'save_form',
                                                id : ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                ibid: $(inhalt).find("#block_ibid").val(),
                                                blockindex: $(inhalt).find("#blockindex").val(),
                                                f: $(relation).serialize()
                                            }, function(data){
                                                logincheck(data);
                                                zurfreigabe();
                                                load_bloecke(); 
                                                
                                                $("#fn260 p.close").trigger("click");
                                            });
                                        });  
                                        
                                        function refresh_relation_index(){
                                            var newinp = '';
                                            $(baum).find("div.zweig").each(function(index){
                                                newinp += (index > 0?',':'')+$(this).data('kat');
                                            });
                                            $(sortinp).val(newinp);
                                        }
                                        
                                        function lade_relation(){
                                            $.post('inc_documents.php', {
                                                index: 'n260_load_relation',
                                                id: ausgewaehltes_dokument,
                                                block: $(inhalt).find("#block_id").val(),
                                                related: related,
                                                limit: limit,
                                                sort: $(sortinp).val()
                                            }, function(data){
                                                logincheck(data);
                                                $(baum).html(data);
                                                
                                                refresh_relation_index();
                                                
                                                var zweig = $(baum).find("div.zweig");
                                                var row = $(zweig).find("div.row");
                                                var whites = $(row).find("div.white");
                                                var more = $(row).find("div.more");
                                                
                                                // Breite der Bereiche anpassen
                                                $(row).each(function(){
                                                    var white = $(this).find("div.white");
                                                    var tmore = $(this).find("div.more");
                                                    $(tmore).width(($(this).width() - $(white).width()));
                                                });
                                                $(more).hide();
                                                
                                                // Graue Balken bei Hover anzeigen
                                                $(row).off("mouseenter mouseleave").on("mouseenter", function(){
                                                    $(this).find("div.more").show();
                                                }).on("mouseleave", function(){
                                                    $(this).find("div.more").hide().find("div.optarea").hide();
                                                });
                                                
                                                // Elemente loeschen
                                                $(more).find("a.del").off().on("click", function(){
                                                    var myrow = $(this).parents("div.zweig");
                                                    
                                                    sfrage_show('Wollen Sie dieses Dokument wirklich entfernen?');
                                                    $("#sfrage button:last").on("click", function(){
                                                        $(myrow).remove();
                                                        
                                                        refresh_relation_index();
                                                        lade_relation();
                                                    });
                                                });
                        
                                                // Eintraege sortierbar machen
                                                $(baum).sortable({
                                                    items: 'div.zweig',                    
                                                    handle: 'div.move',
                                                    containment: relation,
                                                    axis: 'y',
                                                    stop: function(e, ui){
                                                        refresh_relation_index();
                                                    }
                                                });
                                                
                                                // Neues Dokument hinzufügen
                                                $(baum).find("button.new").off().on("click", function(e){
                                                    e.preventDefault();
                                                    
                                                    fenster({
                                                        id: 'n295',
                                                        width: 960,
                                                        blackscreen: '2',
                                                        cb: function(fn295, nfinhalt){
                                                            $.post('inc_documents.php', {
                                                                index: 'n290',
                                                                rel: related,
                                                                choose: true,    
                                                                limit: limit
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
                                                                        
                                                                        var gids = '';
                                                                        $(barea).find("li").each(function(ccc){
                                                                            gids += (!ccc?'':',')+$(this).data('id');
                                                                        });
                                                                        
                                                                        var newsort = ($(sortinp).val() != ''?$(sortinp).val()+',':'')+gids;
                                                                        $(sortinp).val(newsort);
                                                                        
                                                                        lade_relation();
                                                                        
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
                                                });
                                            });
                                        }
                                        lade_relation();
                                    }
                                    // RELATIONSPLATZHALTER ENDE   
                                    
                                    
                                    
                                    //// START DER ELEMENT-OPTIONEN
                                    
                                    // Zuordnung für Aufteilen
                                    var bs_zuordnung = new Array();
                                    bs_zuordnung[1] = 'A';
                                    bs_zuordnung[2] = 'B';
                                    bs_zuordnung[3] = 'C';
                                    bs_zuordnung[4] = 'D';
                                    bs_zuordnung[5] = 'E';
                                    bs_zuordnung[6] = 'F';
                                    bs_zuordnung[7] = 'G';
                                    bs_zuordnung[8] = 'H';
                                    bs_zuordnung[9] = 'I';
                                    bs_zuordnung[10] = 'J';
                                    bs_zuordnung[11] = 'K';
                                    bs_zuordnung[12] = 'L';
                                    bs_zuordnung[13] = 'M';
                                    bs_zuordnung[14] = 'N';
                                    bs_zuordnung[15] = 'O';
                                    bs_zuordnung[16] = 'P';
                                    bs_zuordnung[17] = 'Q';
                                    bs_zuordnung[18] = 'R';
                                    bs_zuordnung[19] = 'S';
                                    bs_zuordnung[20] = 'T';
                                    bs_zuordnung[21] = 'U';
                                    bs_zuordnung[22] = 'V';
                                    bs_zuordnung[23] = 'W';
                                    bs_zuordnung[24] = 'X';
                                    bs_zuordnung[25] = 'Y';
                                    bs_zuordnung[26] = 'Z';
                                    bs_zuordnung[27] = 'AA';
                                    bs_zuordnung[28] = 'AB';
                                    bs_zuordnung[29] = 'AC';
                                    bs_zuordnung[30] = 'AD';
                                    bs_zuordnung[31] = 'AE';
                                    bs_zuordnung[32] = 'AF';
                                    bs_zuordnung[33] = 'AG';
                                    bs_zuordnung[34] = 'AH';
                                    bs_zuordnung[35] = 'AI';
                                    
                                    var bname_zuordnung = new Array();
                                    bname_zuordnung['text'] = 'TEXTBLOCK';
                                    bname_zuordnung['h1'] = 'H1 - Überschrift';
                                    bname_zuordnung['h2'] = 'H2 - Unterüberschrift';
                                    bname_zuordnung['h3'] = 'H3 - Abschnittsüberschrift';
                                    bname_zuordnung['h4'] = 'H4 - Zwischenüberschrift';
                                    bname_zuordnung['zitat'] = 'ZITAT';
                                    bname_zuordnung['list'] = 'LISTE';
                                    
                                    //////////////////////////////////////////////////////////////
                                    // Box Optionen
                                    var op = $("#fn260 .boxfirst a.more_opt");
                                    var opn = $("#fn260 .boxfirst #exto");
                                    
                                    var extol1 = $("#extoL1");
                                    var extol2 = $("#extoL2");
                                    var extolA = $("#extoLA");
                                    
                                    $(op).off("click").on("click", function(){ 
                                        if($(opn).css("display") == "none") { 
                                            $(opn).show();
                                        }
                                    });
                                    $(opn).off("mouseleave").on("mouseleave", function(e){
                                        $(this).hide();
                                    });  
                                    
                                    $(opn).find(".extoR a:eq(0)").off().on("click", function(){
                                        $(opn).hide();
                                    });
                                    
                                    
                                    // Block-Type aendern
                                    $(opn).find("a#e_opt_type").off().on("click", function(){
                                        $(opn).off("mouseleave"); 
                                        $(extol2).hide();
                                        $(extolA).hide();
                                        $(extol1).show();
                                        
                                        $(extol1).find("a").off().on("click", function(){
                                            $(extol1).find("a.aktiv").removeClass("aktiv");
                                            $(this).addClass("aktiv");      
                                        });
                                        
                                        $(extol1).find("button:first").off().on("click", function(e){
                                            e.preventDefault();
                                            var new_type = split_id($("#extoL1").find("a.aktiv").attr("id"));
                                                                            
                                            sfrage_show('Wollen Sie den Elementtyp dieses Blocks wirklich &auml;ndern?');
                                            $("#sfrage button:last").on("click", function(){
                                                $.post('inc_documents.php', {
                                                    index: 'n263',
                                                    id : ausgewaehltes_dokument,
                                                    block : aktiv_block,
                                                    ibid: $("#fn260").find("#block_ibid").val(),
                                                    blockindex: $("#fn260").find("#blockindex").val(),
                                                    nt: new_type
                                                }, function(data){ logincheck(data);
                                                    $("#fn260 p.close").trigger("click");
                                                    zurfreigabe();
                                                    load_bloecke();
                                                }); 
                                            });     
                                        });
                                        
                                        $(extol1).find("button:last").off("click").on("click", function(e){
                                            e.preventDefault();
                                            $(opn).hide();   
                                        });
                                    });
                                    
                                    
                                    // Text auf mehrere Bloecke aufteilen
                                    $(opn).find("a#e_opt_aufteilen").off("click").on("click", function(){
                                        $(opn).hide();
                                        
                                        fenster({
                                            id: 'n260_split',
                                            blackscreen: '2',
                                            width: 550,
                                            cb: function(neww, ninhalt){
                                            
                                                $.post('inc_documents.php', {
                                                    index: 'n260_split_blocks',
                                                    id : ausgewaehltes_dokument,
                                                    block : aktiv_block,
                                                    ibid: $("#fn260 #block_ibid").val(),
                                                    blockindex: $("#fn260 #blockindex").val(),
                                                    html: $(textO).val()
                                                }, function(data){ 
                                                    logincheck(data);
                                                
                                                    $(ninhalt).html(data);
                                                    setFocus(neww);
                                                    
                                                    function setSelects(){
                                                        var used = false;
                                                        var nr = 0;
                                                        var cvalue = $(ninhalt).find("div.abox:first select").val();
                                                        
                                                        $(ninhalt).find("div.abox").each(function(sindex){
                                                            var tselect = $(this).find("select");
                                                            
                                                            $(tselect).removeClass("used").data('related', 0);
                                                                
                                                             if(sindex == 0 || used){
                                                                $(tselect).addClass("used");
                                                                
                                                                nr = $(this).data('nr'); 
                                                                cvalue = $(tselect).val();
                                                             } else {
                                                                $(tselect).data('related', nr).val(cvalue);
                                                             }
                                                                
                                                             if($(this).find("div.hr").hasClass("active")){
                                                                used = true;
                                                             } else {
                                                                used = false;
                                                             }
                                                        });
                                                    }
                                                    setSelects();
                                                    
                                                    $(ninhalt).find("div.hr span").off("click").on("click", function(){
                                                        var hr = $(this).parent("div.hr");
                                                        
                                                        if($(hr).hasClass("active")){
                                                            $(hr).removeClass("active");
                                                        } else {
                                                            $(hr).addClass("active");
                                                        }
                                                        
                                                        setSelects();
                                                    });
                                                    
                                                    
                                                    var lastchecks = new Array();
                                                    lastchecks['text'] = 1;
                                                    
                                                    $(ninhalt).find("select").off("change").on("change", function(){
                                                        if(!$(this).hasClass("used"))
                                                            return false;
                                                            
                                                        var newer = $(this).val();
                                                        var na = newer.split("_");
                                                        var count = parseInt(na[1]);
                                                       
                                                        if(lastchecks[na[0]] < count || !lastchecks[na[0]]){
                                                            lastchecks[na[0]] = count;
                                                            
                                                            var appendname = bname_zuordnung[na[0]]+' '+bs_zuordnung[(count + 1)];
                                                            var appendtext = '<option value="'+na[0]+'_'+(count + 1)+'">'+appendname+'</option>';
                                                            
                                                            $(ninhalt).find("select").children('option[value="'+newer+'"]', this).after(appendtext);
                                                        }
                                                        
                                                        setSelects();
                                                    });
                                                    
                                                    $(ninhalt).find("input.bs2").off().on("click", function(e){
                                                        e.preventDefault();
                                                        $(this).attr("disabled", true);
                                                                                        
                                                        $.post('inc_documents.php', {
                                                            index: 'n260_split_blocks_do',
                                                            id : ausgewaehltes_dokument,
                                                            block : aktiv_block,
                                                            ibid: $("#fn260").find("#block_ibid").val(),
                                                            blockindex: $("#fn260").find("#blockindex").val(),
                                                            f: $(ninhalt).find("form").serialize(),
                                                            text: $(textO).val()
                                                        }, function(data){ 
                                                            logincheck(data);
                                                            zurfreigabe();
                                                            load_bloecke();  
                                                            $(neww).find("p.close").trigger("click");  
                                                            $("#fn260 p.close").trigger("click");
                                                        });  
                                                    });
                                                    
                                                    $(ninhalt).find("input.bs1").off().on("click", function(e){
                                                        e.preventDefault();
                                                        $(neww).find("p.close").trigger("click");   
                                                    });
                                                });
                                            }
                                        });
                                    });
                                    
                                    
                                    // format start
                                    $(opn).find("a#e_opt_format").off().on("click", function(){
                                        $(opn).hide();
                                        
                                        fenster({
                                            id: 'n260_format',
                                            blackscreen: '2',
                                            width: 790,
                                            cb: function(neww, ninhalt){
                                            
                                                $.post('inc_documents.php', {
                                                    index: 'n260_format',
                                                    id : ausgewaehltes_dokument,
                                                    block : aktiv_block,
                                                    ibid: $("#fn260 #block_ibid").val(),
                                                    blockindex: $("#fn260 #blockindex").val(),
                                                }, function(data){ 
                                                    logincheck(data);
                                                
                                                    $(ninhalt).html(data);
                                                    setFocus(neww);
                                        
                                                    var ectr = $(ninhalt).find("#ecss_1 tr");
                                                    var ectrnot = $(ectr).not("tr.kopf");
                                                    var ectr0 = $(ninhalt).find("#ecss_0 tr");
                                                    var ectr0not = $(ectr0).not("tr.kopf");
                                                    
                                                    $(ninhalt).find("#mcss_0").off().on("click", function(){
                                                        if($(this).is(":checked")){
                                                            $(ectr0not).removeClass("inaktiv");
                                                        } else {
                                                            $(ectr0not).addClass("inaktiv");
                                                        }
                                                        set_button(neww);
                                                    });
                                                    $(ninhalt).find("#mcss_1").off().on("click", function(){
                                                        if($(this).is(":checked")){
                                                            $(ectrnot).removeClass("inaktiv");
                                                            $(ectrnot).find("td.a input").removeAttr("disabled");
                                                        } else {
                                                            $(ectrnot).addClass("inaktiv");
                                                            $(ectrnot).find("td.a input").attr("disabled", true);
                                                        }
                                                        set_button(neww);
                                                    });
                                                    
                                                    $(ectrnot).find("td.a input").off("click").on("click", function(){
                                                        var others = $(this).parent("td").siblings("td.b").find("input, select");
                                                        
                                                        if($(this).is(":checked")){
                                                            $(others).removeAttr("disabled");
                                                        } else {
                                                            $(others).attr("disabled", true);
                                                        }
                                                    });
                                                    
                                                    $(ninhalt).find(".colorSelector").each(function(){
                                                        var aktivcp = $(this);
                                                        if($(aktivcp).attr("id") == "cS1") var tcolor = $(ninhalt).find("input[name=color]").val();
                                                        if($(aktivcp).attr("id") == "cS2") var tcolor = $(ninhalt).find("input[name=bordercolor]").val(); 
                                                        if($(aktivcp).attr("id") == "cS3") var tcolor = $(ninhalt).find("input[name=bgcolor]").val();
                                                        
                                                        $(aktivcp).ColorPicker({
                                                        	color: tcolor,
                                                        	onShow: function (colpkr) { 
                                                        		$(colpkr).fadeIn(500);
                                                        		return false;
                                                        	},
                                                        	onHide: function (colpkr) {
                                                        		$(colpkr).fadeOut(500);
                                                        		return false;
                                                        	},
                                                        	onChange: function (hsb, hex, rgb) {
                                                        		$(aktivcp).css('backgroundColor', '#' + hex);
                                                                if($(aktivcp).attr("id") == "cS1") $(ninhalt).find("input[name=color]").val('#'+hex);
                                                                if($(aktivcp).attr("id") == "cS2") $(ninhalt).find("input[name=bordercolor]").val('#'+hex);
                                                                if($(aktivcp).attr("id") == "cS3") $(ninhalt).find("input[name=bgcolor]").val('#'+hex);
                                                        	}
                                                        });
                                                    });
                                                    
                                                    $(ninhalt).find("input.bs2").off().on("click", function(e){
                                                        e.preventDefault();
                                                        $(this).attr("disabled", true);
                                                                                        
                                                        $.post('inc_documents.php', {
                                                            index: 'n264',
                                                            id : ausgewaehltes_dokument,
                                                            block : aktiv_block,
                                                            ibid: $("#fn260 #block_ibid").val(),
                                                            blockindex: $("#fn260 #blockindex").val(),
                                                            f: $(ninhalt).find("form").serialize()
                                                        }, function(data){ 
                                                            logincheck(data);
                                                            zurfreigabe();
                                                            $(neww).find("p.close").trigger("click");   
                                                        });  
                                                    });
                                                    
                                                    $(ninhalt).find("input.bs1").off().on("click", function(e){
                                                        e.preventDefault();
                                                        $(neww).find("p.close").trigger("click");   
                                                    });
                                                });
                                            }
                                        });
                                    });
                                    
                                    $("#fn260 div.set_view a").off().on("click", function(){
                                        $(opn).find("a#e_opt_format").trigger("click");  
                                    });
                                    // format end
                                    
                                    
                                    $(opn).find("a#e_opt_copy").off().on("click", function(){
                                        $.post('inc_documents.php', {
                                            index: 'n262_clipboard',
                                            id : ausgewaehltes_dokument,
                                            block : aktiv_block
                                        }, function(data){ logincheck(data);
                                            show_popup();
                                            $(opn).find("a#e_opt_copy").fadeOut();
                                        }); 
                                    });
                                    
                                    $(opn).find("a#e_opt_del").off().on("click", function(){
                                        sfrage_show('Wollen Sie diesen Block wirklich entfernen? Der Inhalt geht verloren!');
                                        $("#sfrage button:last").on("click", function(){
                                            $.post('inc_documents.php', {
                                                index: 'n262',
                                                id : ausgewaehltes_dokument,
                                                block : aktiv_block,
                                                ibid: $("#fn260").find("#block_ibid").val(),
                                                blockindex: $("#fn260").find("#blockindex").val()
                                            }, function(data){ logincheck(data);
                                                $("#fn260 p.close").trigger("click");
                                                zurfreigabe();
                                                load_bloecke();
                                            }); 
                                        });    
                                    });
                                });
                            }
                        }
                    });
                });
           });
        }
    } 
    
    function tab_doc_4(){
        
        var vor = $("#doc4 #vorschau");
        
        $(vor).find("#suchwort button").off().on("click", function(e){
            e.preventDefault();
            laden($("#doc4"), true, true, '_white');
            
            $.get('inc_documents.php', {
                index : 'n251',
                a : 4,
                id : ausgewaehltes_dokument,
                suchwort : $(vor).find("#suchwort input").val()
            }, function(data){ 
                logincheck(data);
                laden($("#doc4"), false, false, '_white');
                
                $("#doc4").html(data);
                
                tab_doc_4();
            });
        });
                
    }
    
    var zp_dv = 0;
    function tab_doc_5(){
        var zeit = $("#doc5 #zeitsprung");
        
        $.get('inc_documents.php', {
            index : 'n259',
            id: ausgewaehltes_dokument,
            dv: zp_dv
        }, function(data){ 
            logincheck(data);
            var vcontent = $(zeit).find("#vcontent");
            
            if($(vcontent)[0]){
                var clone = $(vcontent).clone();
                var oleft = $(vcontent).offset().left;
                $(clone).css({
                    "top": ($(vcontent).offset().top - $(window).scrollTop()) + "px",
                    "left": oleft + "px"
                }).addClass("flytomoon");
            } 
            
            $(zeit).html(data);
            
            tooltipp();
            
            var zpL = $(zeit).find(".zpL");
            $(clone).appendTo(zpL);
                
            $(zeit).find("a.loadit").off().on("click", function(){
                if($(this).parent("td")[0])
                    $(this).replaceWith('<img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
                $(zeit).find(".short h2:first").html('<img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." />');
                
                zp_dv = $(this).attr("rel");
                tab_doc_5();
            });
            
            $(zeit).find(".laden button").off().on("click", function(){
                function schaltfrei(){
                    $.get('inc_documents.php', {
                        index : 'n203',
                        id: ausgewaehltes_dokument,
                        dv: $(zeit).find("#zp_alteversion").val()
                    }, function(data){ 
                        $(zeit).find(".laden").append('<div class="erfolg">Die gew&auml;hlte Version wurde erfolgreich zur Bearbeitung geladen</div>');
                        $(zeit).find(".laden button").fadeOut(3000, function() { $(this).remove() });
                    });
                }
                
                if($(zeit).find("#zp_gebwarnung").val() == 'true'){
                    sfrage_show('Wollen Sie diese Version wirklich zur Bearbeitung laden? Die &Auml;nderungen an der derzeitigen Version gehen dabei verloren!');
                    $("#sfrage button:last").on("click", schaltfrei);  
                } else {
                    schaltfrei();
                }
            });
            
            if($(clone)[0]){
                $(clone).animate({
                    "opacity": "0.0"
                }, 700, function(){
                    $(clone).remove();
                });
            }
        });        
    }
} 




//// NEUES DOKUMENT ANLEGEN
if($("#fn210")[0] && lastindex == 'n210') {
    newDocumentScript();
}


if($("#fn280")[0]){    
    var cont = $("#fn280 .inhalt");
    
    function load_zsb(){
        $.get('inc_documents.php', {
            index: 'n281'
        }, function(data){ logincheck(data);
            $(cont).find("#zsb").html(data);
            
            $(cont).find("#zsb a").off().on("click", function(){
                var del = $(this).attr("rel");
                
                sfrage_show('Wollen Sie diesen Zust&auml;ndigkeitsbereich wirklich entfernen?');
                $("#sfrage button:last").on("click", function(){
                    $.get('inc_documents.php', {
                        index: 'n282',
                        a: 'del', 
                        id: del
                    }, function(data){ logincheck(data);
                        load_zsb();
                    });
                });
            });
        });
    }
    load_zsb();
    
    $(cont).find("button.sub_zsb").off().on("click", function(e){
        e.preventDefault();
        
        var but = $(this);
        $(but).hide().after('<img src="images/loading.gif" class="ladebalken" />');
        
        var name = $(cont).find("input.name_zsb").val();
        $(cont).find("input.name_zsb").val('');
        
        $.get('inc_documents.php', {
            index: 'n282',
            a: 'new', 
            name: name
        }, function(data){ logincheck(data);
            load_zsb();
            $(but).show().siblings("img").remove();
            
            $(cont).find("input.name_zsb").focus();
        });
    });
}