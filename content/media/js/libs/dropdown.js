//$(function(){
//
//    $("ul.dropdown li").hover(function(){
//
//        $(this).addClass("hover");
//        $('ul:first',this).css('visibility', 'visible');
//
//    }, function(){
//        var right_this = this;
//        setTimeout(function () {
//            $(right_this).removeClass("hover");
//            $('ul:first',right_this).css('visibility', 'hidden');
//        }, 250);
//
//    });
//
//    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");
//
//});


$(document).ready(function () {
    $(".hoverli").hover(
        function () {
            $(this).hover(function() {
            }, function(){
                $(this).find("ul.sub_menu").slideUp('slow'); //When the mouse hovers out of the subnav, move it back up
            });

            $(this).find('ul.sub_menu').slideDown('fast');
        },
        function () {
            $(this).find('ul.sub_menu').slideUp('fast');
        }
    );

});