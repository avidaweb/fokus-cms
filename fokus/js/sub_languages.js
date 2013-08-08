
if($("#fs310")[0] && lastindex == 's310'){
    
    $("#fs310 input[type=radio]").off("click").on("change", function(){  
        var c = $(this).siblings("a"); 
        sprache_wechseln($(c));
    });
    
    $("#fs310 a.sub_settings").off("click").on("click", function(){
        neu($(this));
    });
    
}