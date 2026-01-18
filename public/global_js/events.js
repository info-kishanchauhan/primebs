/**
 * Created by mahendran on 6/27/15.
 */
$(document).ready(function () {
	var discountamt = 0;
	$("#btnBack").click(function (e) {
			
			$(".select2").select2("close");
				
	});
    
	$("#qty").change(function(){

        if($("#qty").val() < 0)
        {
            alert("Qty is Required");
            return false;
        }
        if($("#unit_price").val() < 0)
        {
            alert("Unit Price is Required");
            return false;
        }

        var total=parseFloat($("#qty").val()) * parseFloat($("#unit_price").val());
        $("#total_1").val(total);
		 calculate_details();
    });

    $("#unit_price").change(function(){

        if($("#qty").val() < 0)
        {
            alert("Qty is Required");
            return false;
        }
        if($("#unit_price").val() < 0)
        {
            alert("Unit Price is Required");
            return false;
        }

        var total=parseFloat($("#qty").val()) * parseFloat($("#unit_price").val());
        $("#total_1").val(total);
		 calculate_details();

    });

    $("#tax_inclusive").change(function(){

        calculate_details();
    });

    $("#tax_id").change(function(){

        calculate_details();
    });

    $("#tax2").change(function(){

        calculate_details();
    });

    $("#discount").change(function(){
		var vdiscount = $("#discount").val();
		if($("#discount").val() < 0)
        {
            alert("Discount is Required");
            return false;
        }
		if($("#discount").val() >= 0)
		{
			var disc_type = $("#disc_type").val();
			console.log(disc_type);
			if(disc_type == 'Percentage')
			{
				console.log(1);
				var subamt = parseFloat($("#qty").val()) * parseFloat($("#unit_price").val());
				discountamt = subamt * (vdiscount/100);
			}
			else
			    discountamt = vdiscount;
		}
		
		console.log(discountamt);
		var total = parseFloat($("#qty").val()) * parseFloat($("#unit_price").val()) - discountamt;
        $("#total_1").val(total);
		
        calculate_details();
    });

    function calculate_details()
    {
        if($("#tax_id").val()=="")
        {
            alert("Tax is Required");
            return false;
        }

        if(document.getElementById('tax_inclusive').checked)
        {
            var total=parseFloat($("#qty").val()) * $("#unit_price").val() - discountamt;
            $("#total_2").val(total);
			
        }
        else // Calculate Tax
        {
            var total=parseFloat($("#qty").val()) * $("#unit_price").val();
			
            //var discount=fpercent(total,$("#discount").val());
			var discount=discountamt;
            var total_after_doscount=total-discount;
			
			
            if(parseFloat($("#tax_id").val())>0)
                var tax1=fpercent(total_after_doscount,$("#tax_id").val());
            else
                var tax1=0;

            /*if(parseFloat($("#tax2").val())>0)
                var tax2=fpercent(total_after_doscount,$("#tax2").val());
            else
                var tax2=0;*/

            //var final_total=total_after_doscount+parseFloat(tax1)+parseFloat(tax2);
			
			
			var final_total=total_after_doscount+parseFloat(tax1);
			
            $("#total_2").val(final_total);

        }
        return false;
    }
    btnBack.click(function (e) {
        e.stopPropagation();
        visibleControl("widForm", false);
        visibleControl("widGrid", true);

    });

    btnNew.click(function (e) {
        e.stopPropagation();

        objMyDetailRecords.length=0;
        tblDetailsListBody.html('');

        visibleControl("widForm", true);
        visibleControl("widGrid", false);
        clearForm("frmForm");
        strActionMode="ADD";
        glbControlEnable(true);
       // generateReferenceNumber();
		//generateReferenceNumber1();

    });

    clsLanguages.click(function () {

        document.cookie = "SMS_LANG" + '=' + $(this).attr("value")+';';
        $(this).addClass("active");
        location.reload();

    });
    //Scripts are used to manage the transaction entries globally.
    btnAddList.click(function (e) {

            e.stopPropagation();

			discountamt = 0;
            //Read All form elements
            var input="";
            var iType="";
            var iId="";
            var iVal="";
            var objMyAddItem={};
            var flagValidate=true;


            //Initially set the detail id
            objMyAddItem.DETAIL_KEY_ID=iEditDetailKeyID;


            //Fetch Details Form Values
            $('#frmDetailsForm input, #frmDetailsForm select,#frmDetailsForm textarea').each( function(index) {

                input = $(this);
                iType=input.attr("type");
                iId=input.attr("id");
                iVal=input.val();

                //validate the required fields
                if(input.attr("validate"))
                {
                    //validate the select boxes
					
					
                    if(iType == "select") {
                        if (iVal == '0')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
					if(iType == "multiselect") {

                        if(iVal== ''  || iVal == null)

                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);

                            flagValidate=false;

                            return false;
                        }



                    }
                    if(iType == "text" || iType == "number") {
                        if (iVal == '')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
                }
				
				var stock_not_available = false;
				if (typeof AVAIL_STOCK === 'undefined') {
					 
				} 
				else 
				{
					if(strActionMode == 'ADD' )
					{
						if( Number($('#qty').val()) > AVAIL_STOCK)
							stock_not_available = true;
					}
				}
				
				if(stock_not_available)
				{
					mySmallAlert('Error...!', 'Available stock only '+AVAIL_STOCK, 0);

					 flagValidate=false;

					 return false;
				}
				
				var s_time = $('#start_time').val();

				var e_time = $('#end_time').val();
				
				
				var start_date = $('#start_date').val();

				if((e_time < s_time))  
				{

					 mySmallAlert('Error...!', 'Period end time must be greater than  to start time', 0);

					 flagValidate=false;

					 return false;

				} 
				var reser_btn = document.getElementById("btnReset");
			   
			    if(typeof(reser_btn) != 'undefined' && reser_btn != null){
			        if(s_time != undefined)
					{
						var s_time_duplicate=false;
						
						if(bEditDetailRecord)
						{
							for (var i = 0; i < objMyDetailRecords.length; i++) 
							{
								if(i != iEditIndex)
								{
									
									if((s_time) == (objMyDetailRecords[i].start_time) )
									{
										s_time_duplicate=true;
										break;
									}
								}
							}
						}
						else
						{
							for (var i = 0; i < objMyDetailRecords.length; i++) 
							{
								if((s_time) == (objMyDetailRecords[i].start_time))
								{
									s_time_duplicate=true;
									break;
								}
							}
						}
						if(s_time_duplicate)
						{
							mySmallAlert('Error...!', 'Start Time already exist', 0);

							 flagValidate=false;

							 return false;
						}
					}
				    
				}
				
				
				var fee_id = $('#fee_id').val();
				
				var fee_duplicate=false;
				
				if(bEditDetailRecord)
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(i != iEditIndex)
						{
						
							if(Number(fee_id) == Number(objMyDetailRecords[i].fee_id))
							{
								fee_duplicate=true;
								break;
							}
						}
					}
				}
				else
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(Number(fee_id) == Number(objMyDetailRecords[i].fee_id))
						{
							fee_duplicate=true;
							break;
						}
					}
				}
				if(fee_duplicate)
				{
					mySmallAlert('Error...!', 'Fee type already exist', 0);

					 flagValidate=false;

					 return false;
				}
				
				var subject_id = $('#subject_id').val();
				
				var subject_duplicate=false;
				
				if(bEditDetailRecord)
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(i != iEditIndex)
						{
						
							if(Number(subject_id) == Number(objMyDetailRecords[i].subject_id))
							{
								subject_duplicate=true;
								break;
							}
						}
					}
				}
				else
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(Number(subject_id) == Number(objMyDetailRecords[i].subject_id))
						{
							subject_duplicate=true;
							break;
						}
					}
				}
				if(subject_duplicate)
				{
					mySmallAlert('Error...!', 'Subject already exist', 0);

					 flagValidate=false;

					 return false;
				}
				
				
				var student_id = document.getElementById("student_id");
			   
			if(typeof(student_id) == 'undefined' || student_id == null)
			{
				
				var route_area_id = $('#route_area_id').val();
				
				var route_duplicate=false;
				
				if(bEditDetailRecord)
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(i != iEditIndex)
						{
						
							if(Number(route_area_id) == Number(objMyDetailRecords[i].route_area_id))
							{
								route_duplicate=true;
								break;
							}
						}
					}
				}
				else
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(Number(route_area_id) == Number(objMyDetailRecords[i].route_area_id))
						{
							route_duplicate=true;
							break;
						}
					}
				}
				if(route_duplicate)
				{
					mySmallAlert('Error...!', 'Route Area already exist', 0);

					 flagValidate=false;

					 return false;
				}
			}
			else
			{
				var route_area_id = $('#route_area_id').val();
				
				var sroute_duplicate=false;
				
				if(bEditDetailRecord)
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(i != iEditIndex)
						{
						
							if(Number(student_id) == Number(objMyDetailRecords[i].student_id))
							{
								sroute_duplicate=true;
								break;
							}
						}
					}
				}
				else
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(Number(student_id) == Number(objMyDetailRecords[i].student_id))
						{
							sroute_duplicate=true;
							break;
						}
					}
				}
				if(sroute_duplicate)
				{
					mySmallAlert('Error...!', 'Student already assigned', 0);

					 flagValidate=false;

					 return false;
				}
			}
			
			var route_area_id = document.getElementById("route_area_id");
			   
			if(typeof(route_area_id) == 'undefined' || route_area_id == null)
			{	
				
				var class_id = $('#class_id').val();
				
				var class_duplicate=false;
				
				if(bEditDetailRecord)
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(i != iEditIndex)
						{
						
							if(Number(class_id) == Number(objMyDetailRecords[i].class_id))
							{
								class_duplicate=true;
								break;
							}
						}
					}
				}
				else
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(Number(class_id) == Number(objMyDetailRecords[i].class_id))
						{
							class_duplicate=true;
							break;
						}
					}
				}
				if(class_duplicate)
				{
					mySmallAlert('Error...!', 'Class already exist', 0);

					 flagValidate=false;

					 return false;
				}
			}	
				var product_id = $('#product_id').val();
				
				var product_duplicate=false;
				
				
				
				if(bEditDetailRecord)
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(i != iEditIndex)
						{
						
							if(Number(product_id) == Number(objMyDetailRecords[i].product_id))
							{
								product_duplicate=true;
								break;
							}
						}
					}
				}
				else
				{
					for (var i = 0; i < objMyDetailRecords.length; i++) 
					{
						if(Number(product_id) == Number(objMyDetailRecords[i].product_id))
						{
							product_duplicate=true;
							break;
						}
						
						
					}
				}
				if(product_duplicate)
				{
					mySmallAlert('Error...!', 'Product already exist', 0);

					 flagValidate=false;

					 return false;
				}
				
				
				
				
				
				/*if(!bEditDetailRecord && )
				{
					var due_date = $('#due_date').val();
					due_date = due_date.split('-');
					due_date = due_date[2]+'-'+due_date[1]+'-'+due_date[0];
					
					if(new Date() > new Date(due_date))
					{
						mySmallAlert('Error...!', 'Due date must be future date', 0);

						 flagValidate=false;

						 return false;
					}
				}*/
                if(input.attr("name")) // if input name exists parse values and store to array
                {
					
                    if(iType == "select")  //if only select we parse the text and val
                    {
                        objMyAddItem[iId] = iVal;
                        objMyAddItem[iId + "_desc"] = $("#"+iId+" option:selected").text();
                    }
					if(iType == "multiselect")  //if only select we parse the text and val

                    {

                        objMyAddItem[iId] = iVal;
						var option_all = $("#"+iId+" option:selected").map(function () {

							return $(this).text();

						}).get().join(',');
                        objMyAddItem[iId + "_desc"] = option_all;
                    }
                    else if(iType == "checkbox")  //if checkbox
                    {
                        if(input.prop("checked"))
                            objMyAddItem[iId] = 1;
                        else
                            objMyAddItem[iId] = 0;
                    }
                    else
					{
						
						if(iId=='evaluation')
						{
							objMyAddItem[iId]=evaluation.getData();
						}
						else
                        objMyAddItem[iId]=iVal;
					}
                }

            }
            );

			if(flagValidate == true)
			{
				if ( typeof hide_details_form == 'function') { 

					if(strActionMode != 'ADD')
						hide_details_form();

				}
			}

            if(flagValidate) {
                var intIsExist = 0;
                intIsExist = IsProductExist($("#product_id").val());

                if (intIsExist == 0 || 1) {
                    if (bEditDetailRecord) {
                        visibleControl('idCancelEditDetails', false);
                        editDetailArray(objMyAddItem, iEditIndex);
                        $("#AddListTEXT").html("Add List");
                    }
                    else {
                        objMyDetailRecords.push(objMyAddItem);
                        populateDetailRecords(objMyDetailRecords);
                    }
                } else {
                    mySmallAlert('Warning...!', 'This item already exit in the list, please edit and make changes', 2);

                }

                bEditDetailRecord = false;

                $("#AddListTEXT").html("Add to List");
                $("#idCancelEditDetails").html("Cancel Add");
                visibleControl('idCancelEditDetails', false);

                //Clear Form Fields
                $('#frmDetailsForm input, #frmDetailsForm select,#frmDetailsForm textarea').each(function (index) {

                    input = $(this);
                    iType = input.attr("type");
                    iId = input.attr("id");
                    iVal = input.val();
                    if (input.attr("name")) // if input name exists
                    {
                        if (iType == "text" || iType == "number"  || iType == "hidden")  // if textbox
						{
                            $("#" + iId).val("");
							$("#attached_file1").html("");
							$("#attached_file2").html("");
							if(iId=='evaluation')
							evaluation.setData('');
						}

						else if (iType == "file")  // if textbox
                            $("#" + iId).val("");
                        else if (iType == "select") 
						{							// if Select Box
                            $("#" + iId).val("0");
							$("#" + iId).select2('val',"0");
						}

                        else if (iType == "checkbox")   // if Select Box
                            $("#" + iId).prop("checked", false);

                        else if (iType == "multiselect")   // if Select Box with multi select
                            $("#" + iId).select2("val", "0");

                        else if (iType == "textarea")   // if Select Box with multi select
                            $("#" + iId).val("");

                    }

                });
            }
        });

        //when click delete on transaction
        tblDetailsListBody.delegate('a.delete', 'click', function (e) {
            e.preventDefault();
            var iMyDelIndex = $(this).attr('data-row-index');
            if (parseInt(objMyDetailRecords[iMyDelIndex].DETAIL_KEY_ID) == 0) {
                deleteDetailArray(iMyDelIndex);
                document.getElementById("tblDetailsListBody").deleteRow(iMyDelIndex);
                populateDetailRecords(objMyDetailRecords);
            }
            else {
                alert("Delete not allowed");
            }
        });
        //when click edit transaction
        tblDetailsListBody.delegate('a.edit', 'click', function (e) {
            e.preventDefault();
            if (bEditDetailRecord) {
                mySmallAlert('Warning...!', 'Already in Edit Mode!', 2);
                return;
            }
            bEditDetailRecord = true;
            spanAddListTEXT.html("Update List");
            iEditIndex = $(this).attr('data-row-index');
            iEditDetailKeyID = parseInt(objMyDetailRecords[iEditIndex].DETAIL_KEY_ID);

            //Fill Form Values
            var iId;
			
			if ( typeof show_details_form == 'function' ) { 

						show_details_form();

		    }


            $('#frmDetailsForm input, #frmDetailsForm select,#frmDetailsForm textarea').each(function (index) {

                input = $(this);
                iType = input.attr("type");
                iId = input.attr("id");

                //alert(iType);

                if (input.attr("name")) // if input name exists
                {
                    if (iType == "text" || iType == "number" || iType == "hidden")  // if textbox
					{
                        $("#" + iId).val(objMyDetailRecords[iEditIndex][iId]).trigger("change");
						if(iId == 'filehidden1')
						{
							$('#attached_file1').html(objMyDetailRecords[iEditIndex][iId]);
							$('#attached_file1').attr('href','public/uploads/'+objMyDetailRecords[iEditIndex][iId]);
						}
						if(iId == 'filehidden2')
						{
							$('#attached_file2').html(objMyDetailRecords[iEditIndex][iId]); 
							$('#attached_file2').attr('href','public/uploads/'+objMyDetailRecords[iEditIndex][iId]);
						}
						if(iId=='evaluation')
						{
							$("#" + iId).val(objMyDetailRecords[iEditIndex][iId]);
							evaluation.setData();
						}
						
					}
                    else if (iType == "select") // if Select Box
                        $("#" + iId).val(objMyDetailRecords[iEditIndex][iId]).trigger("change");

                    else if(iType == "checkbox")  //if checkbox
                    {
                        if(objMyDetailRecords[iEditIndex][iId]==1)
                            $("#" + iId).prop("checked",true);
                        else
                            $("#" + iId).prop("checked",false);
                    }
                    else if (iType == "multiselect")  // if Select Box with multi select
                        $("#" + iId).select2("val", objMyDetailRecords[iEditIndex][iId]);

                    else if (iType == "textarea") // if Select Box with multi select
					{
						
                        $("#" + iId).val(objMyDetailRecords[iEditIndex][iId]);
					}

                }

            });

            //control cancel
            $("#idCancelEditDetails").html("Cancel Edit");
            visibleControl('idCancelEditDetails', true);


        });

        //when click cancel
        $("#idCancelEditDetails").click(function (e)
        {
            bEditDetailRecord = false;
            visibleControl('idCancelEditDetails', false);
            spanAddListTEXT.html('Add Line');
            $("#idCancelEditDetails").html("Cancel Add");

            //Clear Form Fields
            $('#frmDetailsForm input, #frmDetailsForm select,#frmDetailsForm textarea').each(function (index) {

                input = $(this);
                iType = input.attr("type");
                iId = input.attr("id");
                iVal = input.val();
                if (input.attr("name")) // if input name exists
                {
                    if (iType == "text" || iType == "number"  || iType == "hidden")  // if textbox
					{
                        $("#" + iId).val("");
						$("#attached_file1").html("");
						$("#attached_file2").html("");
						if(iId=='evaluation')
						evaluation.setData('');
					}
					else if (iType == "file")  // if textbox
                            $("#" + iId).val("");
                    else if (iType == "select")   // if Select Box
                        $("#" + iId).select2("val", "0");

                    else if (iType == "checkbox")   // if Select Box
                        $("#" + iId).prop("checked", false);

                    else if (iType == "multiselect")   // if Select Box with multi select
                        $("#" + iId).select2("val", "0");

                    else if (iType == "textarea")   // if Select Box with multi select
                        $("#" + iId).val("");
                }
            });
        });
			
		
  //////////////////////////////// second detail table ////////////////////////////////
  btnAddList2.click(function (e) {

            e.stopPropagation();


            //Read All form elements
            var input="";
            var iType="";
            var iId="";
            var iVal="";
            var objMyAddItem={};
            var flagValidate=true;
			var leave_duplicate=false;


            //Initially set the detail id
            objMyAddItem.DETAIL_KEY_ID=iEditDetailKeyID2;
			
		
			
			if($('#leave_type_id').val() > 0)
			{
				for (var i = 0; i < objMyDetailRecords2.length; i++) {
						
						if(editdetailID2 >= 0)
						{
							
							if( i != parseInt(editdetailID2))
							{
								
								if(objMyDetailRecords2[i].leave_type_id == $('#leave_type_id').val())
									leave_duplicate=true;
							}
						}
						else
						{
							if(objMyDetailRecords2[i].leave_type_id == $('#leave_type_id').val())
									leave_duplicate=true;
						}
				
				}
			
			}


			if(leave_duplicate)
			{
				 mySmallAlert('Error...!','leave type already exist', 0);	
				 flagValidate=false;
                 return false;
			}


            //Fetch Details Form Values
            $('#frmDetailsForm2 input, #frmDetailsForm2 select,#frmDetailsForm2 textarea').each( function(index) {

                input = $(this);
                iType=input.attr("type");
                iId=input.attr("id");
                iVal=input.val();

                //validate the required fields
                if(input.attr("validate"))
                {
                    //validate the select boxes
                    if(iType == "select") {
                        if (iVal == '0')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
                    if(iType == "text") {
                        if (iVal == '')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
					if(iType == "number") {
                        if (iVal == '')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
                }

                if(input.attr("name")) // if input name exists parse values and store to array
                {

                    if(iType == "select")  //if only select we parse the text and val
                    {
                        objMyAddItem[iId] = iVal;
                        objMyAddItem[iId + "_desc"] = $("#"+iId+" option:selected").text();
                    }
					else if(iId == "total_1")  //if checkbox
                    {
                        
                       objMyAddItem[iId] = objMyAddItem['qty'] * objMyAddItem['unit_price'];
                       
                    }
					else if(iId == "total_2")  //if checkbox
                    {
                       var  total2 = objMyAddItem['total_1'] -  objMyAddItem['discount'];
						
						if(objMyAddItem['tax_inclusive'] == 0)
							 total2 =  parseFloat(total2) + parseFloat((total2 * objMyAddItem['tax_id']) / 100);
						
                       	objMyAddItem[iId] = total2;
                       
                    }
                    else if(iType == "checkbox")  //if checkbox
                    {
                        if(input.prop("checked"))
                            objMyAddItem[iId] = 1;
                        else
                            objMyAddItem[iId] = 0;
                    }
                    else
                        objMyAddItem[iId]=iVal;
						
						
					
                }

            }
            );

			

            if(flagValidate) {
                var intIsExist = 0;
                intIsExist = IsProductExist($("#product_id").val());

                if (intIsExist == 0 || 1) {
                    if (bEditDetailRecord2) {
                        visibleControl('idCancelEditDetails', false);
                        editDetailArray2(objMyAddItem, iEditIndex);
                        $("#AddListTEXT2").html("Add List");
                    }
                    else {
						
                        objMyDetailRecords2.push(objMyAddItem);
                        populateDetailRecords2(objMyDetailRecords2);
                    }
                } else {
                    mySmallAlert('Warning...!', 'This item already exit in the list, please edit and make changes', 2);

                }

                bEditDetailRecord2 = false;

                $("#AddListTEXT2").html("Add to List");
                $("#idCancelEditDetails2").html("Cancel Add");
                visibleControl('idCancelEditDetails2', false);

                //Clear Form Fields
                $('#frmDetailsForm2 input, #frmDetailsForm2 select,#frmDetailsForm2 textarea').each(function (index) {

                    input = $(this);
                    iType = input.attr("type");
                    iId = input.attr("id");
                    iVal = input.val();
                    if (input.attr("name")) // if input name exists
                    {
                        if (iType == "text")  // if textbox
                            $("#" + iId).val("");
							
						if (iType == "number")  // if textbox
                            $("#" + iId).val("");
							
						if (iType == "file")  // if textbox
                            $("#" + iId).val("");

                        else if (iType == "select")   // if Select Box
						{
                            $("#" + iId).val("0");
							 $("#" + iId).select2("val", "0");
						}
                        else if (iType == "checkbox")   // if Select Box
                            $("#" + iId).prop("checked", false);

                        else if (iType == "multiselect")   // if Select Box with multi select
                            $("#" + iId).select2("val", "0");

                        else if (iType == "textarea")   // if Select Box with multi select
                            $("#" + iId).val("");

                    }

                });
				editdetailID2 = "";
            }
        });
  tblDetailsListBody2.delegate('a.delete', 'click', function (e) {
			
            e.preventDefault();
            var iMyDelIndex = $(this).attr('data-row-index');
			
            if (parseInt(objMyDetailRecords2[iMyDelIndex].DETAIL_KEY_ID) == 0) {
                deleteDetailArray2(iMyDelIndex);
                document.getElementById("tblDetailsListBody2").deleteRow(iMyDelIndex);
                populateDetailRecords2(objMyDetailRecords2);
            }
            else {
                alert("Delete not allowed");
            }
        });
   tblDetailsListBody2.delegate('a.edit', 'click', function (e) {
            e.preventDefault();
            if (bEditDetailRecord2) {
                mySmallAlert('Warning...!', 'Already in Edit Mode!', 2);
                return;
            }
            bEditDetailRecord2 = true;
            spanAddListTEXT2.html("Update List");
            iEditIndex = $(this).attr('data-row-index');
            iEditDetailKeyID2 = parseInt(objMyDetailRecords2[iEditIndex].DETAIL_KEY_ID);
			editdetailID2 = iEditIndex;

            //Fill Form Values
            var iId;

            $('#frmDetailsForm2 input, #frmDetailsForm2 select,#frmDetailsForm2 textarea').each(function (index) {

                input = $(this);
                iType = input.attr("type");
                iId = input.attr("id");

               
				
                if (input.attr("name")) // if input name exists
                {

                    if (iType == "text")  // if textbox
					{
                        $("#" + iId).val(objMyDetailRecords2[iEditIndex][iId]).trigger("change");
						
					}
					if (iType == "number")  // if textbox
					{
                        $("#" + iId).val(objMyDetailRecords2[iEditIndex][iId]).trigger("change");
						
					}

                    else if (iType == "select") // if Select Box
                        $("#" + iId).val(objMyDetailRecords2[iEditIndex][iId]).trigger("change");

                    else if(iType == "checkbox")  //if checkbox
                    {
                        if(objMyDetailRecords2[iEditIndex][iId]==1)
                            $("#" + iId).prop("checked",true);
                        else
                            $("#" + iId).prop("checked",false);
                    }
                    else if (iType == "multiselect")  // if Select Box with multi select
                        $("#" + iId).select2("val", objMyDetailRecords2[iEditIndex][iId]);

                    else if (iType == "textarea") // if Select Box with multi select
					{
						
                        $("#" + iId).val(objMyDetailRecords2[iEditIndex][iId]);
					}

                }

            });

            //control cancel
            $("#idCancelEditDetails2").html("Cancel Edit");
            visibleControl('idCancelEditDetails2', true);


        });
      $("#idCancelEditDetails2").click(function (e)
        {
            bEditDetailRecord2 = false;
            visibleControl('idCancelEditDetails2', false);
            spanAddListTEXT2.html('Add Line');
            $("#idCancelEditDetails2").html("Cancel Add");

            //Clear Form Fields
            $('#frmDetailsForm2 input, #frmDetailsForm2 select,#frmDetailsForm2 textarea').each(function (index) {

                input = $(this);
                iType = input.attr("type");
                iId = input.attr("id");
                iVal = input.val();
                if (input.attr("name")) // if input name exists
                {
                    if (iType == "text")  // if textbox
                        $("#" + iId).val("");
					else if (iType == "file")  // if textbox
                            $("#" + iId).val("");
                    else if (iType == "select")   // if Select Box
                        $("#" + iId).select2("val", "0");

                    else if (iType == "checkbox")   // if Select Box
                        $("#" + iId).prop("checked", false);

                    else if (iType == "multiselect")   // if Select Box with multi select
                        $("#" + iId).select2("val", "0");

                    else if (iType == "textarea")   // if Select Box with multi select
                        $("#" + iId).val("");
                }
            });
        });
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	   //////////////////////////////// Third detail table ////////////////////////////////
  btnAddList3.click(function (e) {

            e.stopPropagation();


            //Read All form elements
            var input="";
            var iType="";
            var iId="";
            var iVal="";
            var objMyAddItem={};
            var flagValidate=true;
			var leave_duplicate=false;
			var tax_duplicate=false;


            //Initially set the detail id
            objMyAddItem.DETAIL_KEY_ID=iEditDetailKeyID3;
			
		
			
			if($('#tax_id').val() > 0)
			{
				for (var i = 0; i < objMyDetailRecords3.length; i++) {
						
						if(editdetailID3 >= 0)
						{
							
							if( i != parseInt(editdetailID3))
							{
								
								if(objMyDetailRecords3[i].tax_id == $('#tax_id').val())
									tax_duplicate=true;
							}
						}
						else
						{
							if(objMyDetailRecords3[i].tax_id == $('#tax_id').val())
									tax_duplicate=true;
						}
				
				}
			
			}


			if(tax_duplicate)
			{
				 mySmallAlert('Error...!','Tax type already exist', 0);	
				 flagValidate=false;
                 return false;
			}


            //Fetch Details Form Values
            $('#frmDetailsForm3 input, #frmDetailsForm3 select,#frmDetailsForm3 textarea').each( function(index) {

                input = $(this);
                iType=input.attr("type");
                iId=input.attr("id");
                iVal=input.val();

                //validate the required fields
                if(input.attr("validate"))
                {
                    //validate the select boxes
                    if(iType == "select") {
                        if (iVal == '0')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
                    if(iType == "text") {
                        if (iVal == '')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
					if(iType == "number") {
                        if (iVal == '')
                        {
                            mySmallAlert('Error...!', input.attr("validate-msg"), 0);
                            flagValidate=false;
                            return false;
                        }
                    }
                }

                if(input.attr("name")) // if input name exists parse values and store to array
                {

                    if(iType == "select")  //if only select we parse the text and val
                    {
                        objMyAddItem[iId] = iVal;
                        objMyAddItem[iId + "_desc"] = $("#"+iId+" option:selected").text();
                    }
					else if(iId == "total_1")  //if checkbox
                    {
                        
                       objMyAddItem[iId] = objMyAddItem['qty'] * objMyAddItem['unit_price'];
                       
                    }
					else if(iId == "total_3")  //if checkbox
                    {
                       var  total3 = objMyAddItem['total_1'] -  objMyAddItem['discount'];
						
						if(objMyAddItem['tax_inclusive'] == 0)
							 total3 =  parseFloat(total3) + parseFloat((total3 * objMyAddItem['tax_id']) / 100);
						
                       	objMyAddItem[iId] = total3;
                       
                    }
                    else if(iType == "checkbox")  //if checkbox
                    {
                        if(input.prop("checked"))
                            objMyAddItem[iId] = 1;
                        else
                            objMyAddItem[iId] = 0;
                    }
                    else
                        objMyAddItem[iId]=iVal;
						
						
					
                }

            }
            );

			

            if(flagValidate) {
                var intIsExist = 0;
                intIsExist = IsProductExist($("#product_id").val());

                if (intIsExist == 0 || 1) {
                    if (bEditDetailRecord3) {
                        visibleControl('idCancelEditDetails', false);
                        editDetailArray3(objMyAddItem, iEditIndex);
                        $("#AddListTEXT3").html("Add List");
                    }
                    else {
						
                        objMyDetailRecords3.push(objMyAddItem);
                        populateDetailRecords3(objMyDetailRecords3);
                    }
                } else {
                    mySmallAlert('Warning...!', 'This item already exit in the list, please edit and make changes', 2);

                }

                bEditDetailRecord3 = false;

                $("#AddListTEXT3").html("Add to List");
                $("#idCancelEditDetails3").html("Cancel Add");
                visibleControl('idCancelEditDetails3', false);

                //Clear Form Fields
                $('#frmDetailsForm3 input, #frmDetailsForm3 select,#frmDetailsForm3 textarea').each(function (index) {

                    input = $(this);
                    iType = input.attr("type");
                    iId = input.attr("id");
                    iVal = input.val();
                    if (input.attr("name")) // if input name exists
                    {
                        if (iType == "text")  // if textbox
                            $("#" + iId).val("");
						else if (iType == "file")  // if textbox
                            $("#" + iId).val("");
						if (iType == "number")  // if textbox
                            $("#" + iId).val("");
							
						if (iType == "file")  // if textbox
                            $("#" + iId).val("");

                        else if (iType == "select")   // if Select Box
						{
                            $("#" + iId).val("0");
							 $("#" + iId).select2("val", "0");
						}
                        else if (iType == "checkbox")   // if Select Box
                            $("#" + iId).prop("checked", false);

                        else if (iType == "multiselect")   // if Select Box with multi select
                            $("#" + iId).select2("val", "0");

                        else if (iType == "textarea")   // if Select Box with multi select
                            $("#" + iId).val("");

                    }

                });
				editdetailID3 = "";
            }
        });
  tblDetailsListBody3.delegate('a.delete', 'click', function (e) {
			
            e.preventDefault();
            var iMyDelIndex = $(this).attr('data-row-index');
			
            if (parseInt(objMyDetailRecords3[iMyDelIndex].DETAIL_KEY_ID) == 0) {
                deleteDetailArray3(iMyDelIndex);
                document.getElementById("tblDetailsListBody3").deleteRow(iMyDelIndex);
                populateDetailRecords3(objMyDetailRecords3);
            }
            else {
                alert("Delete not allowed");
            }
        });
   tblDetailsListBody3.delegate('a.edit', 'click', function (e) {
     
            e.preventDefault();
            if (bEditDetailRecord3) {
                mySmallAlert('Warning...!', 'Already in Edit Mode!', 2);
                return;
            }
            bEditDetailRecord3 = true;
            spanAddListTEXT3.html("Update List");
            iEditIndex = $(this).attr('data-row-index');
            iEditDetailKeyID3 = parseInt(objMyDetailRecords3[iEditIndex].DETAIL_KEY_ID);
			editdetailID3 = iEditIndex;

            //Fill Form Values
            var iId;

            $('#frmDetailsForm3 input, #frmDetailsForm3 select,#frmDetailsForm3 textarea').each(function (index) {

                input = $(this);
                iType = input.attr("type");
                iId = input.attr("id");

               
				
                if (input.attr("name")) // if input name exists
                {

                    if (iType == "text")  // if textbox
					{
                        $("#" + iId).val(objMyDetailRecords3[iEditIndex][iId]).trigger("change");
						
					}
					if (iType == "number")  // if textbox
					{
                        $("#" + iId).val(objMyDetailRecords3[iEditIndex][iId]).trigger("change");
						
					}

                    else if (iType == "select") // if Select Box
                        $("#" + iId).val(objMyDetailRecords3[iEditIndex][iId]).trigger("change");

                    else if(iType == "checkbox")  //if checkbox
                    {
                        if(objMyDetailRecords3[iEditIndex][iId]==1)
                            $("#" + iId).prop("checked",true);
                        else
                            $("#" + iId).prop("checked",false);
                    }
                    else if (iType == "multiselect")  // if Select Box with multi select
                        $("#" + iId).select3("val", objMyDetailRecords3[iEditIndex][iId]);

                    else if (iType == "textarea") // if Select Box with multi select
                        $("#" + iId).val(objMyDetailRecords3[iEditIndex][iId]);

                }

            });

            //control cancel
            $("#idCancelEditDetails3").html("Cancel Edit");
            visibleControl('idCancelEditDetails3', true);

	  });
       $("#idCancelEditDetails3").click(function (e) 
        {
            bEditDetailRecord3 = false;
            visibleControl('idCancelEditDetails3', false);
            spanAddListTEXT3.html('Add Line');
            $("#idCancelEditDetails3").html("Cancel Add");

            //Clear Form Fields
            $('#frmDetailsForm3 input, #frmDetailsForm3 select,#frmDetailsForm3 textarea').each(function (index) {

                input = $(this);
                iType = input.attr("type");
                iId = input.attr("id");
                iVal = input.val();
                if (input.attr("name")) // if input name exists
                {
                    if (iType == "text")  // if textbox
                        $("#" + iId).val("");
					else if (iType == "file")  // if textbox
                            $("#" + iId).val("");
                    else if (iType == "select")   // if Select Box
                        $("#" + iId).select2("val", "0");

                    else if (iType == "checkbox")   // if Select Box
                        $("#" + iId).prop("checked", false);

                    else if (iType == "multiselect")   // if Select Box with multi select
                        $("#" + iId).select2("val", "0");

                    else if (iType == "textarea")   // if Select Box with multi select
                        $("#" + iId).val("");
                }
            });
        });


});//end doc ready

