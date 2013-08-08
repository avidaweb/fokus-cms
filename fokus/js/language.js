if($("#fn100")[0] || $("#fn110")[0] || $("#fn140")[0] || $("#fn143")[0]){
    $("#fn100, #fn110, #fn140, #fn143").remove();
    var newwindow = $('<span class="inc_structure" id="n100">Struktur verwalten</span>');
    neu(newwindow);
}
if($("#fn130")[0]){
    $("#fn130").remove();
    var newwindow = $('<span class="inc_structure" id="n130">Neue Struktur anlegen</span>');
    neu(newwindow);
}

if($("#fn200")[0] || $("#fn250")[0] || $("#fn253")[0] || $("#fn254")[0] || $("#fn260")[0]){
    $("#fn200, #fn250, #fn253, #fn254, #fn260").remove();
    var newwindow = $('<span id="n200" class="inc_documents">Dokumente verwalten</span>'); 
    neu(newwindow);
}
if($("#fn210")[0]){
    $("#fn210").remove();
    var newwindow = $('<span class="inc_documents" id="n210">Neues Dokument anlegen</span>');
    neu(newwindow);
}