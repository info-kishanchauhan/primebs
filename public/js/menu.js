function toggleMenuGroup(curGroup, timer, fromCollapse = false) {
    timer = timer || 0;
    curGroup = $(curGroup).is('.bs-menu-group') ? curGroup : curGroup.parents('.bs-menu-group:first');

    //close every other groups
    var alreadyOpened = $('.bs-menu-group-content-open').not(curGroup);

    alreadyOpened.find('.bs-menu-group-content:first').slideUp(timer);
    alreadyOpened.removeClass('bs-menu-group-content-open');

    //toggle menu
    curGroup.find('.bs-menu-group-content:first').slideToggle(timer, function () {
        if ($(this).is(':visible')) {
            curGroup.addClass('bs-menu-group-content-open');
        } else {
            curGroup.removeClass('bs-menu-group-content-open');
        }
    });

    if (curGroup.find('.bs-menu-group-description a').length && !fromCollapse) {
        var link = curGroup.find('.bs-menu-group-description a').attr('href');
        setTimeout(function (link) {
            window.location.href = link;
        }, 100, link);
    }
}

function openLink(current) {
    let link = $(current).attr('href')
    if(link) {
      window.location.href = link;
    }
}

$(function () {
    //open / close menu
    $('.bs-menu-group-description').click(function (event) {
        event.preventDefault();
        if ($('#bs-table-container').is('.menu__collapse')) {
          $('#bs-table-container').toggleClass('menu__collapse', false);
          if (!$(this).parents('.bs-menu-group:first').is('.bs-menu-group-content-open'))
            toggleMenuGroup($(this), 200, true);
        } else {
          toggleMenuGroup($(this), 200);
        }
    });
});