var wmenu = null, dashboard_bg = null, dashboard = null;

$(function(){
    wmenu = $("#widget-menu");
    dashboard_bg = $("#widget-dashboard");
    dashboard = $(dashboard_bg).find("div.dashboard");
    
    getWidgetMenu(true);
});

    
function setWidgetMenuLinks(){
    $(wmenu).find("div.workspace a").off().on("click", function(){
        var gotop = $(window).height() * parseInt($(this).data('nr')); 
        $('html,body').animate({scrollTop: gotop}, 500);
    });
    
    $(wmenu).find("div.menu a").off().on("click", function(){
        startDashboard();  
    });
}

function getWidgetMenu(start){
    $.post('ajax-dashboard/widget-menu.php', function(data){
        $(wmenu).html(data); 
        setWidgetMenuLinks();

        calcWidgetMenu();
        $(window).on("resize", calcWidgetMenu);
        
        if(!start)
            return false;
        
        $(wmenu).animate({
            'left': '0px'
        }, 500);
    });
}

function calcWidgetMenu(){
    $(wmenu).css('margin-top', function(){
        if($(this).height() + 150 + ($(window).height() / 6) >= $(window).height())
            return -($(window).height() / 2 - 50);

        return -(($(this).height() / 2) + ($(window).height() / 6));
    });
}

function startDashboard(){
    $(dashboard_bg).show();
    $("body").addClass("no_overflow");
    
    $.post('ajax-dashboard/widget-dashboard.php', function(data){
        $(dashboard).html(data).animate({
            'left': '0px'
        }, 350);
        
        setDashboardMenu();
        getWidgetGrid();
    });
}

function closeDashboard(){
    $(dashboard).animate({
        'left': '-856px'
    }, 350, function(){
        $(dashboard).html('');
        $(dashboard_bg).hide();
    });
    
    $("body").removeClass("no_overflow");
}

function setDashboardMenu(){
    var dmenu = $(dashboard).find("div.menu div.inner");
        
    $(dmenu).css('margin-top', function(){
        return -($(this).height() / 2);
    });
    
    $(dmenu).find("a.close").off().on("click", closeDashboard);
    $(dmenu).find("a.sort").off().on("click", sortWidgets);
    $(dmenu).find("a.options").off().on("click", fks.openOptions);
    $(dmenu).find("a.clean").off().on("click", fks.openCleaner);
    $(dmenu).find("a.foresight").off().on("click", fks.openForesight);
    $(dmenu).find("a.profile").off().on("click", fks.openProfileSettings);
    $(dmenu).find("a.customize").off().on("click", fks.openCustomizing);
    $(dmenu).find("a.extensions").off().on("click", fks.openExtensionManager);
    
    $(dmenu).find("a").filter(".options, .clean, .foresight, .customize, .profile, .extensions, .frontend").on("click", closeDashboard);
}

function getWidgetGrid(){
    var widgetarea = $(dashboard).find("div.widgets");  
    $(widgetarea).html('<div class="loading"></div>');
    
    $.post('ajax-dashboard/widget-grid.php', function(data){ 
        $(widgetarea).html(data);
        
        $(widgetarea).find("div.widget").each(function(){
            var wid = $(this).data('id');
            var widget = $(this);
            
            $.post('ajax-dashboard/widget-body.php', {
                id: wid
            }, function(data){
                $(widget).html(data);
                
                $(widget).find("a.function").off().on("click", function(){
                    var call = eval($(this).data('function'));
                    var attr = $(this).data('attr');
                    
                    if($.isFunction(call))
                        call(attr);
                        
                    closeDashboard();
                });
            });
        });
    });
}

function sortWidgets(){ 
    fenster({
        width: 800,
        id: 'widget-sort',
        blackscreen: '',
        cb: function(nwin, content){
            $.post('ajax-dashboard/widget-sort.php', function(data){
                $(content).html(data);
                setFocus(nwin); 
                
                function sortWidgetsSave(){
                    $.post('ajax-dashboard/widget-sort-save.php', {
                        f: $(content).find("form#widget-sort").serialize()   
                    }, function(data){
                        getWidgetGrid();
                    });
                }           
                
                var wslider = $(content).find("div.slider");
                
                $(wslider).each(function(){
                    var cur_slider = $(this);
                    var cur_value = $(this).children("input[type=hidden]");
                    
                    $(cur_slider).slider({
                        value: $(cur_value).val(),
                        min: 1,
                        max: 99,
                        stop: function(event, ui) {
                            $(cur_value).val(ui.value);
                            
                            sortWidgetsSave();
                        },
                        start: function(event, ui){
                        }
                    });
                });
                
                $(content).find("div.standard a").off().on("click", function(){
                    $(wslider).each(function(){
                        $(this).children("input[type=hidden]").val(50);
                        $(this).slider("value", 50);
                    });
                     
                    sortWidgetsSave();
                });
            });  
        }
    });           
}