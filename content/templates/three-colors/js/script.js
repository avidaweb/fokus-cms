$(function(){
    function calcMenu(){
        var max_height = $("header a.logo").height();
        $("header .area").each(function(){
            if($(this).height() > max_height)
                max_height = $(this).height();
        }).height(max_height).siblings("a.logo").height(max_height);

        max_height = 0;
        $("footer .area").each(function(){
            if($(this).height() > max_height)
                max_height = $(this).height();
        }).height(max_height);
    }

    calcMenu();
    $(window).load(calcMenu);


    $("div.slot-big").children("div.slot-big-slider").bxSlider({
        auto: true,
        controls: true
    });


    $(".fks_gallery a").colorbox({
        maxWidth: ($(window).width() - 80),
        maxHeight: ($(window).height() - 80)
    });


    $("header a.shownav").click(function(e){
        e.preventDefault();
        $(this).siblings(".area").fadeToggle(800);
    });
})