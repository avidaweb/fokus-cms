
function init_personen(rel){
    var p_limit = 15;
    var p_rollen = '';
       
    if(rel == 1)
        var fn510 = $("#fn510 div.inhalt"); 
    else
        var fn510 = $("#fn515 div.inhalt");
        
    if(!$(fn510)[0])
        return false;
        
    var search_timeoutP = null;
    $(fn510).find("input[name=suche]").focus().val(p_search).off("keyup change").on("keyup change", function(){
        clearTimeout(search_timeoutP);
        var mesearch = $(this);
        search_timeoutP = setTimeout(function(){
            p_search = $(mesearch).val(); 
            per_verwalten_inhalt();
        }, 250);
    });
       
    $(fn510).find("a.rbutton").each(function(){
        var sib = $(this).siblings("div.opt");
        rbutton($(this), $(sib), 'einblenden', 'ausblenden');
    });
    
    $(fn510).find("button.inc_users").off("click").on("click", function(e){
        e.preventDefault();
        neu(this);
    });
    
    $(fn510).find("div.opt1 input").off().on("click", function(){
        init_personen(rel);
    });
    
    $(fn510).find("div.opt2 input").off().on("click", function(){
        if($(this).hasClass("first"))
            $(fn510).find("div.opt2 input").not(".first").removeAttr("checked");
        else
            $(fn510).find("div.opt2 input.first").removeAttr("checked");
        
        init_personen(rel);
    });

    function per_verwalten_inhalt(){
        var thead = $(fn510).find("table #headline");
        var loading = '<tr><td class="loading" colspan="'+($(thead).find("th").length)+'"><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>';
        $(thead).after(loading);
        
        p_opt = '';
        $(fn510).find("div.opt1 input").each(function(){
            if($(this).is(":checked"))
                p_opt += $(this).val()+'+';
        });
        
        p_rollen = '';
        $(fn510).find("div.opt2 input").each(function(){
            if($(this).is(":checked"))
                p_rollen += $(this).val()+'+';
        });
        
        $.get('inc_users.php', {
            index: 'n511',
            q: p_search,
            rel: rel,
            opt: p_opt,
            rollen: p_rollen,
            sortA: p_sortA,
            sortB: p_sortB,
            limit: p_limit
        }, function(data){ logincheck(data); 
            var table = $(fn510).find("#personen table#pers_auflistung");
            $(table).html(data);
            
            $(table).find("td a").off("click").on("click", function(){
                neu(this);
            });
            
            var thead = $(table).find("#headline");
            $(thead).find("th").disableSelection().off().on("click", function(){
                $(thead).find("th").removeClass("sort desc asc");
                $(this).addClass("sort");
                
                if($(this).data('sort') == p_lastsort){
                    $(this).addClass("desc");
                    p_lastsort = '';
                    p_sortB = 'DESC';
                } else {
                    $(this).addClass("asc");
                    p_lastsort = $(this).data('sort');
                    p_sortB = 'ASC';
                }
                    
                p_sortA = $(this).data('sort'); 
                
                per_verwalten_inhalt();
            });
            
            if($(table).find("tr.entry").length == 1){
                $(fn510).find("input[name=suche]").off("keypress").on("keypress", function(e){
                    if(e.keyCode == 13) {
                        e.preventDefault();
                        $(table).find("tr.entry a:first").trigger("click");
                    }
                });
            }
            
            var mr = $(table).find("td.more_results");
            if($(mr)[0]){                
                $(mr).children("a.next").off("click").on("click", function(){ 
                    p_limit += 15;
                    per_verwalten_inhalt();
                });
                
                $(mr).children("a.all").off("click").on("click", function(){ 
                    p_limit = 1000000000;
                    per_verwalten_inhalt();
                });
            }
        }); 
    }
    per_verwalten_inhalt();
    
    $(fn510).parents("table.fenster:first").find("p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        per_verwalten_inhalt();
    });
}

/// KUNDEN & MITARBEITER VERWALTEN
if(($("#fn510")[0] || $("#fn515")[0]) && (lastindex == "n510" || lastindex == "n515")) {
    init_personen(rel);
}

/// KUNDEN ODER MITARBEITER BEARBEITEN
if(($("#fn530")[0] || $("#fn535")[0]) && (lastindex == "n530" || lastindex == "n535")) {
    
    if(lastindex == "n530"){
        var fn530 = $("#fn530 div.inhalt");
        var fn510 = $("#fn510 div.inhalt");
        var trel = 1;
    } 
    else {
        var fn530 = $("#fn535 div.inhalt"); 
        var fn510 = $("#fn515 div.inhalt"); 
        var trel = 2;
    }
    
    $(fn530).find("#firma_no").change(function(){
        if($(this).is(':checked')){
            $(fn530).find("#firma").attr("disabled", "disabled");
            $(fn530).find("#position").attr("disabled", "disabled");
        }
    });
    $(fn530).find("#firma_yes").change(function(){
        if($(this).is(':checked')){
            $(fn530).find("#firma").removeAttr("disabled");
            $(fn530).find("#position").removeAttr("disabled");
        }
    });
    $(fn530).find("#firma").change(function(){
        var fid = $(this).val(); 
        $.get('inc_users.php', {
            index: 'n530A',
            firma: fid
        }, function(data){ 
            logincheck(data); 
            $(fn530).find(".durchwahl").html(data);
        });
    });
    
    $(fn530).find("#dw1A").change(function(){
        if($(this).is(':checked')){
            $(fn530).find("#tel_g").removeAttr("disabled");
            $(fn530).find("#tel_g_d").attr("disabled", "disabled");
        }
    });
    $(fn530).find("#dw1B").change(function(){
        if($(this).is(':checked')){
            $(fn530).find("#tel_g_d").removeAttr("disabled");
            $(fn530).find("#tel_g").attr("disabled", "disabled");
        }
    });
    
    $(fn530).find("#dw2A").change(function(){
        if($(this).is(':checked')){
            $(fn530).find("#fax").removeAttr("disabled");
            $(fn530).find("#fax_d").attr("disabled", "disabled");
        }
    });
    $(fn530).find("#dw2B").change(function(){
        if($(this).is(':checked')){
            $(fn530).find("#fax_d").removeAttr("disabled");
            $(fn530).find("#fax").attr("disabled", "disabled");
        }
    });
    
    $(fn530).find("#tags").autogrow();
    
    calcDatePicker(fn530);
    
    $(fn530).find("input.vonbis").off("click").on("click", function(){
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
    
    function choose_rolle(nid){    
        var index = "n534";
        var value = "inc_users";
        
        fenster({
            id: 'n534',
            blackscreen: '',
            width: 760,
            cb: function(neww, inhalt){
        
                $.post(value+'.php', {
                    index: index
                }, function(data){ 
                    logincheck(data);
                    
                    $(inhalt).html(data);
                    setFocus(neww);
                    
                    $(inhalt).find("#rolle a").off().on("click", function(){
                        var tid = $(this).attr("rel");
                        
                        $.post('inc_users.php', {
                            index: 'n534A',
                            rolle: tid,
                            benutzer: nid
                        }, function(data){ logincheck(data);
                            rolle_laden();
                            $(neww).find("p.close").trigger("click");
                        });
                    });
                });
            }
        });
    }
    
    $(fn530).find("#neue_rolle").off().on("click", function(e){
        e.preventDefault();
        choose_rolle($(fn530).find("#id").val());
    });
    
    function rolle_laden(){        
        $.get('inc_users.php', {
            index: 'n531',
            benutzer: $(fn530).find("#id").val()
        }, function(data){ logincheck(data);
            $(fn530).find("#trollen").html(data);
            
            $(fn530).find("#trollen a.del").off().on("click", function(){
                var tid = $(this).attr("rel");
                var eltern = $(this).parents("tr:first");
                
                sfrage_show('Wollen Sie diese Rolle wirklich l&ouml;schen?');
                $("#sfrage button:last").on("click", function(){
                    $.get('inc_users.php', {
                        index: 'n532',
                        id: tid
                    }, function(data){ logincheck(data);
                        $(eltern).remove();
                        if($(sb).css("display") == "none")
                            $(sb).show("blind", 500);
                    });
                });
            });
        });
    }
    
    rolle_laden();
    
    $(fn530).find("#new_passwort").off().on("change click", function(event){ 
        if($(this).is(":checked")){
            $(this).siblings("div").show("fast");
        } else {
            $(this).siblings("div").hide("fast");
        }
    });
    
    $(fn530).find("#pwt_klartext").off().on("click", function(){
        var elt = $(this).parents("div:first")[0];
        
        if($(this).is(":checked")){
            $(elt).find("input.pw").hide().val('');
            $(elt).find("input.pw_t").show().focus();
        } else {
            $(elt).find("input.pw").show().focus();
            $(elt).find("input.pw_t").hide().val('');
        }
    });
    
    $(fn530).find("#c_del_user").off().on("change click", function(event){
        if($(this).is(":checked")){
            $(fn530).find("#del_user").slideDown();
        } else {
            $(fn530).find("#del_user").slideUp();
        }
    });
    $(fn530).find("#del_user button").off().on("click", function(e){
        e.preventDefault();
        
        $.post('inc_users.php', {
            index: 'n561', 
            id: $(fn530).find("#id").val()
        }, function(data){
            $(fn530).parents(".fenster").find(".close").trigger("click"); 
            init_personen(1);
            init_personen(2);
        });
    });


    // avatar start
    var sbutton = $(fn530).find("button.avatar_select");
    if($(sbutton)[0]){
        $(sbutton).off().on("click", function(e){
            e.preventDefault();

            startImageSelect({
                blackscreen: '',
                selected: function(file){
                    if(!file)
                        return false;

                    $(fn530).find("input[name=avatar]").val(file.id);

                    $(fn530).find("img.avatar").attr("src", file.thumb100h).removeClass("hidden");
                    $(fn530).find("button.avatar_edit").show().data('file', file.id);
                }
            });
        });
    }

    var nbutton = $(fn530).find("button.avatar_new");
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

                    $(fn530).find("input[name=avatar]").val(file.id);

                    $(fn530).find("img.avatar").attr("src", file.thumbnail_url_h100).removeClass("hidden");
                    $(fn530).find("button.avatar_edit").show().data('file', file.id);

                    $(newwp).find("p.close").trigger("click");
                }
            });
        });
    }

    var ebutton = $(fn530).find("button.avatar_edit");
    if($(ebutton)[0]){
        $(ebutton).off("click").on("click", function(e){
            e.preventDefault();

            var the_file = $(this).data('file');

            startImageEdit({
                blackscreen: '',
                file: the_file,
                file_version: 0,
                callback: function(){
                    var old_src = $(fn530).find("img.avatar").attr("src")+'?random='+Math.random();
                    $(fn530).find("img.avatar").attr("src", old_src);
                }
            });
        });
    }
    // avatar finish

        
    var sb = $(fn530).find("div.box_save"); 
    $(fn530).find("input, textarea, select, option, a, button").on("change keyup click", function(){
        if($(sb).css("display") == "none")
            $(sb).show("blind", 500);
    });  
    
    $(sb).find("input.bs1").off("click").on("click", function(){ 
        $(fn530).parents("table:first").find("p.close").trigger("click");
    });
    
    $(sb).find("input.bs2").off().on("click", function(){
        var selfbutton = $(this);
        $(selfbutton).attr("disabled", "disabled");

        if($(fn530).find("input#id")[0])
            var edit = 1;
        else
            var edit = 0;
        
        var be = new Array();
        be['id'] = $(fn530).find("#id").val();
        be['eid'] = $(fn530).find("#eid").val();
        be['cmitarbeiter'] = $(fn530).find("#cmitarbeiter").is(":checked");
        be['ckunde'] = $(fn530).find("#ckunde").is(":checked"); 
        be['type'] = $(fn530).find("#type").val();
        be['anrede'] = $(fn530).find("#anrede").val();
        be['vorname'] = $(fn530).find("#vorname").val();
        be['nachname'] = $(fn530).find("#nachname").val();
        be['namenszusatz'] = $(fn530).find("#namenszusatz").val();
        be['str'] = $(fn530).find("#str").val();
        be['hn'] = $(fn530).find("#hn").val();
        be['plz'] = $(fn530).find("#plz").val();
        be['ort'] = $(fn530).find("#ort").val();
        be['land'] = $(fn530).find("#land").val();
        be['tel_p'] = $(fn530).find("#tel_p").val();
        be['tel_g'] = $(fn530).find("#tel_g").val();
        be['fax'] = $(fn530).find("#fax").val();
        be['tel_g_d'] = $(fn530).find("#tel_g_d").val();
        be['fax_d'] = $(fn530).find("#fax_d").val();
        be['mobil'] = $(fn530).find("#mobil").val();
        be['email'] = $(fn530).find("#email").val();
        be['firma'] = $(fn530).find("#firma").val();
        be['position'] = $(fn530).find("#position").val();
        be['tags'] = $(fn530).find("#tags").val();
        be['pw'] = $(fn530).find("#pw").val();
        be['von'] = $(fn530).find("#von").val();
        be['von_h'] = $(fn530).find("#von_h").val();
        be['von_m'] = $(fn530).find("#von_m").val();
        be['bis'] = $(fn530).find("#bis").val();
        be['bis_h'] = $(fn530).find("#bis_h").val();
        be['bis_m'] = $(fn530).find("#bis_m").val();
        be['status'] = $(fn530).find("#status").val();
        be['avatar'] = $(fn530).find("input[name=avatar]").val();
        
        if($(fn530).find("#pwt_klartext").is(":checked"))
            be['pw'] = $(fn530).find("#pw_t").val();
        
        if($(fn530).find("#dw1A").is(":checked")) be['tel_g_d'] = '';
        if($(fn530).find("#dw1B").is(":checked")) be['tel_g'] = '';
        if($(fn530).find("#dw2A").is(":checked")) be['fax_d'] = '';
        if($(fn530).find("#dw2B").is(":checked")) be['fax'] = '';
        
        $.post('inc_users.php', {
            index: 'n560',  
            edit: edit, 
            f: $(fn530).find("form").serialize(),
            id: be['id'],
            eid: be['eid'],
            cmitarbeiter: be['cmitarbeiter'],
            ckunde: be['ckunde'],
            status: be['status'],
            type: be['type'],
            avatar: be['avatar'],
            anrede: be['anrede'],
            vorname: be['vorname'],   
            nachname: be['nachname'],   
            namenszusatz: be['namenszusatz'],   
            str: be['str'],   
            hn: be['hn'],   
            plz: be['plz'],   
            ort: be['ort'],   
            land: be['land'],   
            tel_p: be['tel_p'],   
            tel_g: be['tel_g'],  
            fax: be['fax'],    
            tel_g_d: be['tel_g_d'],  
            fax_d: be['fax_d'],      
            mobil: be['mobil'], 
            email: be['email'],   
            firma: be['firma'],   
            position: be['position'],   
            tags: be['tags'],   
            pw: be['pw'],   
            von: be['von'], 
            von_h: be['von_h'], 
            von_m: be['von_m'],   
            bis: be['bis'],   
            bis_h: be['bis_h'],   
            bis_m: be['bis_m'],
            sendmail: ($(fn530).find("#sendemailbe").is(":checked")?'true':''),
            pindiv: ($(fn530).find("#pindiv").is(":checked")?'true':'')     
        }, function(data){ 
            logincheck(data);
            
            $(selfbutton).removeAttr("disabled");
            $(fn530).parents("table.fenster").find("p.close").trigger("click"); 
            
            if(!$(fn530).find("#person_user_info")[0] && data != ''){
                choose_rolle(data);    
            }
            
            init_personen(1);
            init_personen(2);
        });
        
    });      
}




function init_firmen(){
       
    var inhalt = $("#fn520").find(".inhalt");
        
    $(inhalt).find("#suche, #suche").off("keyup change").on("keyup change", function(){
        f_search = $(this).val(); 
        $(inhalt).find("#suche, #suche").val(f_search);
        firmen_verwalten_inhalt();
    });
    
    $(inhalt).find("a.more_opt").off().on("click", function(){
        var so = $(inhalt).find(".opt");
        if($(so).css("display") == "none") {
            $(so).slideDown(300);
            $(this).html("Optionen ausblenden").css('background', '#fff url(images/rpfeil_oben.png) no-repeat 2px center');
        } else {
            $(so).slideUp(300);
            $(this).html("Optionen einblenden").css('background', '#fff url(images/rpfeil_unten.png) no-repeat 2px center');
        }
    });
    
    $(inhalt).find(".re button").off("click").on("click", function(event){
        event.stopPropagation();
        rel = 0;
        neu(this);
    });
    
    f_opt = '';
    $(inhalt).find(".opt input").each(function(){
        if($(this).is(":checked"))
            f_opt += $(this).val()+'+';
    });
    
    $(inhalt).find(".opt button").off("click").on("click", function(){
        init_firmen();
    });

    function firmen_verwalten_inhalt(){
        $.get('inc_users.php', {
            index: 'n521',
            q: f_search,
            opt: f_opt,
            sortA: f_sortA,
            sortB: f_sortB
        }, function(data){ 
            logincheck(data); 
            
            var table = $(inhalt).find("#firmen table#firmen_auflistung");
            $(table).html(data);
            
            $(table).find("td a").off("click").on("click", function(){
                neu(this);
            });
            
            if(!f_sortA){ 
                f_sortA = $(table).find("#headline td:first").attr("id").replace('ddf_', ''); 
                f_sortB = "ASC";
                firmen_verwalten_inhalt();
            }
            
            $(table).find("#headline td").off("click").on("click", function(){ 
                var sort = $(this).attr("id").replace('ddd_', ''); 
                if(sort == f_sortA) f_sortC ++;
                else f_sortC = 0;
                f_sortB = (f_sortC % 2 == 0?"ASC":"DESC");
                f_sortA = sort; 
                firmen_verwalten_inhalt();
            });
        }); 
    }
    firmen_verwalten_inhalt();
}

/// FIRMEN VERWALTEN
if($("#fn520")[0] && lastindex == "n520") {
    init_firmen(rel);
}

/// FIRMEN BEARBEITEN
if($("#fn570")[0] && lastindex == "n570") {
    
    var inhalt = $("#fn570").find(".inhalt");
    
    $(inhalt).find("#tags").autogrow();
        
    var sb = $(inhalt).find(".box_save"); 
    $(inhalt).find("input, textarea, select, option").on("change keyup", function(e){
        if($(sb).css("display") == "none")
            $(sb).show("blind", 500);
    });   
    
    $(sb).find("input:last").off().on("click", function(e){
        var selfbutton = $(this);
        $(selfbutton).attr("disabled", "disabled");
        e.preventDefault();
        
        if($(inhalt).find("input#id")[0])
            var edit = 1;
        else
            var edit = 0; 
        
        var be = $(inhalt).find("form").serialize(); 
        
        $.post('inc_users.php', {
            index: 'n580',  
            edit: edit, 
            v: be         
        }, function(data){ 
            logincheck(data);
            $(selfbutton).removeAttr("disabled");
            $(inhalt).parents(".fenster").remove(); 
            
            if(data) alert(data);
            
            init_firmen();
        });
    });
}




/// ROLLEN VERWALTEN
if($("#fn540")[0] && lastindex == "n540") { 
    rollen();
}

/// ROLLE BEARBEITEN
if($("#fn550")[0] && lastindex == "n550") { 
    rolle_bearbeiten();
}

function rollen(){
    var inhalt = $("#fn540 .inhalt"); 
    
    $(inhalt).find("button.inc_users").off("click").on("click", function(){ 
        neu($(this));
    });
    
    rollen_laden();
}

function rollen_laden(){
    var inhalt = $("#fn540 div.inhalt");
    var rolleO = $(inhalt).find("table#rolle"); 
    
    $.get('inc_users.php', {
        index: 'n541'
    }, function(data){ logincheck(data);
        $(rolleO).html(data);
        
        $(rolleO).find("a.inc_users").off().on("click", function(){
            neu($(this));
        });
        
        $(rolleO).sortable({
            items: 'tr.csort',
            appendTo: '#rolle',
            handle: 'img',
            axis: 'y',
            containment: 'parent',
            stop: function(event, ui) {       
                $.post('inc_users.php', {
                    index: 'n541_sort',
                    sort: $(this).sortable("serialize")
                });
            }                       
        });
        
        $(rolleO).find("a.del").off().on("click", function(){
            var tid = $(this).attr("rel");
            var eltern = $(this).parents("tr:first");
            sfrage_show('Wollen Sie diese Rolle wirklich l&ouml;schen?');
            
            $("#sfrage button:last").on("click", function(){
                $.post('inc_users.php', {
                    index: 'n542',
                    id: tid
                }, function(data){ logincheck(data);
                    $(eltern).remove();
                });
            });
        });
    });
}


function rolle_bearbeiten(){
    var mfen = $("#fn550");
    var inhalt = $("#fn550 div.inhalt"); 
    var sb = $(inhalt).find("div.box_save");
    var strdoks = $(inhalt).find("#Rstruk_doks");
    
    $(inhalt).find("div.area h2 input").each(function(){
        if(!$(this).is(":checked")){ 
            var par = $(this).parents("div.area:first").find("article");
            $(par).addClass("disabled").find("input").attr("disabled", true);
        }
    }).off().on("click", function(){
        var par = $(this).parents("div.area:first").find("article");
        
        if($(this).is(":checked")){
            $(par).removeClass("disabled").find("input").attr("disabled", false);    
        } else {
            $(par).addClass("disabled").find("input").attr("disabled", true).attr("checked", false).trigger("click");
        }
    });
    
    $(inhalt).find("div.lmore").children("input[type=checkbox]").off().on("click", function(){
        var sc = $(this).siblings("div.more");
        
        if($(this).is(":checked")){
            slideDownSave(sc, 300, mfen, sb);
        } else {
            slideUpSave(sc, 300, mfen, sb);
            $(sc).find("input[type=checkbox]").removeAttr("checked");
        }
    });
    
    $(inhalt).find("input.has_standards").off("click").on("click", function(){
        var sib = $(this).parent("p").next("p");
        
        if($(this).is(":checked")){
            $(sib).removeClass("not_active").children("input").removeAttr("disabled");
        } else {
            $(sib).addClass("not_active").children("input").attr("disabled", true);
        }
    });
    
    $(inhalt).find("input#r_str_ele").off("click").on("click", function(){
        var sib = $(this).parent("p").next("div.mopt");
        
        if($(this).is(":checked")){
            $(sib).removeClass("not_active").children("input").removeAttr("disabled");
        } else {
            $(sib).addClass("not_active").children("input").attr("disabled", true).attr("checked", false).trigger("click");
        }
    });
    
    $(inhalt).find("input#r_dok_publ").off("click").on("click", function(){
        var sib = $(this).parent("p").siblings("p.r_dok_publ_all");
        
        if($(this).is(":checked")){
            $(sib).slideDown();
        } else {
            $(sib).slideUp();
        }
    });
    
    function fehler_doks(){
        $(strdoks).sortable({
            items: 'div.struk_dok',
            stop: function(){
                if($(sb).css("display") == "none") 
                    $(sb).show("blind", 500);
            }
        });
                            
        $(inhalt).find(".doc_delete").off("click").on("click", function(event){
            event.stopPropagation();
            var pp = $(this).parents(".struk_dok");
            var del = $(pp).attr("id").split("_");
            $(strdoks).html(waiting); 
            
            $.post('inc_users.php', {
                index: 'n550F',
                del: del[1],
                rel: $(mfen).find("#r_id").val()
            }, function(data){ logincheck(data); 
                $(strdoks).html(data);
                fehler_doks();
            });
        });
        
        var doc_offen = false;
        $(strdoks).find(".options").off('click').on('click', function(event) {
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
        $(strdoks).find("button").off().on("click", function(e){ 
            e.stopPropagation();
            e.preventDefault();
            var buttonclass = $(this).attr("class");
            
            fenster({
                id: 'n554',
                width: 964,
                blackscreen: '',
                cb: function(nwin, ninhalt){
                    $.post('inc_documents.php', {
                        index: 'n200',
                        rel: 0
                    }, function(data){ logincheck(data);
                    
                        $(ninhalt).html(data);
                        setFocus(nwin);
                        
                        /// EXAKT WIE BEI DEN DOKUMENTEN O'REALLY            
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
                                        $(strdoks).html(waiting);
                                        
                                        if($(sb).css("display") == "none") 
                                            $(sb).show("blind", 500);
                                        
                                        $.post('inc_users.php', {
                                            index: 'n550F',
                                            newe: selected,
                                            rel: $(mfen).find("#r_id").val()
                                        }, function(data){ logincheck(data); 
                                            $(nwin).find("p.close").trigger("click");
                                            
                                            $(strdoks).html(data);
                                            fehler_doks();
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
    
    $.post('inc_users.php', {
        index: 'n550F',
        rel: $(mfen).find("#r_id").val()
    }, function(data){ logincheck(data);
        $(strdoks).html(data);
        fehler_doks();
    });
                            
    
    $(inhalt).find("input").on("keyup change", function(){
        if($(sb).css("display") == "none") 
            $(sb).show("blind", 500);
    });
    
    $(sb).find("button:first").off().on("click", function(e){
        e.preventDefault();
        $(mfen).find("p.close").trigger("click");
    });
    
    $(sb).find("button:last").off().on("click", function(e){ 
        e.preventDefault();
        var mebut = $(this);
        
        function send_rolle(){
            $(mebut).attr("disabled", true);
            
            $.post('inc_users.php', {
                index: 'n551',
                values: $(inhalt).find("form").serialize(),
                sort: $(strdoks).sortable("serialize")
            }, function(data){ logincheck(data); 
                rollen_laden();
                
                $(mfen).find("p.close").trigger("click");
            });
        }
        
        var check_error = false;
        
        $(inhalt).find("div.area h2 input").filter(":checked").each(function(){
            var relinpt = $(this).parent("h2").siblings("article").children("div, p").children("input[type=checkbox]").filter(":checked");
            if(!$(relinpt)[0])
                check_error = true;
        });
        
        if(check_error){
            sfrage_show('Sie haben beim Bearbeiten der Rolle Hauptbereiche aktiviert, für die Sie keine weiteren Rechte festgelegt haben. Sollen die entsprechenden Hauptbereiche einfach deaktiviert werden?');
            $("#sfrage button:last").on("click", function(){
                $(inhalt).find("div.area h2 input").filter(":checked").each(function(){
                    var meinp = $(this);
                    var relinpt = $(this).parent("h2").siblings("article").children("div, p").children("input[type=checkbox]").filter(":checked");
                    if(!$(relinpt)[0])
                        $(meinp).removeAttr("checked");
                });    
                
                send_rolle();
            });
        } else {        
            send_rolle();
        }
    });
}



/// BENUTZERPROFIL
if($("#fn590")[0] && lastindex == "n590") { 
    $("#fn590 p.linkz a").off("click").on("click", function(e){
        e.preventDefault();
        
        neu(this);
    });
}