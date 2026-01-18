<!-- Widget ID (each widget will need unique ID)-->
 <link rel="stylesheet" href="<?php echo $this->basePath(); ?>/public/css/bootstrap.datatable.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
#bs-main-content-container {
    padding-top: 10px;
}
  
    /* Table ke header aur rows ke liye font control */
  .table.dataTable th {
    font-size: 13px;
    color: #4d4d4d;
}
 .table td {
    font-size: 13px;   /* chhota size set karen (default 14-16 hota hai) */
    line-height: 1.4;  /* spacing control */
    font-family: 'Roboto', Arial, sans-serif; /* clean font */
    color: #333;       /* text ka color */
    vertical-align: middle; /* text alignment improve */
    padding: 6px 10px; /* thoda compact banane ke liye */
}
</style>
<div class="jarviswidget jarviswidget-color-blueDark panel panel-lined table-responsive panel-hovered mb20 data-table " id="widGrid" data-widget-editbutton="false"

     data-widget-colorbutton="false"

     data-widget-editbutton="false"

     data-widget-togglebutton="false"

     data-widget-deletebutton="false"

     data-widget-fullscreenbutton="false"

     data-widget-custombutton="false"

     role="widget" style=""



    >

	
       <div class="panel-heading clearfix" style="background-image: url('<?php echo $this->basePath(); ?>/public/img/breadcrumb_user.jpg'); background-size: cover; background-position: top; margin:10px 20px 40px 20px;padding:100px 30px;border-radius:10px;">
						 
							
			 <div class="col-md-4">
			 <h4 style="font-weight:600;padding:20px 0px;">Get things done faster as team.You can create users for Labels & Artists</h4>
			 <button id="btnNew" type="button" class="btn btn-primary addEventBtn waves-effect" style="display: block;"> Invite Staff</button>
			 </div>
						 
	    </div>
  


       <div class="pull-left">
      <form method="get" action="accountsettings/useraccess" id="searchForm" class="form-horizontal"><input type="hidden" name="~formSubmitted" value="1">
          <div class="col-lg-12">
              <div class="col-lg-6">
                  <div class="form-group">
                      <label for="labelSelector" class="col-lg-3 control-label">
                          <label class=" behavior-2" id="label-searchForm-mysearch-0" for="mysearch">Search by login or email: </label>
                      </label>
                      <div class="col-lg-9 select2-container select2-container-multi ">
                          <input placeholder="Type here to search..." class=" inputString inputBehavior-2 form-control" name="mysearch" id="mysearch" type="text" value="">
                      </div>
                  </div>
              </div>

              <div class="col-lg-6">
                  <div class="form-group">
                      <label for="labelSelector" class="col-lg-3 control-label">
                          <label class="" id="">Account Status: </label>
                      </label>
                      <div class="col-lg-9 select2-container select2-container-multi ">
                          <select class=" inputBehavior- form-control" name="loginStatus" id="loginStatus">
						  <option value="0">All</option>
						  <option value="Pending Connection">Pending Confirmation</option>
						  <option value="Confirmed">Confirmed</option>
						  </select>
                      </div>
                  </div>
              </div>
          </div>

                <div class="col-lg-12">
              <div class="col-lg-6">
                  <div class="form-group">
                      <label for="labelSelector" class="col-lg-3 control-label">
                          <label class="" id="">This staff has access to:: </label>
                      </label>
                      <div class="col-lg-9">
                          <select multiple="multiple" type="multiselect" class="inputBehavior-  select2" name="accessLevel" id="accessLevel" tabindex="-1">
						  <option value="New Release">New Release</option>
						  <option value="All Releases">All Releases</option>
						  <option value="Drafts">Drafts</option>
						  <option value="Finanicial">Financial reports</option></select>
                      </div>
                  </div>
              </div>
             
          </div>

      
            </form>



      </div>

        <!-- end widget edit box -->


        <!-- widget content -->
		
		<div class="col-md-12"><div id="catalogContent">
        <div class="dataTables_wrapper no-footer" >



            <table id="tblMasterList" class="table  table-bordered dataTable table-hover responsive-table" width="100%">



<col width="20%">

<col width="20%">

<col width="10%">

<col width="30%">

<col width="20%">

                <thead>

                <tr class="active">

                    <th>ID</th>

                    
					<th> <?php echo $this->translate('Login'); ?></th>

					<th>  <?php echo $this->translate('Email'); ?></th>
					
					<th><?php echo $this->translate('Account Status'); ?></th>
					
					<th><?php echo $this->translate('This staff has access to'); ?></th>

					<th><?php echo $this->translate('Action'); ?> </th>
                   



                </tr>

                </thead>

                <tbody>

					

                </tbody>

            </table>

<!-- No Staff Message -->
<div id="noUsersMessage" style="display:none; text-align:center; margin: 40px 0; font-size:16px; font-weight:500; color:#999;">
    <i class="fa fa-users" style="font-size:24px; color:#999;"></i><br>
    No staff added yet
</div>

        </div>
		</div>
		
        <!-- end widget content -->





   
</div>

    <!-- end widget div -->



</div>

<!-- end widget -->

