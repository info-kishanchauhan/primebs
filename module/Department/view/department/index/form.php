<div class="jarviswidget hide panel panel-default panel-hovered panel-stacked mb30" id="widForm"
     data-widget-colorbutton="false"
     data-widget-editbutton="false"
     data-widget-togglebutton="false"
     data-widget-deletebutton="false"
     data-widget-fullscreenbutton="false"
     data-widget-custombutton="false"
     role="widget" style=""
     xmlns="http://www.w3.org/1999/html">
     
     
     <div class="panel-heading clearfix">
   Department Info
    
    
   <div class="right col-lg-3 col-xs-12 mp0 frm">
   <button id="btnSave" type="submit" class="right btn btn-success btn-icon-inline btn-sm mb15 addEventBtn">
   <i class="fa fa-floppy-o"></i>Save
   </button>
   <button id="btnBack" type="button" class="right btn btn-default btn-icon-inline btn-sm mb15 addEventBtn">
   <i class="ion ion-arrow-left-c"></i>Back
   </button>
   
   
   </div>
    
    
    </div>

    <!-- widget div-->
    <div class="panel-body">
        <!-- widget edit box -->
      <!--  <div class="jarviswidget-editbox">
            Username
        </div>-->
        <!-- end widget edit box -->

        <div class="widget-body">

            <!-- content -->
<!--

            <ul class="nav nav-tabs  in" id="myTabMaster">
                <li class="active">
                    <a data-toggle="tab" href="#general"><i class="fa fa-lg fa-info-circle"></i> <span
                            class="hidden-mobile hidden-tablet">General</span></a>
                </li>
            </ul>
-->

            <div id="myTabContent">
                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="general">
                    <form id="frmForm" class="smart-form" novalidate="novalidate" enctype="multipart/form-data">
                        <fieldset style="padding-top: 5px">
                           <div class="panel panel-default panel-hovered panel-stacked mb30">
        <div class="panel-body">
                            <div class="row">
<div class="col-md-6">
                                <section class="form-group form-group-sm clearfix">
                                    <label class="col-md-3 col-sm-3 col-xs-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Name*'); ?></strong></label>
                                    <label class="col-md-9 col-sm-9 col-xs-12"> 
                                        <input type="text" id="name" name="name" class="form-control " placeholder="<?php echo $this->translate('Name'); ?>">
                                    </label>
                                </section>
                             </div>
                             <div class="col-md-6">


                                <section class="form-group form-group-sm clearfix">
                                    <label class="col-md-3 col-sm-3 col-xs-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Description'); ?></strong></label>
                                    <label class="col-md-9 col-sm-9 col-xs-12"> 
									<textarea type="textarea" name="descriptions" id="descriptions" class="form-control" rows="4" placeholder="<?php echo $this->translate('Description'); ?>"></textarea>
                                        
                                    </label>
                                </section>

                             </div>



                            </div>					
                            </div>
                         </div>
               			</fieldset>
                </form>

                </div>
            </div>
            <!-- end general tab pane -->

        </div>

        <!-- Start All tab pane -->

        <!-- End All tab pane -->

        <!-- end content -->
    </div>

</div>
<!-- end widget div -->
