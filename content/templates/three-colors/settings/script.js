var apptc = $("#app-three-colors");

if($(apptc)[0]){
    $(apptc).find("button.logo_select").off().on("click", function(e){
        e.preventDefault();

        fks.openImageSelection({
            popup: 1,
            selected: function(file){
                if(!file)
                    return false;

                $(apptc).find("input[name=logo]").val(file.id);

                $(apptc).find("img.logo").attr("src", file.thumb200).removeClass("hidden");
                $(apptc).find("button.logo_edit").show().data('file', file.id);
            }
        });
    });

    $(apptc).find("button.logo_new").off().on("click", function(e){
        e.preventDefault();

        fks.openImageUpload({
            dir: 0,
            images: true,
            popup: 1,
            hide_edit: true,
            limit: 1,
            refresh: function(newwp, data){
                var file = data[0];
                if(!file)
                    return false;

                $(apptc).find("input[name=logo]").val(file.id);

                $(apptc).find("img.logo").attr("src", file.thumbnail_url_200).removeClass("hidden");
                $(apptc).find("button.logo_edit").show().data('file', file.id);

                $(newwp).find("p.close").trigger("click");
            }
        });
    });

    $(apptc).find("button.logo_edit").off("click").on("click", function(e){
        e.preventDefault();

        var the_file = $(this).data('file');

        fks.openImageEdit({
            popup: 1,
            file: the_file,
            file_version: 0,
            callback: function(){
                var old_src = $(apptc).find("img.logo").attr("src")+'?random='+Math.random();
                $(apptc).find("img.logo").attr("src", old_src);
            }
        });
    });
}