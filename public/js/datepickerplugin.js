
/**
 * Module de date picker
 */
(function ($) {

    var HandleDatePicker = function(element, options){
        this.$element  = $(element);
        this.options   = $.extend({}, HandleDatePicker.DEFAULTS, options);
        this.tmpId = this.$element.attr('id');

    }

    HandleDatePicker.DEFAULTS = {
        scenario: 'scenarioDailySalesDatepicker',
        displayLeftMenuPicker:'',
        displayFooterMenuPicker:'',
        defaultPeriodType : 'days_30',
        defaultPeriodValue : 'days_30',
        defaultDateFrom : '',
        defaultDateTo : '',
        periodValueTmp : 'days_30',
        datePickerFromInitialisationParameter:'',
        datePickerToInitialisationParameter:'',
        choosePeriodDisplayButtonList:'',//['fromBegin','last7','last15','last30','last60','last90','thisMonth', 'lastMonth', 'oneYear', 'custom'],
        specialVarious:'',
        userDefinedOrder : false
    };

    HandleDatePicker.prototype.setScenario = function() {


        if(this.options.scenario == 'scenarioDailySalesDatepicker'){


            if(this.options.datePickerFromInitialisationParameter == '') {
                this.options.datePickerFromInitialisationParameter = {
                    format: 'yyyy-mm-dd',
                    maxViewMode: 2,
                    minViewMode: '',
                    endDate: '-2d',
                    startDate: '-1y -2d'
                };
            }
            if(this.options.datePickerToInitialisationParameter == '') {
                this.options.datePickerToInitialisationParameter = {
                    format: 'yyyy-mm-dd',
                    maxViewMode: 2,
                    minViewMode: '',
                    endDate: '-2d',
                    startDate: '-1y -2d'
                };
            }


            if(this.options.displayLeftMenuPicker == ''){
                this.options.displayLeftMenuPicker = 'show';
            }

            if(this.options.displayFooterMenuPicker == ''){
                this.options.displayFooterMenuPicker = 'show';
            }

            if(this.options.choosePeriodDisplayButtonList == ''){
                this.options.choosePeriodDisplayButtonList = ['last7','last15','last30','last90','thisMonth', 'lastMonth', 'oneYear', 'custom'];
            }

        }

        if(this.options.scenario == 'scenarioReportsPaymentDatepicker'){


            if(this.options.datePickerFromInitialisationParameter == ''){
                this.options.datePickerFromInitialisationParameter = {format:'yyyy-mm',maxViewMode:2,minViewMode:'months',endDate:'-2m',startDate:'-2y -2m'};
            }


            if(this.options.datePickerToInitialisationParameter == ''){
                this.options.datePickerToInitialisationParameter = {format:'yyyy-mm',maxViewMode:2,minViewMode:'months',endDate:'-2m',startDate:'-2y -2m'};
            }


            if(this.options.displayLeftMenuPicker == ''){
                this.options.displayLeftMenuPicker = 'hide';
            }

            if(this.options.displayFooterMenuPicker == ''){
                this.options.displayFooterMenuPicker = 'hide';
            }

            if(this.options.choosePeriodDisplayButtonList == ''){
                this.options.choosePeriodDisplayButtonList = ['fromBegin', 'lastMonth','thisMonth', 'oneYear', 'custom'];
            }





        }

    }

    HandleDatePicker.prototype.init = function() {

        this.setScenario();
        this.createDisplay();


        $('#' + this.tmpId + '-datetimepickerFrom').datepicker({
            format:this.options.datePickerFromInitialisationParameter.format,
            maxViewMode:this.options.datePickerFromInitialisationParameter.maxViewMode,
            minViewMode:this.options.datePickerFromInitialisationParameter.minViewMode,
            endDate:this.options.datePickerFromInitialisationParameter.endDate,
            startDate:this.options.datePickerFromInitialisationParameter.startDate,
            language: clientLanguage
        });

        $('#' + this.tmpId + '-datetimepickerTo').datepicker({
            format: this.options.datePickerToInitialisationParameter.format,
            maxViewMode: this.options.datePickerToInitialisationParameter.maxViewMode,
            minViewMode: this.options.datePickerToInitialisationParameter.minViewMode,
            endDate: this.options.datePickerToInitialisationParameter.endDate,
            startDate: this.options.datePickerToInitialisationParameter.startDate,
            language: clientLanguage
        });

        this.update($('#' + this.tmpId + '-periodPanel').data('periodvalue'));
        $( '#' + this.tmpId + '-contentTitle').html(this.getHeaderDayTitle());

    };

    HandleDatePicker.prototype.update = function(periodValue) {

        var maxEndDate = $('#'+this.tmpId+'-datetimepickerTo').datepicker('getEndDate');

        switch (periodValue) {

            case 'fromBegin':
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate',this.options.datePickerFromInitialisationParameter.startDate);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);
                break;
            case 'days_7':
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', "-7d " + this.options.datePickerFromInitialisationParameter.endDate);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);

                break;
            case 'days_15':
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', "-10d " + this.options.datePickerFromInitialisationParameter.endDate);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);

                break;
            case 'days_30':
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', "-30d " + this.options.datePickerFromInitialisationParameter.endDate);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);

                break;

            case 'days_60':
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', "-60d " + this.options.datePickerFromInitialisationParameter.endDate);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);
                break;

            case 'days_90':
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', "-90d " + this.options.datePickerFromInitialisationParameter.endDate);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);
                break;
            case 'month_0':

                if (this.options.scenario == 'scenarioDailySalesDatepicker') {
                    var currDate = new Date();
                    var currMonth = currDate.getMonth();
                    var currYear = currDate.getFullYear();

                    if (currDate.getDate() <= 2) {

                        if (currMonth != 0) {
                            currMonth = currMonth - 1;
                        } else {
                            currMonth = 11;
                            currYear = currYear - 1;
                        }
                    }

                    var startDate = new Date(currYear, currMonth, 1);
                    $('#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', startDate);
                    $('#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);


                } else {
                    var firstDay = new Date(maxEndDate.getFullYear(), maxEndDate.getMonth(), 1);
                    $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', firstDay);
                    $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);
                }
                break;
            case 'month_1':

                if (this.options.scenario == 'scenarioDailySalesDatepicker'){
                    var currDate = new Date();
                    var currMonth = currDate.getMonth() ;
                    var currYear = currDate.getFullYear();

                    if(currDate.getDate() <= 2) {
                        if (currMonth != 0) {
                            currMonth = currMonth - 1;
                        } else {
                            currMonth = 11;
                            currYear = currYear - 1;
                        }
                    }

                    var startDate = new Date(currYear,currMonth - 1 ,1);
                    var endDate = new Date(currYear,currMonth,0);

                    $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', startDate);
                    $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', endDate);

                } else {
                    var firstDay = new Date(maxEndDate.getFullYear(), maxEndDate.getMonth()-1, 1);
                    var lastDay  = new Date(firstDay.getFullYear(), firstDay.getMonth()+1, 0);

                    $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', firstDay);
                    $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', lastDay);
                }


                break;
            case 'year_1':
                var firstDay = new Date(maxEndDate.getFullYear()-1, maxEndDate.getMonth(), 1);
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', firstDay);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.datePickerToInitialisationParameter.endDate);
                break;
        }

        return true;
    }

    HandleDatePicker.prototype.setCustomValue = function() {
        var startDate  = $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('getDate'),
            endDate    = $( '#' + this.tmpId + '-datetimepickerTo').datepicker('getDate'),
            maxEndDate = $( '#' + this.tmpId + '-datetimepickerTo').datepicker('getEndDate'),
            newEndDate = new Date(endDate.getFullYear(), endDate.getMonth()+1, 0);

        endDate = (newEndDate < maxEndDate) ? newEndDate : maxEndDate;

        this.setPeriodValueTmp('custom');

        $('#' + this.tmpId + '-periodPanel').data('periodvalue', 'custom');
        $('#' + this.tmpId + '-periodPanel').data('datefrom', startDate);
        $('#' + this.tmpId + '-periodPanel').data('dateto', endDate);

        return true;
    }

    HandleDatePicker.prototype.getFinalChoice = function() {

        var startDate = $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('getDate'),
            endDate = $( '#' + this.tmpId + '-datetimepickerTo').datepicker('getDate'),
            maxEndDate = $( '#' + this.tmpId + '-datetimepickerTo').datepicker('getEndDate'),
            newEndDate = new Date(endDate.getFullYear(), endDate.getMonth()+1, 0);

        endDate = (newEndDate < maxEndDate) ? newEndDate : maxEndDate;

        var selectDateD = startDate.getDate(),
            selectDateM = startDate.getMonth(),
            selectDateY = startDate.getFullYear(),
            toDateD = endDate.getDate(),
            toDateM = endDate.getMonth(),
            toDateY = endDate.getFullYear();

        selectDateM += 1;
        toDateM += 1;

        var data = {};
        data.period = $( '#' + this.tmpId + '-periodPanel').data('periodvalue');
        data.dateFrom =(selectDateY + "-" + ("0" + selectDateM).slice(-2)  + "-" + ("0" + selectDateD).slice(-2));
        data.dateTo = (toDateY + "-" + ("0" + toDateM).slice(-2)  + "-" + ("0" + toDateD).slice(-2));
        data.fromDayDetailed = selectDateD;
        data.fromMonthDetailed = selectDateM;
        data.fromYearDetailed = selectDateY;
        data.toDayDetailed = toDateD;
        data.toMonthDetailed = toDateM;
        data.toYearDetailed = toDateY;


        return data;
    }

    HandleDatePicker.prototype.createDisplay = function() {

        var html = '';

        html += '<div><button id="'+this.tmpId+'-periodButton" class="btn btn-default" data-buttontype="period"><span class="blv-font blv-font-calendar"></span><span id="'+this.tmpId+'-contentTitle"></span></button></div>';

        html += '<div id="'+this.tmpId+'-periodPanel" class="slidePanel" data-periodvalue="'+this.options.defaultPeriodValue+'" data-datefrom="" data-dateto="">';


        if(this.options.displayLeftMenuPicker == 'show'){

            html += '<div class="col-lg-12">';
        }else{
            html += '<div class="col-lg-8 col-lg-offset-2">';
        }

        html += this.headerPeriodDisplay();
        html += '<div class="row">';

        if(this.options.displayLeftMenuPicker == 'show'){
            html += this.leftChoicePeriodDisplay();
        }

        html += this.datePickerFromAndToDisplay();

        html += '</div>';

        if(this.options.displayFooterMenuPicker == 'show'){
            html += this.footerApplyPeriodDisplay();
        }

        html += '</div>';
        html += '</div>';

        this.$element.html(html);


    }

    HandleDatePicker.prototype.deprecatedLeftChoicePeriodDisplay = function() {

        var html = '';
        var addClassSelectDateActif = 'selectDateActif';

        html += '<div class="col-lg-2 col-lg-offset-1 periodMenu">';

        if(this.options.choosePeriodDisplayButtonList.indexOf('fromBegin') >= 0){

            var langVarFromBegin = languageDatePickerConstants['DATE_FILTER_FROM_BEGINNING'];

            if(this.options.specialVarious.currentPage !== undefined) {
                if(this.options.specialVarious.currentPage == 'catalogProductSynthesis'){
                    langVarFromBegin = languageDatePickerConstants['DATE_FILTER_SINCE_OUTPUT'];
                }
            }

            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'fromBegin')? addClassSelectDateActif : '') + '" data-periodvalue="fromBegin">' + langVarFromBegin + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('last7') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'last7')? addClassSelectDateActif : '') + '" data-periodvalue="days_7">' + languageDatePickerConstants['DATE_FILTER_7_DAYS_AGO'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('last15') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'last15')? addClassSelectDateActif : '') + '" data-periodvalue="days_15">' + languageDatePickerConstants['DATE_FILTER_15_DAYS_AGO'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('last30') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'last30')? addClassSelectDateActif : '') + '" data-periodvalue="days_30">' + languageDatePickerConstants['DATE_FILTER_30_DAYS_AGO'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('last60') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'last60')? addClassSelectDateActif : '') + '" data-periodvalue="days_60">' + languageDatePickerConstants['DATE_FILTER_60_DAYS_AGO'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('last90') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'last90')? addClassSelectDateActif : '') + '" data-periodvalue="days_90">' + languageDatePickerConstants['DATE_FILTER_90_DAYS_AGO'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('thisMonth') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'thisMonth')? addClassSelectDateActif : '') + '" data-periodvalue="month_0">' + languageDatePickerConstants['DATE_FILTER_THIS_MONTH'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('lastMonth') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'lastMonth')? addClassSelectDateActif : '') + '" data-periodvalue="month_1">' + languageDatePickerConstants['DATE_FILTER_LAST_MONTH'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('oneYear') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary '+ ((this.options.defaultPeriodValue == 'oneYear')? addClassSelectDateActif : '') + '" data-periodvalue="year_1">' + languageDatePickerConstants['DATE_FILTER_ONE_YEAR_AGO'] + '</button>';
        }

        if(this.options.choosePeriodDisplayButtonList.indexOf('custom') >= 0) {
            html += '<button class="btn btn-sm btn-block btn-primary custom '+ ((this.options.defaultPeriodValue == 'custom')? addClassSelectDateActif : '') + '" data-periodvalue="custom">' + languageDatePickerConstants['DATE_FILTER_CUSTOM'] + '</button>';
        }

        html += '</div>';

        return html;

    }

    HandleDatePicker.prototype.leftChoicePeriodDisplay = function() {

        if (this.options.userDefinedOrder) {
            var displayOptions = {
                "fromBegin" : {
                    "periodvalue" : "fromBegin",
                    "language" : (this.options.specialVarious.currentPage !== undefined && this.options.specialVarious.currentPage == 'catalogProductSynthesis') ? languageDatePickerConstants['DATE_FILTER_SINCE_OUTPUT'] : languageDatePickerConstants['DATE_FILTER_FROM_BEGINNING'],
                },
                "last7": {
                    "periodvalue" : "days_7",
                    "language" : languageDatePickerConstants['DATE_FILTER_7_DAYS_AGO'],
                },
                "last15": {
                    "periodvalue" : "days_15",
                    "language" : languageDatePickerConstants['DATE_FILTER_15_DAYS_AGO'],
                },
                "last30": {
                    "periodvalue" : "days_30",
                    "language" : languageDatePickerConstants['DATE_FILTER_30_DAYS_AGO'],
                },
                "last60": {
                    "periodvalue" : "days_60",
                    "language" : languageDatePickerConstants['DATE_FILTER_60_DAYS_AGO'],
                },
                "last90": {
                    "periodvalue" : "days_90",
                    "language" : languageDatePickerConstants['DATE_FILTER_90_DAYS_AGO'],
                },
                "thisMonth": {
                    "periodvalue" : "month_0",
                    "language" : languageDatePickerConstants['DATE_FILTER_THIS_MONTH'],
                },
                "lastMonth": {
                    "periodvalue" : "month_1",
                    "language" : languageDatePickerConstants['DATE_FILTER_LAST_MONTH'],
                },
                "oneYear": {
                    "periodvalue" : "year_1",
                    "language" : languageDatePickerConstants['DATE_FILTER_ONE_YEAR_AGO'],
                },
                "custom": {
                    "periodvalue" : "custom",
                    "language" : languageDatePickerConstants['DATE_FILTER_CUSTOM'],
                    "class" : "custom"
                }
            };
            //add user language variables
            if (this.options.choosePeriodDisplayButtonListLanguage !== undefined) {
                for(var index in this.options.choosePeriodDisplayButtonListLanguage) {
                    displayOptions[index]["language"] = this.options.choosePeriodDisplayButtonListLanguage[index];
                }
            }


            var addClassSelectDateActif = 'selectDateActif';
            var html = '<div class="col-lg-2 col-lg-offset-1 periodMenu">';
            var defaultPeriodValue = this.options.defaultPeriodValue;

            this.options.choosePeriodDisplayButtonList.forEach(function(element, index) {
                if (displayOptions[element] === undefined) {
                    throw "No display parameters defined for type : "+element;
                }

                html += '<button class="btn btn-sm btn-block btn-primary '+ ((displayOptions[element]['class'] !== undefined) ? displayOptions[element]['class'] : '') +' '+ ((defaultPeriodValue == displayOptions[element]['periodvalue'])? addClassSelectDateActif : '') + '" data-periodvalue="'+displayOptions[element]['periodvalue']+'">' + displayOptions[element]['language'] + '</button>';
            });

            html += '</div>';

            return html;

        } else {

            return this.deprecatedLeftChoicePeriodDisplay();
        }

    }

    HandleDatePicker.prototype.headerPeriodDisplay = function() {
        var html = '';



        html += '<div class="row">';
        if(this.options.displayLeftMenuPicker == 'show'){
            html += '<div class="col-lg-4 col-lg-offset-3">';
        }else{
            html += '<div class="col-lg-5">';
        }
        html += '<h4 class="text-center text-uppercase">' + languageDatePickerConstants['PERIOD_FILTERS_FROM'] + '</h4>';
        html += '</div>';


        if(this.options.displayLeftMenuPicker == 'show'){
            html += '<div class="col-lg-4">';
        }else{
            html += '<div class="col-lg-5 col-lg-offset-2">';
        }


        html += '<h4 class="text-center text-uppercase">' + languageDatePickerConstants['PERIOD_FILTERS_TO'] + '</h4>';
        html += '</div>';
        html += '</div>';

        return html;
    }

    HandleDatePicker.prototype.footerApplyPeriodDisplay = function() {


        var html = '';

        html += '<div class="row" id="'+this.tmpId+'-slideButtons" >';
        html += '<div class="col-lg-12">';
        html += '<div class="form-group">';
        html += '<div class="pull-right col-sm-2">';
        html += '<button id="'+this.tmpId+'-applyDatePickerFilters" type="submit" class="btn btn-default btn-block">' + languageDatePickerConstants['APPLY_FILTERS_BUTTON'] + '</button>';
        html += '</div>';
        html += '<div class="pull-right col-sm-2">';
        html += '<button id="'+this.tmpId+'-clearDatePickerFilters" type="submit" class="btn btn-default btn-block">' + languageDatePickerConstants['CANCEL_FILTERS_BUTTON'] + '</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        return html;

    }

    HandleDatePicker.prototype.datePickerFromAndToDisplay = function() {
        var html = '';


        if(this.options.displayLeftMenuPicker == 'show') {
            html += '<div class="col-lg-4">';
        }else{
            html += '<div class="col-lg-5">';
        }
        html += '<div style="overflow:hidden;">';
        html += '<div class="form-group">';

        if(this.options.displayLeftMenuPicker == 'show'){
            html += '<div id="'+this.tmpId+'-datetimepickerFrom" class="col-lg-10 col-lg-offset-1"></div>';
        }else{
            html += '<div id="'+this.tmpId+'-datetimepickerFrom" class="col-lg-12"></div>';
        }

        html += '<input type="hidden" id="'+this.tmpId+'-datetimepickerFrom_hidden_input">';
        html += '</div>';
        html += '</div>';
        html += '</div>';


        if(this.options.displayLeftMenuPicker == 'show'){
            html += '<div class="col-lg-4">';
        }else{
            html += '<div class="col-lg-5 col-md-offset-2">';
        }


        html += '<div style="overflow:hidden;">';
        html += '<div class="form-group">';


        if(this.options.displayLeftMenuPicker == 'show'){

            html += '<div id="'+this.tmpId+'-datetimepickerTo" class="col-lg-10 col-lg-offset-1"></div>';

        }else{
            html += '<div id="'+this.tmpId+'-datetimepickerTo" class="col-lg-12"></div>';
        }


        html += '<input type="hidden" id="'+this.tmpId+'-datetimepickerTo_hidden_input">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        return html;
    }

    HandleDatePicker.prototype.setPeriodValueTmp = function(periodValue) {
        this.options.periodValueTmp = periodValue;
    }

    HandleDatePicker.prototype.setDefaultPeriodValue = function(apply) {
        if(apply == true) {
            // set les nouvelles valeurs
            this.options.defaultPeriodType = this.options.periodValueTmp;
            this.options.defaultPeriodValue = $( '#' + this.tmpId + '-periodPanel').data('periodvalue');
            this.options.defaultDateFrom = $( '#' + this.tmpId + '-periodPanel').data('datefrom');
            this.options.defaultDateTo = $( '#' + this.tmpId + '-periodPanel').data('dateto');
        } else {
            $( '#' + this.tmpId + '-periodPanel button[data-periodvalue=' + this.options.defaultPeriodType + ']').click();
            $( '#' + this.tmpId + '-periodPanel').data('periodvalue', this.options.defaultPeriodValue);
            $( '#' + this.tmpId + '-periodPanel').data('dateFrom', this.options.defaultDateFrom);
            $( '#' + this.tmpId + '-periodPanel').data('dateTo', this.options.defaultDateTo);

            if(this.options.defaultPeriodType == 'custom') {
                $( '#' + this.tmpId + '-datetimepickerFrom').datepicker('setDate', this.options.defaultDateFrom);
                $( '#' + this.tmpId + '-datetimepickerTo').datepicker('setDate', this.options.defaultDateTo);
            }
        }
    }

    HandleDatePicker.prototype.getHeaderDayTitle = function() {

        var dataDate = this.getFinalChoice();

        if(this.options.scenario == 'scenarioReportsPaymentDatepicker'){

            var langVarScopeDate = languageDatePickerConstants['HEADER_PERIOD_MONTH_YEAR_TEXT'];

        }else if(this.options.scenario == 'scenarioDailySalesDatepicker'){

            var langVarScopeDate = languageDatePickerConstants['HEADER_PERIOD_WITH_DAY_TITLE'];

        }

        langVarScopeDate = langVarScopeDate.replace('#DATE_BEGIN_MONTH#', VAR_LANG_LANG_DATE['DATE_MONTH_' + parseInt( dataDate.fromMonthDetailed)]);
        langVarScopeDate = langVarScopeDate.replace('#DATE_BEGIN_YEAR#', parseInt(dataDate.fromYearDetailed));
        langVarScopeDate = langVarScopeDate.replace('#DATE_BEGIN_DAY#', parseInt(dataDate.fromDayDetailed));
        langVarScopeDate = langVarScopeDate.replace('#DATE_END_MONTH#', VAR_LANG_LANG_DATE['DATE_MONTH_' + parseInt(dataDate.toMonthDetailed)]);
        langVarScopeDate = langVarScopeDate.replace('#DATE_END_YEAR#', parseInt(dataDate.toYearDetailed));
        langVarScopeDate = langVarScopeDate.replace('#DATE_END_DAY#', parseInt(dataDate.toDayDetailed));
        
        //console.log(dataDate);
        
        if(dataDate.period == 'fromBegin') {
            
            if(this.options.specialVarious.currentPage !== undefined) {
                if(this.options.specialVarious.currentPage == 'homePage'){
                    langVarScopeDate = languageDatePickerConstants['DATE_FILTER_FROM_BEGINNING'];
                }else if(this.options.specialVarious.currentPage == 'catalogProductSynthesis'){

                    langVarScopeDate = languageDatePickerConstants['DATE_FILTER_SINCE_OUTPUT'];
                    
                }
                
            }
            
        }

        
        if(this.options.scenario == 'scenarioReportsPaymentDatepicker'){

             if (parseInt(dataDate.fromYearDetailed) == parseInt(dataDate.toYearDetailed)) {
                 if (dataDate.fromMonthDetailed == dataDate.toMonthDetailed) {
                    langVarScopeDate =  VAR_LANG_LANG_DATE['DATE_MONTH_' + parseInt(dataDate.fromMonthDetailed)] + ' ' + parseInt(dataDate.fromYearDetailed);
                 }
             }

        }

        

        return langVarScopeDate;
    }



    $.fn.blvHandleDatePicker = function(options)
    {

        return this.each(function()
        {
            var element = $(this);

            // Return early if this element already has a plugin instance
            if (element.data('blvHandleDatePicker')) return;

            var blvHandleDatePicker = new HandleDatePicker(this, options );

            // Store plugin object in this element's data
            element.data('blvHandleDatePicker', blvHandleDatePicker);

            blvHandleDatePicker.init();

            var elId = blvHandleDatePicker.tmpId;



            $(document).on('click', '#' + elId + '-periodButton', function(event) {

                if($('#' + elId + '-periodPanel').css('display') == 'none'){
                    $('#' + elId + '-periodPanel').slideDown();
                    $('#' + elId + '-periodButton').addClass('periodButtonRollActive');
                    $('#' + elId).addClass('pickerContainerShadow');
                }else{
                    $('#' + elId + '-periodPanel').slideUp(function(){
                        $('#' + elId).removeClass('pickerContainerShadow');
                    });
                    $('#' + elId + '-periodButton').removeClass('periodButtonRollActive');

                }

            });



            $(document).on('click', '#' + elId + '-periodPanel .periodMenu button', function(event) {

                event.preventDefault();
                var periodValue = $(this).data('periodvalue');
                blvHandleDatePicker.update(periodValue);
                $('#' + elId + '-periodPanel button').removeClass('selectDateActif');
                $(this).addClass('selectDateActif');
                $('#' + elId + '-periodPanel').data('periodvalue', periodValue);

                blvHandleDatePicker.setPeriodValueTmp(periodValue);
                if(periodValue == 'custom') {
                    blvHandleDatePicker.setCustomValue();
                }

                $('#' + elId + '-contentTitle').html(blvHandleDatePicker.getHeaderDayTitle());


            });

            // Change From date
            $('#' + elId + '-datetimepickerFrom').on("changeDate", function (e) {
                $('#' + elId + '-periodPanel button').removeClass('selectDateActif');
                $('#' + elId + '-periodPanel button.custom').addClass('selectDateActif');

                var selectDate = $('#' + elId + '-datetimepickerFrom').datepicker('getDate'),
                    toDate = $('#' + elId + '-datetimepickerTo').datepicker('getDate');

                if(selectDate > toDate) {
                    $('#' + elId + '-datetimepickerTo').datepicker('setDate', selectDate);
                }
                blvHandleDatePicker.setCustomValue();

                $('#' + elId + '-contentTitle').html(blvHandleDatePicker.getHeaderDayTitle());

            });

            // Change To date
            $('#' + elId + '-datetimepickerTo').on("changeDate", function (e) {
                $('#' + elId + '-periodPanel button').removeClass('selectDateActif');
                $('#' + elId + '-periodPanel button.custom').addClass('selectDateActif');

                var selectDate = $('#' + elId + '-datetimepickerTo').datepicker('getDate'),
                    fromDate = $('#' + elId + '-datetimepickerFrom').datepicker('getDate');

                if(selectDate < fromDate) {
                    $('#' + elId + '-datetimepickerFrom').datepicker('setDate', selectDate);
                }
                blvHandleDatePicker.setCustomValue();

                $('#' + elId + '-contentTitle').html(blvHandleDatePicker.getHeaderDayTitle());
            });

        });

    };


}(jQuery));







