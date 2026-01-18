<div class="jarviswidget hide panel panel-default panel-hovered panel-stacked mb30" id="widForm"
     data-widget-colorbutton="false"
     data-widget-editbutton="false"
     data-widget-togglebutton="false"
     data-widget-deletebutton="false"
     data-widget-fullscreenbutton="false"
     data-widget-custombutton="false"
     role="widget" style=""
     xmlns="http://www.w3.org/1999/html">

    <header>

        <span class="widget-icon"> <i class="glyphicon glyphicon-stats txt-color-darken"></i> </span>

        <h2>Terms Info :</h2>

        <div class="widget-toolbar" style="">

        </div>

    </header>

    <!-- widget div-->
    <div class="no-padding">
        <!-- widget edit box -->
        <div class="jarviswidget-editbox">
            Username
        </div>
        <!-- end widget edit box -->

        <div class="widget-body">

            <!-- content -->

            <div class="widget-toolbar" style="padding-right: 10px;padding-left: 10px">
                <a id="btnBack" href="javascript:void(0);" class="btn btn-xs btn-default"><i
                        class="fa fa-arrow-left"></i> <span
                        class="hidden-mobile hidden-tablet">Back</span> </a>
                <button id="btnSave" type="submit" class="btn btn-xs btn-success"><i class="fa fa-save"></i>
                    <strong><span
                            class="hidden-mobile hidden-tablet">Save</span> </strong></button>

            </div>
            <ul class="nav nav-tabs  in" id="myTabMaster">
                <li class="active">
                    <a data-toggle="tab" href="#general"><i class="fa fa-lg fa-info-circle"></i> <span
                            class="hidden-mobile hidden-tablet">General</span></a>
                </li>
            </ul>

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="general">
                    <form id="frmForm" class="smart-form" novalidate="novalidate" enctype="multipart/form-data">
                        <fieldset style="padding-top: 5px">
                            <div class="row">
							
								<section class="col col-4">

                                    <label class="label"><strong

                                            class="txt-color-blue"><?php echo $this->translate('Ref No'); ?></strong><span class="txt-color-red"> *</span></label>

                                    <label class="input"> <i class="  "></i>

                                        <input type="text" id="refno" name="refno" class=" ">

                                    </label>

                                </section>

                                <section class="col col-4">
                                    <label class="label"><strong class="txt-color-blue"><?php echo $this->translate('Name'); ?></strong><span class="txt-color-red"> *</span></label>
                                    <label class="input"> <i class="  "></i>
                                        <input type="text" id="name" name="name" class=" ">
                                    </label>
                                </section>
								
								<section class="col col-4">

                                    <label class="label"><strong class="txt-color-blue"><?php echo $this->translate('Description'); ?></strong></label>

                                    <label class="textarea"> <i class=""></i>

                                        <textarea type="textarea" rows="3" class=" " placeholder="Description" name="description" id="description"></textarea>



                                    </label>

                                </section>



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
