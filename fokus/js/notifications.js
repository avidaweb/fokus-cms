$(function(){
    var notifications = $("aside#notifications");
    var first_notification = 1;
    var notification_timeout = 15000;
    var notification_intervall = null;
    var original_title = document.title;
    
    function getNotifications(){
        $.get('notifications.php', {
            first: first_notification
        }, function(obj){  
            document.title = original_title;
            
            if(obj.changed == 0)
                return false;
                
            $(notifications).html(obj.result);
            initNotifications();
            document.title = $(notifications).find("div.note:first h5").text();
        }, "json");
        
        first_notification = 0;
    }
    
    function initNotifications(){
        $(notifications).find("div.note span").off().on("click", function(){
            var parent = $(this).parent("div.note");
            var delid = $(parent).data('id');
            
            $(parent).slideUp(400, function(){
                $(this).remove();
            
                $.get('notifications.php', {
                    task: 'delete',
                    delid: delid
                }, function(){
                    first_notification = 1;
                    getNotifications();
                });
            }); 
        });
        
        $(notifications).find("div.empty a").off().on("click", function(){
            $(notifications).html('');
            
            $.get('notifications.php', {
                task: 'delete-all'
            });
        });
        
        $(notifications).find("a.function").off().on("click", function(){
            var call = eval($(this).data('function'));
            var info = $(this).data('info');
            
            if($.isFunction(call))
                call(info);
                
            $(this).parents("div.note").children("span").trigger("click");
        });
    }
    
    notification_intervall = setInterval(getNotifications, notification_timeout);
    getNotifications();
});