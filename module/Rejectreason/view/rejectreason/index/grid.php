<!-- Widget ID (each widget will need unique ID)-->
<style>
.table>tbody>tr>td
{
	padding-left:25px;
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


                    <?php /* <div class="panel-heading clearfix">
						  <div class="row">
							 <div class="col-md-9"><?php echo $this->translate('') ?></div>
							 <div class="col-md-3"><button id="btnNew" type="button" class="btn btn-primary addEventBtn waves-effect right" style="display: block;">Add New Label</button></div>
						  </div>
					   </div> */ ?>
  


        <div class="jarviswidget-editbox">

            <!-- This area used as dropdown edit box -->



        </div>

        <!-- end widget edit box -->


        <!-- widget content -->

        <div class="dataTables_wrapper no-footer">



            <table id="tblMasterList" class="table table-striped table-bordered table-hover responsive-table" width="100%">


<col width="20%">
<col width="60%">
<col width="20%">

                <thead>

                <tr>

                    <th>ID</th>

                    
					<th><i class="fa fa-fw text-muted hidden-md hidden-sm hidden-xs"></i>  <?php echo $this->translate('Tag'); ?></th>

					<th><i class="fa fa-fw text-muted hidden-md hidden-sm hidden-xs"></i>  <?php echo $this->translate('Description'); ?></th>

					 <th><i class="fa fa-fw text-muted hidden-md hidden-sm hidden-xs"></i> <?php echo $this->translate('Action'); ?> </th>
                   



                </tr>

                </thead>

                <tbody>

					

                </tbody>

            </table>



        </div>

        <!-- end widget content -->





   

    <!-- end widget div -->



</div>

<!-- end widget -->

