
function get_elements(mwin, type){ 
    var limit = 10;
    
    var realspalten = 3;
    if(type == 'suche')
        realspalten = 2;
    else if(type == 'papierkorb')
        realspalten = 4;
   
    var qi = $(mwin).find("#search_box input");
    
    var lastuse = $(mwin).find("#last_use");
    var loading = $(lastuse).children("div.loading");
    var dktable = $(lastuse).children("table");  
     
    function reload_elements(){ 
        $(loading).show(); 
        
        if(type == 'suche')
            $(lastuse).slideDown();                                               
        
        $.post('sub_last.php', {
            index: 's120_get',
            papierkorb: (type == 'papierkorb'?'true':''),
            suche: (type == 'suche'?'true':''),
            limit: limit,
            q: $(qi).val(),
            realspalten: realspalten
        }, function(data){
            logincheck(data);
            $(loading).hide();
            
            $(dktable).html(data);
            
            if(type != 'papierkorb'){
                $(dktable).find("td.goto a").not(".selement").off().on("click", function(){
                    neu($(this));
                });
                
                $(dktable).find("td.goto a.selement").off().on("click", function(){
                    open_strukturelement($(this).data('id'));
                });
            } else {    
                $(dktable).find("td.goto a").each(function(){
                    var mecont = $(this).html();
                    $(this).replaceWith(mecont);
                });
                
                $(dktable).find("td.reset a").off().on("click", function(){
                    var matr = $(this).data('id');
                    var pa = $(this).parent("td");
                    
                    $.get('sub_last.php', {
                        index: 's121',
                        atr: matr
                    }, function(data){
                        logincheck(data);
                        $(pa).html("wiederhergestellt");
                    });
                    
                    $(pa).html(". . .");
                });
            }
    
            var mr = $(dktable).find("td.more_results");
            if($(mr)[0]){
                $(mr).children("a.next").off("click").on("click", function(){ 
                    limit += 10;
                    reload_elements();
                });
                
                $(mr).children("a.all").off("click").on("click", function(){ 
                    limit = 1000000000;
                    reload_elements();
                });
            }
        });
    }
    
    $(mwin).find("p.move a.reload").off().on("click", function(ev){
        ev.preventDefault();
        reload_elements();
    }); 
    
    if(type != 'suche')
    {
        reload_elements();
    }
    else
    {
        var search_timeout_zuve = null;
        $(qi).focus().off("keyup change").on("keyup change", function(){
            clearTimeout(search_timeout_zuve);
            search_timeout_zuve = setTimeout(function(){
                reload_elements();
            }, 300);
        });
    }
}


if($("#fs110")[0] && lastindex == 's110') { 
    get_elements($("#fs110"), 'suche');
}

if($("#fs115")[0] && lastindex == 's115') { 
    get_elements($("#fs115"), 'papierkorb');
}

if($("#fs120")[0] && lastindex == 's120') { 
    get_elements($("#fs120"), 'zuve');
} 


if($("#fs480")[0] && lastindex == 's480') { 
    var pinnwandpn = $("#fs480 #pnotizen");
    var editapn = $(pinnwandpn).find("textarea");  
    var pnote_ckeditor = null;
    
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
    
    var sbpn = $(pinnwandpn).find("div.box_save").show();
    var savepn = $(sbpn).find("input.bs2");
    var stimerp = null;
    
    $(sbpn).find("input.bs1").off().on("click", function(){
        if(pnote_ckeditor != null)
            pnote_ckeditor.destroy();
        $("#fs480 p.close").trigger("click");
    });
    
    $(savepn).off().on("click", function(){
        clearTimeout(stimerp);
        
        $(savepn).attr("disabled", "disabled");
        
        $.post('sub_last.php', {
            index: 's481',
            t: $(editapn).val()
        }, function(data){
            logincheck(data);
            
            $(savepn).val('gespeichert');
        });
    });
    
    function activate_button_pn(){
        clearTimeout(stimerp);
        $(savepn).val('speichern').removeAttr("disabled");  
        
        stimerp = setTimeout(function(){
            $(savepn).trigger("click");
        }, 3000);
    }
    
    $(editapn).ckeditor(function() { 
        pnote_ckeditor = $(editapn).ckeditorGet(); 
        
        pnote_ckeditor.on('change', activate_button_pn);
        pnote_ckeditor.on('key', activate_button_pn);
        pnote_ckeditor.on('setData', activate_button_pn);
        pnote_ckeditor.on('insertHtml', activate_button_pn);
        pnote_ckeditor.getCommand('undo').on('afterUndo', activate_button_pn);
        pnote_ckeditor.getCommand('redo').on('afterRedo', activate_button_pn);
    }, nckconfig); 
    
}