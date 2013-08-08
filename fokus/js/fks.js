var fks = {
    openMessages: function(){
        neu($('<a id="n640" class="inc_communication" rel="0"></a>'));  
    },
    openComments: function(){
        neu($('<a id="n631" class="inc_communication" rel="comments"></a>'));  
    },
    openWall: function(){
        neu($('<a id="n660" class="inc_communication" rel="0"></a>'));  
    },
    openDocument: function(id){
        neu($('<a class="inc_documents" id="n250" rel="'+id+'"></a>'));  
    },
    openRecords: function(id){
        neu($('<a id="n631" class="inc_communication" rel="formular_'+id+'"></a>'));  
    },
    openNotes: function(){
        neu($('<a id="s480" class="sub_last" rel="0"></a>'));  
    },
    openSessionInfo: function(){
        neu($('<a id="s440" class="sub_info" rel="0"></a>'));  
    },
    openOptions: function(){
        neu($('<a id="s420" class="sub_settings" rel="0"></a>'));  
    },
    openCleaner: function(){
        neu($('<a id="s490" class="sub_settings" rel="0"></a>'));  
    },
    openProfileSettings: function(){
        neu($('<a id="s510" class="sub_indiv" rel="0"></a>'));  
    },
    openCustomizing: function(){
        neu($('<a id="s500" class="sub_indiv" rel="0"></a>'));  
    },
    openForesight: function(){
        neu($('<a id="s450" class="sub_foresight" rel="0"></a>')); 
    },
    openExtensionManager: function(){
        neu($('<a id="extensions" class="sub_settings" rel="0"></a>'));
    },
    openImageSelection: function(options){
        startImageSelect(options);
    },
    openImageUpload: function(options){
        startUpload(options);
    },
    openImageEdit: function(options){
        startImageEdit(options);
    },
    openLinkPicker: function(options){
        options.url_only = false;
        fck_link_ext(options);
    },
    openUrlPicker: function(options){
        options.url_only = true;
        fck_link_ext(options);
    },
    openApp: function(id){
        openAppWindow(id, 0);  
    },
    newWindow: function(options){
        if(!options.id) { alert('Error: ID not defined'); return false; }
        if(!options.action) { alert('Error: AJAX Action Hook not defined'); return false; }
        if(!options.callback || !options.done) { alert('Error: Callback not defined'); return false; }
        
        if(!options.width) options.width = 600;
        if(!options.data) options.data = {};
        
        options.popup = parseInt(options.popup);
        if(!options.popup) options.popup = 'none';
        if(options.popup == 1) options.popup = '';
        
        fenster({
            id: '-'+options.id,
            width: options.width,
            blackscreen: options.popup+'',
            cb: function(nwin, ncontent){
                
                $.ajax({
                    type: "POST",
                    url: fks.getAjaxUrl(options.action),
                    data: options.data
                }).done(function(data){ 
                    $(ncontent).html(data);

                    if($.isFunction(options.callback))
                        options.callback(nwin, ncontent);
                    if($.isFunction(options.done))
                        options.done(nwin, ncontent);
                }).fail(function(req, msg){
                    if($.isFunction(options.fail))
                        options.fail(nwin, ncontent);
                    else
                        alert('AJAX error: '+msg);
                });   
            }
        });
    },
    getAjaxUrl: function(action){
        return ajax_url+action+'/';
    }
}