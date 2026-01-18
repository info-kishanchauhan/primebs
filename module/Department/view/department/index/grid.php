<!-- Widget ID (each widget will need unique ID)-->

<div class="jarviswidget jarviswidget-color-blueDark panel panel-lined table-responsive panel-hovered mb20 data-table " id="widGrid" data-widget-editbutton="false"

     data-widget-colorbutton="false"

     data-widget-editbutton="false"

     data-widget-togglebutton="false"

     data-widget-deletebutton="false"

     data-widget-fullscreenbutton="false"

     data-widget-custombutton="false"

     role="widget" style=""



    >

    <!-- widget options:

                    usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">



                    data-widget-colorbutton="false"

                    data-widget-editbutton="false"

                    data-widget-togglebutton="false"

                    data-widget-deletebutton="false"

                    data-widget-fullscreenbutton="false"

                    data-widget-custombutton="false"

                    data-widget-collapsed="true"

                    data-widget-sortable="false"



                    -->

                    

                    

                     

                     <div class="panel-heading clearfix">
						  <div class="row">
							 <div class="col-md-9"><a href="<?php echo $this->url('administration', array('action' => 'index')); ?>"><?php echo $this->translate('Settings') ?></a> <?php echo $this->translate(' | Department List') ?><i class="ion ion-help-circled help" title="help"></i></div>
							 <div class="col-md-3"><button id="btnNew" type="button" class="btn btn-primary pull-right addEventBtn waves-effect" data-toggle="dropdown" style="display: block;"><i class="ion ion-plus"></i>New</button></div>
						  </div>
					   </div>
  



    <!-- widget div-->



        <!-- widget edit box -->

        <div class="jarviswidget-editbox">

            <!-- This area used as dropdown edit box -->



        </div>

        <!-- end widget edit box -->


        <!-- widget content -->

        <div class="dataTables_wrapper no-footer">



            <table id="tblMasterList" class="table table-striped table-bordered table-hover responsive-table" width="100%">



<col width="45%">

<col width="45%">

<col width="10%">

                <thead>

                <tr>

                    <th>ID</th>

                    
					<th><i class="fa fa-fw text-muted hidden-md hidden-sm hidden-xs"></i>  <?php echo $this->translate('Name'); ?></th>

					<th><i class="fa fa-fw  txt-color-blue hidden-md hidden-sm hidden-xs">

                    </i>  <?php echo $this->translate('Description'); ?></th>

					 <th><i class=" txt-color-blue hidden-md hidden-sm hidden-xs"></i> <?php echo $this->translate('Action'); ?> </th>
                   



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

