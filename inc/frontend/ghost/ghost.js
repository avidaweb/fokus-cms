$(function(){
    var ckconfig_ghost = {
        toolbar_Full: [['Bold', 'Italic'], ['Undo', 'Redo']],
        enterMode: CKEDITOR.ENTER_BR,
        shiftEnterMode: CKEDITOR.ENTER_BR,
        forceEnterMode: true,
        forcePasteAsPlainText: true,
        extraPlugins : 'autogrow',
        removePlugins : 'resize,contextmenu,link',
        autoGrow_minHeight: 5,
        autoGrow_bottomSpace: 1,
        height: 15,
        autoGrow_onStartup: true,
        startupFocus: true,
        language: 'de',
        contentsLanguage: 'de',
        defaultLanguage: 'de',
        contentsCss: '',
        sharedSpaces:
		{
			top : 'fks_ghost_topSpace',
			bottom : 'fks_ghost_bottomSpace'
		}
    };
 
    var editable = $(".ghost_editable").find("h1, h2, h3, h4, h5, p, blockquote, span").filter("[rel=editable]");
   
    $("body").append('<div id="fks_ghost"></div><div id="fks_ghost_ok"></div>');  
    
    $.get(ghost_ordner+'inc/frontend/ghost/ghost.php', {
        url: ghost_url
    }, function(data){ 
        $("#fks_ghost").html(data); 
        
        $(editable).each(function(){ 
            var shadow = $('<div class="fks_ghost_shadow" data-sid="'+$(this).data('id')+'" data-dkname="'+$(this).data('dkname')+'" data-ibid="'+$(this).data('ibid')+'" data-blockindex="'+$(this).data('blockindex')+'" data-type="'+$(this).data('type')+'"><textarea></textarea></div>');
            $(this).after(shadow);
            
            var ta = $(shadow).children("textarea");
            var old_html = $(this).html();
            $(ta).html(old_html);
            
            var cssp = {};
            cssp.fontsize = $(this).css("font-size");
            cssp.fontstretch = $(this).css("font-stretch");
            cssp.fontfamily = $(this).css("font-family");
            cssp.fontstyle = $(this).css("font-style");
            cssp.fontvariant = $(this).css("font-variant");
            cssp.fontweight = $(this).css("font-weight");
            cssp.textalign = $(this).css("text-align");
            cssp.textdecoration = $(this).css("text-decoration");
            cssp.textjustify = $(this).css("text-justify");
            cssp.texttransform = $(this).css("text-transform");
            cssp.textwrap = $(this).css("text-wrap");
            cssp.color = $(this).css("color");
            
            $(this).hide();
            
            $(ta).each(function(){
                var meeditor = $(this);
                
                $(meeditor).ckeditor(function() { 
                    ghostckeditor = $(ta).ckeditorGet();
                    
                    this.on('change', function(e) {   $(meeditor).addClass("has_changed");    });
                    this.on('key', function(e) {   $(meeditor).addClass("has_changed");    });
                    this.on('setData', function(e) {   $(meeditor).addClass("has_changed");    });
                    this.on('insertHtml', function(e) {   $(meeditor).addClass("has_changed");    });
                    this.getCommand('undo').on('afterUndo', function(e) {   $(meeditor).addClass("has_changed");    });
                    this.getCommand('redo').on('afterRedo', function(e) {   $(meeditor).addClass("has_changed");    });
                
                    $(shadow).find("span.cke_skin_kama").css("border", ghost_border);
                     
                    $(shadow).find("iframe").contents().find("html body").css({
                        'font-size': cssp.fontsize,
                        'font-stretch': cssp.fontstretch,
                        'font-family': cssp.fontfamily,
                        'font-style': cssp.fontstyle,
                        'font-variant': cssp.fontvariant,
                        'font-weight': cssp.fontweight,
                        'text-align': cssp.textalign,
                        'text-decoration': cssp.textdecoration,
                        'text-justify': cssp.textjustify,
                        'text-transform': cssp.texttransform,
                        'text-wrap': cssp.textwrap,
                        'color': cssp.color
                    });
                }, ckconfig_ghost);
            });
        });
        
        
        var tbar = $("#fks_ghost div.fks_right p");
        var ghost_ok = $("#fks_ghost_ok");
        var save_timer = null;
        
        $(tbar).find("button.close").off().on("click", function(e){
            e.preventDefault();
            $(this).attr("disabled", "disabled");
            
            $.get(ghost_ordner+'inc/frontend/ghost/task.php', {
                task: 'close'
            }, function(data){
                window.location.reload();
            });
        });
        
        var tbuttons = $(tbar).find("button.save");
        $(tbuttons).off().on("click", function(e){
            e.preventDefault();
            clearTimeout(save_timer);
            
            var tbut = $(this);
            var task = $(this).data('task');
            var requests = 0;
            
            var changed = $("div.fks_ghost_shadow textarea.has_changed");
            var insgesamt = $(changed).length;
            
            var d_ids = new Array();
            
            $(changed).each(function(){
                var me_val = $(this).val();
                var me_parent = $(this).parent("div.fks_ghost_shadow");
                var me_id = $(me_parent).data('sid');
                var me_dkname = $(me_parent).data('dkname');
                var me_dkname = $(me_parent).data('dkname');
                var me_ibid = $(me_parent).data('ibid');
                var me_blockindex = $(me_parent).data('blockindex');
                 
                $(tbuttons).attr("disabled", "disabled");
                $(ghost_ok).html('Wird gespeichert...').removeClass("ok").addClass("wait").slideDown();
                
                var teditable = $(this).parents(".ghost_editable:first");
                var did = $(teditable).data('did');
                var dvid = $(teditable).data('dvid');
                var dklasse = $(teditable).data('klasse');
                
                if(dklasse && me_dkname)
                    me_id = me_dkname;
                
                if(did > 0){ 
                    $.post(ghost_ordner+'fokus/inc_documents.php', {
                        index: 'n261qe',
                        id: did,
                        block: me_id,
                        ibid: me_ibid,
                        blockindex: me_blockindex,
                        html: me_val
                    }, function(data){
                        requests ++; 
                        
                        if(!d_ids.in_array(did)){
                            d_ids.push(did);
                            
                            if(task == 'wait'){
                                $.get(ghost_ordner+'fokus/inc_documents.php', {
                                    index: 'n250f',
                                    id: did,
                                    a: 1
                                }, function(data){
                                    if(requests >= insgesamt){
                                        $(tbuttons).removeAttr("disabled");
                                        ok_banner(d_ids.length+' Dokument'+(d_ids.length != 1?'e':'')+' erfolgreich gespeichert und zur Freigabe vorgelegt');
                                    }
                                });
                            } else if(task == 'free'){
                                $.get(ghost_ordner+'fokus/inc_documents.php', {
                                    index: 'n202',
                                    v: did+'_'+dvid,
                                    a: 1
                                }, function(data){
                                    if(requests >= insgesamt){
                                        $(tbuttons).removeAttr("disabled");
                                        ok_banner(d_ids.length+' Dokument'+(d_ids.length != 1?'e':'')+' erfolgreich gespeichert und direkt freigegeben');
                                        $("div.fks_ghost_shadow textarea").removeClass("has_changed");
                                    }
                                });
                            } 
                        }
                        
                        if(task == 'save' && requests >= insgesamt){
                            $(tbuttons).removeAttr("disabled");
                            ok_banner(d_ids.length+' Dokument'+(d_ids.length != 1?'e':'')+' erfolgreich gespeichert');
                        }
                    });
                }
            }); 
            
            if(!$(changed)[0]){ 
                $(ghost_ok).html('Es wurden keine Änderungen festgestellt').removeClass("ok").addClass("wait").slideDown();
                save_timer = setTimeout(function(){
                    $(ghost_ok).slideUp();
                }, 2500);
            }
        });
        
        function ok_banner(etext){
            $(ghost_ok).html(etext).removeClass("wait").addClass("ok");
            save_timer = setTimeout(function(){
                $(ghost_ok).slideUp();
            }, 2500);            
        }
    });
});



Array.prototype.in_array = function(needle){
    for(var i=0; i<this.length; i++){
        if(needle===this[i])
            return true
    }
    return false;
}