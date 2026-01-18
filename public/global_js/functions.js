/**
 * Created by mahendran on 6/27/15.
 */
function visibleControl(IDofObject, flag) {
    var item = $('#' + IDofObject);
    if (flag) {
        item.removeClass("hide");
        item.addClass("show");
    }
    else {
        item.removeClass("show");
        item.addClass("hide");
    }
}
//My Small Alert
function mySmallAlert(sTitle, sText, sSuccess) {
	$('.SmallBox').remove();
    var iColor = '#296191';
    var sIcon = 'fa fa-save swing animated';
    if (sSuccess == 0) {
        sIcon = 'fa fa-times swing animated';
        iColor = '#C46A69';
    }
    else if (sSuccess == 1) {
        sIcon = 'fa fa-save swing animated';
        iColor = '#739E73';
    }
    else if (sSuccess == 2) {
        sIcon = 'fa fa-warning swing animated';
        iColor = '#C79121';
    }
    else if (sSuccess == 3) {
        sIcon = 'fa fa-info-circle swing animated';
        iColor = '#296191';
    }
	var closeButton = '<a class="toast-close" onclick="this.parentElement.parentElement.remove()">×</a>';
    $.smallBox({
        title  : '<h1>' + sTitle + '</h1>',
        content: '<div style="">' + sText + '</div>',
        color  : iColor,
        timeout: 4000,
        icon   : sIcon
    });
}

function myAlert(sTitle, sText, sSuccess)
{
	var msg='';
	if (sSuccess == 0) {
       msg = '<div class="alert alert-danger">'+sText+'</div>';
    }
    else if (sSuccess == 1) {
       msg= '<div class="alert alert-success">'+sText+'</div>';
    }
    else if (sSuccess == 2) {
       msg= '<div class="alert alert-warning">'+sText+'</div>';
    }
	$('#msgBox').html(msg);
	
	setTimeout(function(){
		$('#msgBox').html('');
	},4000);
}
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
function AJAX_Post(sURL, sData) {
    var sArray = [];
    var iError = 0;
    var strError = "";
    $.ajax({
        type      : "POST",
        url       : sURL,
        dataType  : 'json',
        async     : false,
        data      : sData,
        beforeSend: function () {
        },
        success   : function (result) {
            sArray = result;
            iError = 0;
        },
        error     : function (xhr, status, error) {
            iError = 1;
            strError = xhr.responseText;
        }
    });
    return ({ERR_NO: iError, DATA: sArray, ERR_TEXT: strError});
}
function AJAX_Post_Image(sURL, sData) {
    var sArray = [];
    var iError = 0;
    var strError = "";
    var deferred;
    deferred= $.ajax({
        type      : "POST",
        url       : sURL,
        dataType  : 'json',
        processData: false,
        contentType: false,
        data      : sData,
        beforeSend: function () {
        },
        success   : function (result) {
            sArray = result;
            iError = 0;
        },
        error     : function (xhr, status, error) {
            iError = 1;
            strError = xhr.responseText;
        }
    });
    return ({ERR_NO: iError, DATA:sArray, ERR_TEXT: strError});
}
function AJAX_Get(sURL) {
    var sArray = [];
    var iError = 0;
    var strError = "";
    var asSync = false;
    $.ajax({
        type      : "GET",
        url       : sURL,
        dataType  : 'json',
        async     : asSync,
        beforeSend: function () {
        },
        success   : function (result) {
            sArray = result;
            iError = 0;
        },
        error     : function (xhr, status, error) {
            iError = 1;
            strError = xhr.responseText;
        }
    });
    return ({ERR_NO: iError, DATA: sArray, ERR_TEXT: strError});
}
function grid_buttons_with_student(id,map_id)
{
	 var strAction = "";
    strAction += '<div class="btn-group" style="width:190px;" >';
    if(acl_VIEW=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-eye btn-sm view" row-id="' + id + '">';
        //strAction += '<i class="fa fa-eye fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_EDIT=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-compose btn-sm edit" row-id="' + id + '">';
        //strAction += '<i class="fa fa-edit fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_DELETE=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-trash-a btn-sm delete" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-trash-o fa-lg"></i>';
        strAction += '</a>';
    }
	 if(acl_EDIT=="1") {
        strAction += '<a href="#" class="btn btn-primary ion-android-person-add btn-sm assign_student" row-id="' + id + '" map-id="' + map_id + '">';
        //strAction += '<i class="fa fa-edit fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_PRINT=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-printer btn-sm print" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-print fa-lg"></i>';
        strAction += '</a>';
    }
    strAction += '</div>';
    return strAction;
}
//----------------------------- Action Buttons -----------------------------------------------------------------------
function grid_buttons(id)
{
    var strAction = "";
    strAction += '<div class="btn-group" style="width:auto;display: flex;float:right;" >';
    if(acl_VIEW=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-eye btn-sm view" row-id="' + id + '">';
        //strAction += '<i class="fa fa-eye fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_EDIT=="1") {
        strAction += '<a href="#" class="btn btn-success ion ion-compose btn-sm edit" row-id="' + id + '">';
        //strAction += '<i class="fa fa-edit fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_DELETE=="1") {
        strAction += '<a href="#" class="btn btn-danger ion ion-trash-a btn-sm delete" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-trash-o fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_PRINT=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-printer btn-sm print" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-print fa-lg"></i>';
        strAction += '</a>';
    }
    strAction += '</div>';
    return strAction;
}


function grid_buttons_student(id)
{
    var strAction = "";
    strAction += '<div class="btn-group" style="width:auto;display: flex;float:right;" >';
	if(acl_ADD=="1") {
        strAction += '<a href="#" class="btn btn-success ion ion-ios-copy btn-sm copy" title="Copy Student" row-id="' + id + '">';
        //strAction += '<i class="fa fa-eye fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_VIEW=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-eye btn-sm view" row-id="' + id + '">';
        //strAction += '<i class="fa fa-eye fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_EDIT=="1") {
        strAction += '<a href="#" class="btn btn-success ion ion-compose btn-sm edit" row-id="' + id + '">';
        //strAction += '<i class="fa fa-edit fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_DELETE=="1") {
        strAction += '<a href="#" class="btn btn-danger ion ion-trash-a btn-sm delete" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-trash-o fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_PRINT=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-printer btn-sm print" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-print fa-lg"></i>';
        strAction += '</a>';
    }
    strAction += '</div>';
    return strAction;
}
//-----------------------------  Custom Action Buttons -----------------------------------------------------------------------
function grid_buttons_customs(id,strEditName,strViewName,printUrl)
{
    var strAction = "";
    strAction += '<div class="btn-group" style="width:auto;display: flex;float:right;" >';
    if(acl_VIEW=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-eye btn-sm view '+strViewName+'" row-id="' + id + '">';
        //strAction += '<i class="fa fa-eye fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_EDIT=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-compose btn-sm edit '+strEditName+'" row-id="' + id + '">';
        //strAction += '<i class="fa fa-edit fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_DELETE=="1") {
        strAction += '<a href="#" class="btn btn-primary ion ion-trash-a btn-sm delete" row-id="' + id + '" >';
        //strAction += '<i class="fa fa-trash-o fa-lg"></i>';
        strAction += '</a>';
    }
    if(acl_PRINT=="1") {
        if(printUrl != "")
        {
            strAction += '<a class="btn btn-primary ion ion-printer btn-sm print" href="'+printUrl+id+'" target="_blank">';
            //strAction += '<i class="fa fa-print fa-lg"></i>';
            strAction += '</a>';
        }
        else
        {
            strAction += '<a href="#" class="btn btn-primary ion ion-printer btn-sm print" href="'+id+'">';
            //strAction += '<i class="fa fa-print fa-lg"></i>';
            strAction += '</a>';
        }
    }
    return strAction;
}
function grid_buttons_docs(id,viewURL)
{
    var strAction = "";
    strAction += '<div class="btn-group" style="width:140px;" >';
	strAction += '<a class="btn btn-primary ion ion-eye btn-sm view" href="'+viewURL+id+'" target="_blank">';
		//strAction += '<i class="fa fa-eye fa-lg"></i>';
	strAction += '</a>';
	strAction += '</div>';
    return strAction;
}
function grid_buttons_download_docs(id,download_link)
{
    var strAction = "";
    strAction += '<div class="btn-group" style="width:140px;" >';
	strAction += '<a class="btn btn-primary ion ion-arrow-down-a btn-sm view" href="'+download_link+'" target="_blank">';
	//strAction += '<i class="fa fa-eye fa-lg"></i>';
	strAction += '</a>';
	strAction += '</div>';
    return strAction;
}
//-----------------------------  Custom Action Buttons -----------------------------------------------------------------------
function grid_buttons_pwr(id)
{
    var strAction = "";
    strAction += '<div class="btn-group btn-group-justified display-inline  text-align-center" style="padding-right: 5px" >';   
        strAction += '<a class="btn btn-xs btn-primary  reset" row-id="' + id + '">';
        strAction += '<i class="fa fa-undo fa-lg"></i>';
        strAction += '</a>';
	strAction += '</div>';
    return strAction;
}
//-------------------------- Clear the Form ----------------------------------------------------------------------------
function clearForm(frmName) {
    $('#' + frmName)[0].reset();
    $("#" + frmName + " select").each(function () {
        $(this).val(0);
        $(this).select2("val", 0);
		
    });
}
//------------------------------------ Enable Disable  Form Elements ---------------------------------------------------
function glbControlEnable(bolFlag) {
    if (bolFlag) {
        $("#myTabContent").find("input, button, textarea, select").attr("disabled", false);
        $("#myTabContent").find("input, textarea").removeClass("bg-color-lighten");
		$(".customAddNewClientVendor").attr("disabled", false);
		$(".customAddNewProduct").attr("disabled", false);
		$(".customDisableDiv").removeClass("hide");
        $("#btnSave").show();
    }
    else {
        $("#myTabContent").find("input, button, textarea, select").attr("disabled", true);
        $("#myTabContent").find("input, textarea").addClass("bg-color-lighten");
        $(".editRecord ").attr("disabled", true);
		$(".customAddNewClientVendor").attr("disabled", true);
		$(".customAddNewProduct").attr("disabled", true);
		$(".customDisableDiv").addClass("hide");
        $("#btnSave").hide();
    }
}
//------------------- populate edit entries-----------------------------------------------------------------------------
function populateEditEntries(iID,strURL) {
    iActiveID = iID;
    var arrForms=[]; // Keep Form Values
    var strElementType; // Keep element type
    var objFormData =
    {
        pAction: 'GETREC',
        KEY_ID : iActiveID
    };
    var objMyPost = AJAX_Post(strURL, objFormData);
    if (objMyPost.ERR_NO === 0) {
        if (objMyPost.DATA.DBStatus === 'OK') {
            visibleControl('widForm', true);
            visibleControl('widGrid', false);
            strActionMode = 'EDIT';
            arrForms=objMyPost.DATA.data[0];
            $.each( arrForms, function( key, value ) {
                if ($("#"+key).length > 0) {  // check if exists or not
                    strElementType= $("#"+key).attr("type");
                    if(strElementType == "text") {  // if textbox
                        $("#" + key).val(value);
                    }
                   else if(strElementType == "select") {  // if Select Box
                        $("#" + key).select2("val",value);
                    }
                   else if(strElementType == "multiselect") {  // if Select Box with multi select
                        var arrValues = $.parseJSON(stripslashes(value));
                        $("#" + key).select2("val", arrValues);
                    }
                    else if(strElementType == "image") {  // if Image
                        $("#" + key).attr("src",value);
                    }
                }
            });
        }
    }
    else {
        mySmallAlert('Error...!', 'There was an error', 0);
    }
}
//-------------Strip Slashed -------------------------
function stripslashes(str) {
    str = str.replace(/\\'/g, '\'');
    str = str.replace(/\\"/g, '"');
    str = str.replace(/\\0/g, '\0');
    str = str.replace(/\\\\/g, '\\');
    return str;
}
//Function Used for Edit
function fnEdit(strUrl)
{
    oTable.delegate('a.edit', 'click', function (e) {
        e.preventDefault();
        /*
         var nRow = $(this).parents('tr')[0];
         var aData = oTable.fnGetData(nRow);
         iActiveID = parseInt(aData[0]);
         */
        iActiveID = $(this).attr("row-id");
        //empty the form fields
        clearForm("frmForm");
        populateEditEntries(iActiveID, strUrl);
        glbControlEnable(true);
    });
}
//Function Used for View
function fnView(strUrl)
{
    oTable.delegate('a.view', 'click', function (e) {
        e.preventDefault();
        /*
         var nRow = $(this).parents('tr')[0];
         var aData = oTable.fnGetData(nRow);
         iActiveID = parseInt(aData[0]);
         */
        iActiveID=$(this).attr("row-id");
        //empty the form fields
        clearForm("frmForm");
        populateEditEntries(iActiveID,strUrl);
        glbControlEnable(false);
    });
}
//Function Used for Delete
function fnDelete(strUrl)
{
    oTable.delegate('a.delete', 'click', function (e) {
        e.preventDefault();
        /*
         var nRow = $(this).parents('tr')[0];
         var aData = oTable.fnGetData(nRow);
         intID = aData[0];
         */
        intID=$(this).attr("row-id");
        var url = "pAction=DELETE&ID=" + intID;
        $.SmartMessageBox({
            title  : "Alert!",
            content: "Do you confirm to DELETE?",
            buttons: '[Yes][No]'
        }, function (ButtonPressed) {
            if (ButtonPressed === "Yes") {
                var objFormData =
                {
                    pAction: 'DELETE',
                    KEY_ID : intID
                };
                var objMyPost = AJAX_Post(strUrl, objFormData);
                if (objMyPost.ERR_NO === 0) {
                    if (objMyPost.DATA.DBStatus === 'OK') {
                        oTable.fnDraw();
                        mySmallAlert('Success', 'Record  Deleted successfully', 1);
                    }
					else if (objMyPost.DATA.DBStatus === 'USED') {
                        oTable.fnDraw();
                        mySmallAlert('Success', "Record in used. Can't Delete", 2);
                    }
                    else {
                        mySmallAlert('Error...!', 'There was an error', 0);
                    }
                }
            }
            if (ButtonPressed === "No") {
            }
        });
    });
}
//function used to find the duplicate
function fn_validate_duplicate(iID,tableName,fieldName,strURL)
{
    var objFormData =
    {
        tableName: tableName,
        fieldName: fieldName,
        KEY_ID : iID,
		EDIT_ID : iActiveID
    };
    var objMyPost = AJAX_Post(strURL, objFormData);
        if (objMyPost.DATA.DBStatus === 'ERR') {
            return true;
        }
}
function fn_validate_duplicate_multiple(strURL,objMasterData)
{
	var objFormData={
		
		FORM_DATA : JSON.stringify(objMasterData)
	};
    var objMyPost = AJAX_Post(strURL, objFormData);
        if (objMyPost.DATA.DBStatus === 'ERR') {
            return true;
        }
}
//function used to find the duplicate for two fields
function fn_validate_duplicate_two(iVal1,iVal2,field1,field2,tableName,strURL)
{
    var objFormData =
    {
        tableName: tableName,
        field1: field1,
        field2: field2,
        value1: iVal1,
        value2: iVal2,
		EDIT_ID : iActiveID
    };
    var objMyPost = AJAX_Post(strURL, objFormData);
        if (objMyPost.DATA.DBStatus === 'ERR') {
            return true;
        }
}
/************************* functions used to maintain transaction entries ***************************/
function IsProductExist(productId) {
    var intExist = 0;
    for (var i = 0; i < objMyDetailRecords.length; i++) {
        if (bEditDetailRecord) {
            if (objMyDetailRecords[iEditIndex].product_id == productId)
                intExist = 0;
            else
                intExist = 1;
        }
        else {
            if (objMyDetailRecords[i].product_id == productId)
                intExist = 1;
        }
    }
    return intExist;
}
function deleteDetailArray(iIndex) {
    objMyDetailRecords.splice(iIndex, 1);
}
function deleteDetailArray2(iIndex) {
    objMyDetailRecords2.splice(iIndex, 1);
}
function deleteDetailArray3(iIndex) {
    objMyDetailRecords3.splice(iIndex, 1);
}
function editDetailArray(objMyEditItem, iIndex) {
    //console.log("original Array");
    //console.log(objMyDetailRecords[iIndex]);
    //console.log("Update Array");
    //console.log(objMyEditItem);
    $.each(objMyDetailRecords[iIndex],function(index,value){
        objMyDetailRecords[iIndex][index]=objMyEditItem[index];
    });
    bEditDetailRecord = false;
    populateDetailRecords(objMyDetailRecords);
}
function editDetailArray2(objMyEditItem, iIndex) {
    //console.log("original Array");
    //console.log(objMyDetailRecords[iIndex]);
    //console.log("Update Array");
    //console.log(objMyEditItem);
    $.each(objMyDetailRecords2[iIndex],function(index,value){
        objMyDetailRecords2[iIndex][index]=objMyEditItem[index];
    });
    bEditDetailRecord2 = false;
    populateDetailRecords2(objMyDetailRecords2);
}
function editDetailArray3(objMyEditItem, iIndex) {
    //console.log("original Array");
    //console.log(objMyDetailRecords[iIndex]);
    //console.log("Update Array");
    //console.log(objMyEditItem);
    $.each(objMyDetailRecords3[iIndex],function(index,value){
        objMyDetailRecords3[iIndex][index]=objMyEditItem[index];
    });
    bEditDetailRecord3 = false;
    populateDetailRecords3(objMyDetailRecords3);
}
function fpercent(quantity, percent) {
    return quantity * percent / 100;
}
//--------------------Get Invoice details based on the Master ID------------------------------------------------------
function getInvoiceNotes(ID,Url) {
    var objFormData =
    {
        module_id: ID
    };
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------populate Vendors--------------------------------------------------------------------------------
function populateVendors(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Vendor---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.vendor_name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function fpercent(quantity, percent) {
    return quantity * percent / 100;
}
//--------------------Get Invoice details based on the Master ID------------------------------------------------------
function getDetailRecordsByMasterID(ID,Url) {
    var objFormData =
    {
        masterID: ID
    };
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------Get Invoice details based on the Master ID------------------------------------------------------
function getInvoiceRecordsByVendorID(ID,Url) {
    var objFormData =
    {
        vendor_id: ID
    };
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------Get Invoice details based on the Master ID------------------------------------------------------
function getInvoiceNotes(ID,Url) {
    var objFormData =
    {
        module_id: ID
    };
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------Get Invoice details based on the Master ID------------------------------------------------------
function getInvoiceRecordsByClientID(client_id,Url) {
    var objFormData =
    {
        client_id: client_id
    };
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------Get Invoice details based on the Master ID------------------------------------------------------
function getPriceByProductID(ID,Url,price_field) {
    var objFormData =
    {
        productID: ID,
		priceField : price_field
    };
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------Get Tax Details Based on Tax ID------------------------------------------------------
function getTaxDetail(ID,Url) {
    var objFormData =
    {
        pAction: 'GETREC',
        KEY_ID : ID
    };
	if(ID > 0)
	{
    var objGet = AJAX_Post(Url,objFormData);
    if (objGet.DATA.DBStatus === 'OK') {
        //console.log(objGet.DATA);
        if(objGet.DATA.data.length >0)
            return objGet.DATA.data[0]['tax_per'];
        else
            return 0;
    }
    else
    {
        return "";
    }
	}
	else
		return "";
}
//--------------------populate Activity--------------------------------------------------------------------------------
function populateActivity(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Activity---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateOptionValue(strObjectName,Url,title) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
		if(strObjectName=='approval_staff')
	    items += "<option value=''><column>- "+title+" -</column></option>";
		else
        items += "<option value='0'><column>- "+title+" -</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
	
    cboObject.select2("val", "0");
	
	
}
//--------------------populate Teacher--------------------------------------------------------------------------------
function populateTeacher(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Teacher---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Agegroups--------------------------------------------------------------------------------
function populateAgegroups(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Age Groups---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Agegroups--------------------------------------------------------------------------------
function populateFoods(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Food---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Child--------------------------------------------------------------------------------
function populateChild(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Child---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Medicine--------------------------------------------------------------------------------
function populateMedicine(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Medicine---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Activity Type--------------------------------------------------------------------------------
function populateActivityType(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Activity Type---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Alergies--------------------------------------------------------------------------------
function populateAlergies(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Alergies---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.f1 + "'><column>" + item.f1 + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate staff--------------------------------------------------------------------------------
function populateStaff(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Staff---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.first_name +" " +item.last_name +"</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Purchase Request--------------------------------------------------------------------------------
function populatePurchaserequest(strObjectName,Url,vendor_id) {
    var cboObject = $('#' + strObjectName);
	 var objFormData =
    {
        vendor_id: vendor_id
    };
    var objGet = AJAX_Post(Url,objFormData);
    //var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Choose Purchase Request---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.refno + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Vendor address & contact person--------------------------------------------------------------------------------
function populateVendoraddresscontact(strObjectName,strObjectName2,Url,vendor_id) {
    var cboObject = $('#' + strObjectName);
	var cboObject2 = $('#' + strObjectName2);
	 var objFormData =
    {
        vendor_id: vendor_id
    };
    var objGet = AJAX_Post(Url,objFormData);
    //var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
       
        //items += "<option value='0'><column>---Choose Quotation---</column></option>";
		iVal = objGet.DATA.business_address+" , "+objGet.DATA.town_city+" , "+objGet.DATA.state_province+" , "+objGet.DATA.postal_zip_code;
       	cboObject.val(iVal);
       	/*cboObject.val(objGet.DATA.business_address);*/
		cboObject2.val(objGet.DATA.contact_person);
		
    }
    
    //cboObject.select2("val", "0");
}
//--------------------populate Permissions by role --------------------------------------------------------------------------------
function getPermissionlistByRole(role_id,Url) {
    
	 var objFormData =
    {
        role_id: role_id
    };
    var objGet = AJAX_Post(Url,objFormData);
    //var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
		return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------Get Available permissons --------------------------------------------------------------------------------
function getAvailablePermissions(module_name,role_id,Url) {
	 var objFormData =
    {
        role_id: role_id,
        module_name: module_name
    };
    var objGet = AJAX_Post(Url,objFormData);
    //var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
		return objGet.DATA.DBData;
    }
    else
    {
        return "";
    }
}
//--------------------------- populate Modules ---------------------------------------------------------------------
function populateModules(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Module---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.module_name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------------- Generate reference number ---------------------------------------------------------------------
function generateReferenceNumber() {
    var objGet = AJAX_Get("./acl/getrefno");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo.html(objGet.DATA.REFNO);
    }
}
function generateReferenceNumberDO() {
    var objGet = AJAX_Get("./acl/getrefnodo");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo.html(objGet.DATA.REFNO);
    }
}
function generateReferenceNumberRO() {
    var objGet = AJAX_Get("./acl/getrefnoro");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo.html(objGet.DATA.REFNO);
    }
}
//--------------------------- Generate reference number 1---------------------------------------------------------------------
function generateReferenceNumber1() {
    var objGet = AJAX_Get("./acl/getrefno1");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo1.html(objGet.DATA.REFNO);
    }
}
function generateTCNo() {
    var objGet = AJAX_Get("./acl/gettcno");
    if (objGet.DATA.DBStatus === 'OK') {
		
        refno.val(objGet.DATA.REFNO);
        spanRefNo1.html(objGet.DATA.REFNO);
    }
}
function generateExpenseNo() {
    var objGet = AJAX_Get("./acl/getexpenseno");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo1.html(objGet.DATA.REFNO);
    }
}
function generateApplicationNo() {
    var objGet = AJAX_Get("./acl/getappno");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo1.html(objGet.DATA.REFNO);
    }
}
function generateFineNo() {
    var objGet = AJAX_Get("./acl/getfineno");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo1.html(objGet.DATA.REFNO);
    }
}
function generateFeesNo() {
    var objGet = AJAX_Get("./acl/getfeesno");
    if (objGet.DATA.DBStatus === 'OK') {
        refno.val(objGet.DATA.REFNO);
        spanRefNo1.html(objGet.DATA.REFNO);
    }
}
//--------------------populate Roles--------------------------------------------------------------------------------
function populateRoles(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Choose Role---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.role_name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Roles--------------------------------------------------------------------------------
function populateRolesall(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---All Role---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.role_name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateMultipleOptionValue(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        //items += "<option value='0'><column>---Select Department---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
  //  cboObject.select2("val", "0");
}
function populateMultipleOptionValue3(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        //items += "<option value='0'><column>---Select Department---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
	cboObject.select2({
        minimumInputLength: 3
    });
  //  cboObject.select2("val", "0");
}
function populateMultipleOptionValuename(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        //items += "<option value='0'><column>---Select Department---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
  //  cboObject.select2("val", "0");
}
//--------------------populate Department--------------------------------------------------------------------------------
function populateDepartment(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Department---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateNationality(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Nationality---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateParents(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Parent---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateClasses(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Class---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateSubject(strObjectName,Url,objFormData) {
	
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Post(Url,objFormData);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Subject---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateSection(strObjectName,Url,objFormData) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Post(Url,objFormData);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Section---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateDependentOptionValue(strObjectName,Url,objFormData,title) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Post(Url,objFormData);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>- "+title+" -</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
function populateDependentMultipleSelectedOptionValue(strObjectName,Url,objFormData,title) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Post(Url,objFormData);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        //items += "<option value='0'><column>--"+title+"---</column></option>";
		var i=0;
		var selectedValues = new Array();

        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "' selected><column>" + item.name + "</column></option>";
			selectedValues[i]=item.id;
			i++;
        });
    }
    cboObject.html(items);
    cboObject.select2("val", selectedValues);
    
}
function populateDependentMultipleOptionValue(strObjectName,Url,objFormData,title) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Post(Url,objFormData);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        //items += "<option value='0'><column>--"+title+"---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    //cboObject.select2("val", "0");
}
function populateLanguage(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Second Language---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//--------------------populate Payment Types--------------------------------------------------------------------------------
function populateDesignation(strObjectName,Url) {
    var cboObject = $('#' + strObjectName);
    var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";		
    if (objGet.DATA.DBStatus === 'OK') {
        var arrMyData = objGet.DATA.DBData;
        items += "<option value='0'><column>---Select Designation---</column></option>";
        $.each(arrMyData, function (index, item) {
            items += "<option value='" + item.id + "'><column>" + item.name + "</column></option>";
        });
    }
    cboObject.html(items);
    cboObject.select2("val", "0");
}
//Calculate the Detail Entries
function fnDetailCalculation(objArray,index)
{
    //start the calculation
  //  var objTax=getTaxDetail(objArray.tax_id,"./taxsettings/getrec");
    var iTax=objArray.tax_id;
	
	
    //for tax 2
    //var objTax=getTaxDetail(objArray.tax2,"./taxsettings/getrec");
    //var iTax2=objTax;
	var iTax2=0;
    var iDiscount=objArray.discount;
    var iDiscountAmount=0;
    var iTaxAmount=0;
    var iTaxAmount2=0;
    var iNetAmount=0;
	var iExltax=0;
    var percentFlag=true;
	var disc_type = $("#disc_type").val();
    var iTotalAmount=parseFloat(objArray.qty) * parseFloat(objArray.unit_price);
    if(percentFlag)
    {
        //iDiscountAmount=fpercent(iTotalAmount,iDiscount);
		iDiscountAmount=parseFloat(iDiscount);
		if(disc_type == 'Percentage')
		{
			iDiscountAmount = iTotalAmount * (iDiscountAmount/100);
		}
		
	
		if(!(iDiscountAmount > 0))
		{
			iDiscountAmount=0;
		}
		
        //iTaxAmount=fpercent(iTotalAmount-iDiscountAmount,iTax);
        if(objArray.tax_inclusive != "1")
		    iTaxAmount=fpercent(iTotalAmount-iDiscountAmount,objArray.tax_id);
        else //tax inclusive
            iTaxAmount=iTotalAmount-(iTotalAmount*100)/106;
        iTaxAmount=parseFloat(iTaxAmount).toFixed(2);
        iTaxAmount=parseFloat(iTaxAmount);
		
        //iTaxAmount2=fpercent(iTotalAmount-iDiscountAmount,iTax2);
		iTaxAmount2=0;
		iExltax=parseFloat(iTotalAmount-iDiscountAmount);
		
        if(objArray.tax_inclusive != "1")
        {
            iNetAmount = (iTotalAmount + iTaxAmount + iTaxAmount2) - iDiscountAmount;
	   
		    iExltaxAmount = iExltaxAmount + iExltax;
        }
        else
        {
            iNetAmount=iTotalAmount - iDiscountAmount;
            iExltaxAmount = (iTotalAmount-iTaxAmount) - iDiscountAmount;
        }
//     iExltaxAmount = iExltaxAmount + iExltax - iTaxAmount; 
    // iExltaxAmount = iExltaxAmount + iExltax;
        objMyDetailRecords[index].SUB_TOTAL=iNetAmount;
        objMyDetailRecords[index].TAX_AMOUNT=iTaxAmount;
        //objMyDetailRecords[index].TAX_AMOUNT2=iTaxAmount2;
		//objMyDetailRecords[index].TAX_PERCENT=iTax;
		if(objArray.tax_inclusive != "1")
        {
		    objMyDetailRecords[index].TAX_PERCENT=objArray.tax_id;
		}
		else
		{
		    objMyDetailRecords[index].TAX_PERCENT=0;
		}
        objMyDetailRecords[index].DISCOUNT_AMOUNT=iDiscountAmount;
        objMyDetailRecords[index].TAX_INCL_AMOUNT=iNetAmount;
        //Total
        iGrossAmountTotal=iGrossAmountTotal + iTotalAmount;
        iDiscountAmountTotal=iDiscountAmountTotal + iDiscountAmount;
        iTAXAmountTotal=iTAXAmountTotal + iTaxAmount + iTaxAmount2;
        iNetAmountTotal=iNetAmountTotal+iNetAmount;
    }
    //end the calculation
}
function populateProductdescription(strObjectName,Url,product_id) {
    var cboObject = $('#' + strObjectName);
	
	 var objFormData =
    {
        product_id: product_id
    };
    var objGet = AJAX_Post(Url,objFormData);
    //var objGet = AJAX_Get(Url);
    var items = "";
    var iVal = "";
    if (objGet.DATA.DBStatus === 'OK') {
       
        //items += "<option value='0'><column>---Choose Quotation---</column></option>";
		iVal = objGet.DATA.DBData; //+" , "+objGet.DATA.town_city+" , "+objGet.DATA.state_province+" , "+objGet.DATA.postal_zip_code;
       	cboObject.val(iVal);
		/*cboObject.val(objGet.DATA.town_city);
		cboObject.val(objGet.DATA.state_province);
		cboObject.val(objGet.DATA.postal_zip_code);*/
		//cboObject2.val(objGet.DATA.contact_person);
		
    }
    
    //cboObject.select2("val", "0");
}
/*------- report detail section popup function -------------------*/
function popup_plus_icon(recordID)
{
	strAction = '';
	 strAction += '<a class="btn btn-pink ion ion-plus btn-sm" href="#record_'+recordID+'" data-toggle="modal">';
	 strAction += '</a>';	
	return strAction;
}