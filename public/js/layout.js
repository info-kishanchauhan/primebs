
//*********************************************************
//** HEADER MENU
//*********************************************************
var commonAvatarMenu = {};
var commonNotificationMenu = {};

(function(app){

	var self = {};
	self.alreadyLoaded = false;

	self.init = function(){

		$(document).on('click', 'body', function(event) {

			if($(event.target).closest('.containerFloating').length === 0) {
				$('#avatarContainer').hide();
			}

		});

		$(document).on('click', '#avatarIconMenu', function(event) {

			$('#notificationContainer').hide();

			if($('#avatarContainer').css('display') == 'block') {
				$('#avatarContainer').hide();
				return false;
			}
			else{
				$('#avatarContainer').show();
			}

			event.stopPropagation();

		});

	};

	app.displayMenu = self;


})(commonAvatarMenu);

(function(app){

	var self = {};

	self.init = function(){

		$(document).on('click', 'body', function(event) {

			if($(event.target).closest('.containerFloating').length === 0) {
				$('#notificationContainer').hide();
			}

		});

		$(document).on('click', '#notificationIconMenu', function(event) {

			$('#avatarContainer').hide();
			 $('#notificationContainer').toggle(); // <--- Toggle instead of always show
			event.stopPropagation();
		});

		var offsetNotifIcon = $('#notificationIconMenu').offset();
		var widthNotifIcon = $('#notificationIconMenu').width();
		var offsetMainHeaderMenu =  $('#bs-main-header-menu').offset();
		var notifMenuWidth = 304;
		var containerPositionLeft = 0 - notifMenuWidth + (offsetNotifIcon.left - offsetMainHeaderMenu.left) + widthNotifIcon ;
		containerPositionLeft = containerPositionLeft + 17;//hide numbers

		/*$.ajax({
			url : 'menu/getNotificationMenu',
			success : function(response) {
				if(response.pastille != '' && response.pastille > 0){*/
					$('#notificationIconMenu').removeClass('notClickable');
					//$('#notificationNumberSign').html(response.pastille);
					//var notificationContainer = $(response.view);
					//notificationContainer.find('.containerFloating').css('left', containerPositionLeft + 'px');
					//$('#bs-main-header-menu').prepend(notificationContainer);
				//}

				self.alreadyLoaded = true;
			//}
		//});
	};

	app.displayMenu = self;


})(commonNotificationMenu);

$(function(){

	commonAvatarMenu.displayMenu.init();

	if($('#notificationIconMenu').length) {
		commonNotificationMenu.displayMenu.init();
	}

	$('iframe').load(function(){
		$(this).contents().on('click', function(event) {
			$('#avatarContainer').hide();
			$('#notificationContainer').hide();
		});
	});

});

//*********************************************************
//** STICKY MENU
//*********************************************************
function updateStickyStatus()
{
	var leftSize   = $('#bs-left-container').width();
	var windowSize = $(window).width() - 100; //keep 100px safe

	if(leftSize > windowSize)
		$('#bs-table-container').removeClass('bs-sticked');
	else
		$('#bs-table-container').addClass('bs-sticked');
}

$(function(){

	$(window).resize(function() {
		updateStickyStatus();
	});

	updateStickyStatus();

});

//*********************************************************
//** LOADER
//*********************************************************
function showLoader() {
	$('#loader-placeholder').css('margin-top', '50px');
	$("#loader-placeholder").prepend('<div id="loader"></div>');
	$("#loader").circularLoader("show");
}

//*********************************************************
//** ALERTS
//*********************************************************
function applyLayoutAlert(selector, message, sessionId, options)
{
	options = options || {};

	$(selector).attr('data-alert-id', '');
	$(selector).find('.bs-alert-message').html(message);

	if(sessionId)
		$(selector).attr('data-alert-id', sessionId);

	if(options['class'] != undefined)
		$(selector).addClass(options['class']);

	$(selector).show();
}

function addLayoutAlert(message, sessionId, options)
{
	applyLayoutAlert('#bs-main-alert', message, sessionId, options);
}

function addSubAlert(prependTo, message, sessionId, options)
{
	var customAlert = $('#bs-main .bs-sub-alert').clone();

	$(prependTo).prepend(customAlert);

	applyLayoutAlert(customAlert, message, sessionId, options);
}

function newRelease() {
	prepareNewReleaseStep('10');
  $('#newReleaseModal').modal();

  dataLayer.push({
	event: 'new_release_view',
	release_type: 'single'
  });
}

function cancelNewRelease() {
  let step = $('#newReleaseModalStep').val();
	switch(step) {
		case '10':
      $('#newReleaseModal').modal('hide');
			break;
		default:
      $('#newReleaseModalBody').removeClass('step' + step)
      prepareNewReleaseStep($('#newReleaseModalPreviousStep').val());
			break;
	}
}

function prepareNewReleaseStep(step) {
	switch(step){
		case '10':
      $('#newReleaseModalBody').removeClass().addClass('body step10');
      $('#newReleaseModalState').text('1/3');
      $('#newReleaseModalTitle').text(EEM.EEM_NEW_RELEASE);
      $('#newReleaseModalStep').val("10");
      $('#newReleaseModalCreateButton').addClass('none');
      $('#newReleaseModalNextButton').removeClass('none');
      $('#newReleaseModalCancelButton').text(EEM.EEM_CANCEL);
			break;
    case '11':
      $('#newReleaseModalTitle').text(EEM.EEM_NEW_AUDIO_RELEASE);
      $('#newReleaseModalBody').addClass('step11');
      $('#newReleaseModalStep').val("11");
      $('#newReleaseModalPreviousStep').val("10");
      $('#newReleaseModalState').text('2/3');
      $('#newReleaseModalCreateButton').addClass('none');
      $('#newReleaseModalNextButton').removeClass('none');
      $('#newReleaseModalCancelButton').text(EEM.EEM_BACK);
      break;
    case '21':
      $('#newReleaseModalTitle').text(EEM.EEM_NEW_VIDEO_RELEASE);
      $('#newReleaseModalBody').addClass('step21');
      $('#newReleaseModalStep').val("21");
      $('#newReleaseModalPreviousStep').val("10");
      $('#newReleaseModalState').text('2/3');
      $('#newReleaseModalCreateButton').addClass('none');
      $('#newReleaseModalNextButton').removeClass('none');
      $('#newReleaseModalCancelButton').text(EEM.EEM_BACK);
			$('input[name=video_type]')[0].checked = true
      break;
    case '12':
      $('#newReleaseModalState').text('3/3');
      $('#newReleaseModalBody').addClass('step12');
      $('#newReleaseModalStep').val("12");
      $('#newReleaseModalCreateButton').removeClass('none');
      $('#newReleaseModalNextButton').addClass('none');
      $('#newReleaseModalCancelButton').text(EEM.EEM_BACK);
      break;
	}
}

function nextStepNewRelease() {
  let step = $('#newReleaseModalStep').val();
  $('#newReleaseModalPreviousStep').val(step);
  $('#newReleaseModalBody').removeClass('step' + step)
	switch(step) {
		case '11':
      let genre = $('input[name=genre_audio]:checked').val()
			$("#newReleaseGenre").val(genre);
      prepareNewReleaseStep('12');
			$('#step12Compilation').removeClass('none');
      $('#newReleaseModalPreviousStep').val("11");
      let audio_genre = {
      	"" : "",
        "no_genre" : "",
				"jazz" : EEM.EEM_JAZZ,
				"western" : EEM.EEM_WESTERN_CLASSIC,
				"itunes" : EEM.ITUNES,
			}
      $('#newReleaseInputTitle').attr("placeholder", EEM.EEM_FILL_AUDIO_NAME.replace('%TYPE%', audio_genre[genre]));
			if('itunes' === genre) {
        $('#newReleaseModalTitle').text(EEM.EEM_NEW_ITUNES_VIDEO);
        $('#newReleaseInputTitle').attr("placeholder", EEM.EEM_FILL_ITUNES_NAME);
			}
      if ('jazz' == genre) {
      	$('#step12Compilation').addClass('none');
			}
			$('#newReleaseIsCompilation').prop('checked', false)
      break;
    case '21':
      let video_type = $('input[name=video_type]:checked').val()
      $("#newReleaseGenre").val(video_type);
      prepareNewReleaseStep('12');
      $('#newReleaseModalPreviousStep').val("21");
      let video_genre = {
        "" : "",
        "entertainment" : EEM.EEM_ENTERTAINMENT,
        "music" : EEM.EEM_VIDEO_MUSIC,
        "news" : EEM.EEM_VIDEO_NEWS,
        "play" : EEM.EEM_VIDEO_PLAY,
      }
      $('#step12Compilation').addClass('none');
      $('#newReleaseInputTitle').attr("placeholder", EEM.EEM_FILL_VIDEO_NAME.replace('%TYPE%', video_genre[video_type]));
      break;
		default:
			let type = $('input[name=typeNewRelease]:checked').val()
      $('#newReleaseModalState').text('2/3');
      $('#newReleaseType').val(type);
			switch (type) {
				case 'video':
          prepareNewReleaseStep('21');
					break;
				case 'ringtone':
          prepareNewReleaseStep('12');
          $('#newReleaseModalTitle').text(EEM.EEM_NEW_RINGTONES);
          $('#newReleaseInputTitle').attr("placeholder", EEM.EEM_NEW_RINGTONE_FILL);
          $('#newReleaseModalType').text('ringtone');
          $('#newReleaseModalPreviousStep').val("10");
          $('#newReleaseModalState').text('2/2');
          $('#step12Compilation').addClass('none');
					break;
				default:
          prepareNewReleaseStep('11');
					break;
			}
			break;
	}
}

function createNewRelease() {
  let genre = $('#newReleaseGenre').val()
  let type = $('#newReleaseType').val()
  let title = $('#newReleaseInputTitle').val()
  let compilation = $('#newReleaseIsCompilation').is(':checked')

	if (title.length <= 0) {
  	return;
	}

	var typeRelease = 0;

  switch(type) {
    case 'video':
      switch (genre) {
        case 'entertainment':
          typeRelease = 5;
          break;
        case 'news':
          typeRelease = 7;
          break;
        case 'play':
          typeRelease = 8;
          break;
        case 'itunes':
          typeRelease = 3;
          break;
        default:
          typeRelease = 4;
          break;
      }
      break;
    case 'ringtone':
      typeRelease = 6;
      break;
		default:
      switch (genre) {
        case 'western':
          typeRelease = (compilation) ? 10 : 1;
          break;
        case 'jazz':
          typeRelease = 2;
          break;
				default:
          typeRelease = (compilation) ? 9 : 0;
					break;
      }
			break;
	}

	$('#newReleaseModalCreateButton').addClass('disabled');
	$('#newReleaseModalCreateButton').prop('disabled', true);

	dataLayer.push({
		event: 'new_release_start',
		release_type: 'single'
	});

	showLoader();

	$.post( '/easyentry/creation/newRelease/'+ typeRelease, { title: title }, function( data ) {
		if (data.idRelease) {
			window.location = '/easyentry/main/edit/' + data.idRelease;
		} else if (data.idProduct) {
			window.location = '/smartentry/' + data.idProduct;
		}
	}, "json");
}

$(function(){

	$('#bs-table-container').addClass('bs-sticked');

	//alert close
	$('#bs-main').on('click', '.bs-alert-close', function(){

		var alert   = $(this).parent();
		var alertId = alert.data('alert-id');

		//saving state
		if(alertId)
			$.get('/layout/alertsessionupdater/index/'+alertId);

		alert.slideUp(300);
	});

  $('.menu__icon').on('click', function() {
    $('#bs-table-container').toggleClass('menu__collapse');
	});

  $('#bs-main-header-icon').on('click', function() {
    $('#bs-table-container').toggleClass('menu__collapse');
  });


  if ($(window).width() < 1280) {
    $('#bs-table-container').toggleClass('menu__collapse', true);
	}

  setTimeout(function(){
    $("body").removeClass("preload");
	}, 2000);

  $( "#newReleaseInputTitle" ).keyup(function() {
    if($("#newReleaseInputTitle").val().length > 0) {
      $('#newReleaseModalCreateButton').prop('disabled', false);
      $('#newReleaseModalCreateButton').removeClass('disabled');
		} else {
      $('#newReleaseModalCreateButton').addClass('disabled');
      $('#newReleaseModalCreateButton').prop('disabled', true);
		}
  });

});


//Artist page : Activate "create release" button
$(document).ready(function(){
  $(document).on('click', '#artistPageCreateRelease', function(event) {
    event.preventDefault();
    newRelease();
  });

})
