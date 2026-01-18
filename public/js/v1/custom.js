$('.scrollbar-inner').overlayScrollbars({
    resize: "none",
    sizeAutoCapable: true,
    paddingAbsolute: true,
    scrollbars: {
        clickScrolling: true
    }
});

$("#menu-toggle").click(function (e) {
    e.preventDefault();
    $(".dashboard-body").toggleClass("aside-minimize");
});
$(".navbar-toggler").click(function (e) {
    e.preventDefault();
    $(".navbar-collapse").toggleClass("show");
});
//   $(".aside-minimize .mspeducator_aside").on('mouseenter', function(e) {
//     $(".dashboard-body").addClass("side-minimize-hover");
// });
// $(".aside-minimize .mspeducator_aside").on('mouseleave', function(e) {
//   $(".dashboard-body").addClass("side-minimize-hover");
// });
$(".mspeducator_top-search input").focus(function () {
    $(".mspeducator_top-search-list").addClass("d-block");
}).blur(function () {
    //$(".mspeducator_top-search-list").removeClass("d-block");
});
$(document).click((event) => {
  if (!$(event.target).closest('.mspeducator_top-search-box').length) {
	$(".mspeducator_top-search-list").removeClass("d-block");
  }        
});
$(window).scroll(function () {
    if ($(this).scrollTop() > 100) { // this refers to window
        $('.mspeducator_header').addClass('mspeducator_header_fix');
    } else {
        $('.mspeducator_header').removeClass('mspeducator_header_fix')
    }
});