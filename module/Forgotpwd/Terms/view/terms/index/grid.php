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
    <header>
        <span class="widget-icon"> <i class="fa fa-table"></i> </span>

        <h2><?php echo $this->translate('Terms List') ?></h2>


        <div class="widget-toolbar">

            <div class="btn-group">
                <button id="btnNew" class="btn dropdown-toggle btn-xs btn-primary" data-toggle="dropdown"><i
                        class="fa fa-plus"></i> <?php echo $this->translate('New') ?>
                </button>
            </div>
        </div>

    </header>

    <!-- widget div-->
    <div>

        <!-- widget edit box -->
        <div class="jarviswidget-editbox">
            <!-- This area used as dropdown edit box -->

        </div>
        <!-- end widget edit box -->

        <!-- widget content -->
        <div class="dataTables_wrapper no-footer">

            <table id="tblMasterList" class="table table-striped table-bordered table-hover responsive-table" width="100%">


                <col width = "30%">
                <col width = "25%">
                <col width = "25%">

                <thead>
                <tr>
                    <th>ID</th>
                    <th><i class="fa fa-fw text-muted hidden-md hidden-sm hidden-xs"></i>  <?php echo $this->translate('Ref No.'); ?></th>
					<th><i class="fa fa-fw  txt-color-blue hidden-md hidden-sm hidden-xs">
                    </i>  <?php echo $this->translate('Name'); ?></th>
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


    </div>
    <!-- end widget div -->

</div>
<!-- end widget -->
