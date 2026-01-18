<div class="jarviswidget  panel panel-default panel-hovered panel-stacked mb30" id="widForm"
     data-widget-colorbutton="false"
     data-widget-editbutton="false"
     data-widget-togglebutton="false"
     data-widget-deletebutton="false"
     data-widget-fullscreenbutton="false"
     data-widget-custombutton="false"
     role="widget" style=""
     xmlns="http://www.w3.org/1999/html">
     
     
    

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
                        
									<div class="row">
										<div class="col-md-5">
											<fieldset style="padding-top: 5px">
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Label'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="name" name="name" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
											</fieldset>
										</div>
										<?php
											if($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1')
											{
										?>
										<div class="col-md-4">
											<fieldset style="padding-top: 5px">
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Assign User'); ?></strong></label>
															<label class="col-md-12"> 
																<select type="select" id="user_id" name="user_id" class="select2" ></select>
																<small></small>
															</label>
														</section>
												</div>
											</fieldset>
										</div>
										<?php } ?>
										<div class="col-md-3">
										 <div class="buttons flex mt-8 ">
												<button id="btnSave" class="btn-primary mr-2" form="attributeVerification" aria-label="Continue" style="">Add</button><a id="btnBack" href="" class=" bg-white border uppercase text-sm border-blue-500 text-blue-500 py-2 ml-2 px-10 rounded-full" style="">Cancel</a>
												
										</div>
										</div>
									</div>	
									
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
