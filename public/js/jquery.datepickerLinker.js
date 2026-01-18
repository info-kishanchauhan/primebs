/**
 * @jQuery      plugin
 *
 * Link a datepicker with other controls
 *
 * @date        september 2014
 * @copyright   Believe Digital
 * @version     1.4 (datetime management)
 */
function datepickerLinker(selector, options) 
{
	//init options
	var defaultOptions = { 
		minDate    : false, 
		maxDate    : false, 
		autoInit   : true, 
		autoAdjust : true, 
		dateMode   : 'date',
		onAdjust   : function() { return true; } 
	};

	options = $.extend('', defaultOptions, options);

	//applying event
	$('body').on('mouseenter', selector, function(){
		
		//a calendar is already open
		if($("#ui-datepicker-div").is(":visible")) return false;

		if($(this).hasClass('hasDatepicker'))
		{
			var curDatePicker = $(this);

			//adding focus
			$('.datepickerLinkerHover').removeClass('datepickerLinkerHover');
			$(this).addClass('datepickerLinkerHover');

			//init
			if(!$(this).hasClass('datepickerLinker'))
			{
				$(this).addClass('datepickerLinker');
				$(this).attr('data-dplinker-prevval', $(this).val());

				//calculting min and max date
				datepickerLinkerApplyRestrictions(curDatePicker, options);	

				//force date adjusting
				if(options['autoAdjust'])
				{
					//change action
					$(this).change(function(){
						$(this).attr('data-dplinker-prevval', $(this).val());
					});

					//extracting selectors
					var changeSelectors = Array();
					
					for(var index in options)
					{
						if((index == 'minDate' || index == 'maxDate') && options[index])
						{
							for(var field in options[index])
							{
								if(isNaN(field)) changeSelectors[field] = field;
							}
						}
					}
					//applying change for each selector
					for(var field in changeSelectors)
					{
						$('body').on('change', field, function(){
							
							//same input
							if($(this).is(curDatePicker)) return false;

				 // console.log('change-'+$(curDatePicker).attr('id'));
							datepickerLinkerCheckAdjust(curDatePicker, options);

							//comparing old and new value
							// var currentVal = $(curDatePicker).attr('data-dplinker-prevval') ? $(curDatePicker).attr('data-dplinker-prevval') : $(curDatePicker).val();

							// datepickerLinkerApplyRestrictions(curDatePicker, options);

							// var newVal = $(curDatePicker).val();

							// //value adjusted
							// if(currentVal != newVal) 
							// {
							// 	options.onAdjust.call($(curDatePicker), newVal, currentVal);								
							// }

						});
					}

				}
			}
			else
			{
				//calculting min and max date
				datepickerLinkerApplyRestrictions(curDatePicker, options);	
			}
		}

	});		

	//auto init
	if(options.autoInit)
		setTimeout(function(){ 

			$(selector).each(function(){

				$(this).mouseenter();

				 // console.log('init-'+$(this).attr('id'));
				datepickerLinkerCheckAdjust($(this), options);
			});

		}, 500);
}

//tools
function datepickerLinkerCheckAdjust(curDatePicker, options)
{
	var currentVal = $(curDatePicker).attr('data-dplinker-prevval') ? $(curDatePicker).attr('data-dplinker-prevval') : $(curDatePicker).val();

	datepickerLinkerApplyRestrictions(curDatePicker, options);

	var newVal = $(curDatePicker).val();

	//value adjusted
	if(currentVal != newVal) 
	{
		options.onAdjust.call($(curDatePicker), newVal, currentVal);								
	}

	return true;
}

function datepickerLinkerApplyRestrictions(curDatePicker, options)
{
	var limitUpdated = false;

	for(var index in options)
	{
		if((index == 'minDate' || index == 'maxDate') && options[index])
		{
			var curDate   = false;
			var cmpMethod = options['minDate']['cmpMethod'] == 'max' ? 'max' : 'min';

			//dealing with each filter
			for(var field in options[index])
			{
				var foundDate = false;

				//real date
				if(!isNaN(field))
				{
					if(options[index][field] != "" && !options[index][field].match(/^[0.\/-]+$/)) 
						foundDate = new Date(options[index][field]);
				}
				//extracting date from inputs
				else 
				{
					//splitting field
					var fieldCmpMethod = options[index][field].replace(/[+-].*$/, '');
					var fieldGap       = options[index][field].replace(/^.*([+-].*)$/, '$1');

					fieldGap = fieldGap == '' || fieldGap == options[index][field] ? 0 : parseInt(fieldGap);

					$(field).not(curDatePicker).each(function(){

						if($(this).val() != "")
						{
							//forcing datepicker
							if(!$(this).hasClass("hasDatepicker")) 
								$(this).mouseover();
							
							if($(this).hasClass("hasDatepicker"))
							{
								var dpDate = datepickerLinkerAddDays($(this).datepicker("getDate"), fieldGap);

								if(!foundDate || options[index][field] == 'max' && dpDate > foundDate)
									foundDate = dpDate;
								else if(!foundDate || dpDate < foundDate)
									foundDate = dpDate;
							} 
						}
					});
					
				}


				//updating date 
				if(foundDate)
				{
					if(!curDate || cmpMethod == 'max' && foundDate > curDate)
						curDate = foundDate;
					else if(!curDate || foundDate < curDate)
						curDate = foundDate;
				}
			}

			if(curDate) 
			{
				// console.log(index+'-'+$(curDatePicker).attr('id'));
				// console.log(curDate);

				if(!$(curDatePicker).datepicker('option', index) || curDate.getTime() != $(curDatePicker).datepicker('option', index).getTime())
				{
					$(curDatePicker).datepicker('option', index, curDate);
					$(curDatePicker).datepicker('option', index+'Time', curDate);
				}

				limitUpdated = true;
			}
		}
	}

	return limitUpdated;
}

function datepickerLinkerAddDays(dateObj, nbDays) 
{ 
	return new Date(dateObj.getTime()+nbDays*24*3600*1000);
}