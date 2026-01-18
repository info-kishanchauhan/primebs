<!-- Widget ID (each widget will need unique ID)-->
<style>
	tr td:nth-child(5),tr td:nth-child(7)
	{
		text-align:center;
	}
	tr{
		height:47px;
	}
	#availableReportTitle,#whichKindReportTitle
	{
		margin-left: -15px !important;
	}
	#btnNewReport
	{
		margin-right: -15px !important;
	}
	.design-process-section .text-align-center {
    line-height: 25px;
    margin-bottom: 12px;
}
#wizardStep
{
	margin-bottom:50px;
}
.design-process-content {
    border: 1px solid #e9e9e9;
    position: relative;
    padding: 30px;
}
.design-process-content img {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 0;
    max-height: 100%;
}
.design-process-content h3 {
    margin-bottom: 16px;
}
.design-process-content p {
    line-height: 26px;
    margin-bottom: 12px;
}
.process-model {
    list-style: none;
    padding: 0;
    position: relative;
    margin: 20px auto 26px;
    border: none;
    z-index: 0;
}
.process-model li::after {
    background: #e5e5e5 none repeat scroll 0 0;
    bottom: 0;
    content: "";
    display: block;
    height: 4px;
    margin: 0 auto;
    position: absolute;
	right: calc(-50%);
    top: 33px;
    width: 100%;
    z-index: -1;
}
.process-model li.visited::after {
    background: #57b87b;
}
.process-model li:last-child::after {
    width: 0;
}
.process-model li {
    display: inline-block;
    width: 33%;
    text-align: center;
    float: none;
}
.nav-tabs.process-model > li.active > a, .nav-tabs.process-model > li.active > a:hover, .nav-tabs.process-model > li.active > a:focus, .process-model li a:hover, .process-model li a:focus {
    border: none;
    background: transparent;

}
.process-model li a {
    padding: 0;
    border: none;
    color: #606060;
}
.process-model li.active,
.process-model li.visited {
    color: #57b87b;
}
.process-model li.active a,
.process-model li.active a:hover,
.process-model li.active a:focus,
.process-model li.visited a,
.process-model li.visited a:hover,
.process-model li.visited a:focus {
    color: #57b87b;
}
.process-model li.active p,
.process-model li.visited p {
    font-weight: 600;
}
.process-model li i {
    display: block;
    height: 68px;
    width: 68px;
    text-align: center;
    margin: 0 auto;
    background: #f5f6f7;
    border: 2px solid #e5e5e5;
    line-height: 65px;
    font-size: 30px;
    border-radius: 50%;
}
.process-model li.active i, .process-model li.visited i  {
    background: #fff;
    border-color: #57b87b;
}
.process-model li p {
    font-size: 14px;
    margin-top: 11px;
}
.process-model.contact-us-tab li.visited a, .process-model.contact-us-tab li.visited p {
    color: #606060!important;
    font-weight: normal
}
.process-model.contact-us-tab li::after  {
    display: none; 
}
.process-model.contact-us-tab li.visited i {
    border-color: #e5e5e5; 
}



@media screen and (max-width: 560px) {
  .more-icon-preocess.process-model li span {
        font-size: 23px;
        height: 50px;
        line-height: 46px;
        width: 50px;
    }
    .more-icon-preocess.process-model li::after {
        top: 24px;
    }
}
@media screen and (max-width: 380px) { 
    .process-model.more-icon-preocess li {
        width: 16%;
    }
    .more-icon-preocess.process-model li span {
        font-size: 16px;
        height: 35px;
        line-height: 32px;
        width: 35px;
    }
    .more-icon-preocess.process-model li p {
        font-size: 8px;
    }
    .more-icon-preocess.process-model li::after {
        top: 18px;
    }
    .process-model.more-icon-preocess {
        text-align: center;
    }
}
.select2-container-multi .select2-choices
{
	border-radius:4px;
}
</style>
<div class="data-table " id="widGrid" data-widget-editbutton="false"

     data-widget-colorbutton="false"

     data-widget-editbutton="false"

     data-widget-togglebutton="false"

     data-widget-deletebutton="false"

     data-widget-fullscreenbutton="false"

     data-widget-custombutton="false"

     role="widget" style=""
    >

				<?php
					if($_SESSION['user_id'] == '0')
					{
				?>
                     <div class="panel-heading clearfix">
						  <div class="row">
							 <div class="col-md-9"><h5 class="text-uppercase" id="availableReportTitle" style="">AVAILABLE REPORTS</h5></div>
							 <div class="col-md-3 mt-10"><button id="btnNewReport" type="button" class="btn btn-primary addEventBtn waves-effect right " style="display: block;">Upload Report</button></div>
						  </div>
					   </div>
  
				<?php }?>

				<div class="jarviswidget-editbox">
				</div>

				<div class="tab-content" id="availableReport">
                    <div role="tabpanel" class="tab-pane active" id="requestedReports">
						<table id="tblMasterList" class="table dataTable no-footer" width="100%">

							<col width="15%">
							<col width="16%">
							<col width="15%">
							<col width="15%">
							<col width="15%">
							<col width="15%">
							<col width="15%">

							<thead>
								<tr>
									<th>ID</th>
									<th><?php echo $this->translate('Period'); ?></th>
									<th><?php echo $this->translate('Report Type'); ?></th>
									<th><?php echo $this->translate('Label'); ?></th>
									<th style="text-align:center"><?php echo $this->translate('Royalty Amount'); ?></th>
									<th><?php echo $this->translate('Generation date'); ?></th>
									<th style="text-align:center"><?php echo $this->translate('Status'); ?></th>
									<th> <?php echo $this->translate('Action'); ?> </th>
								</tr>
							</thead>

							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				<?php
					if($_SESSION['user_id'] == '0')
					{
				?>
						<div class="panel-heading clearfix">
						  <div class="row">
							 <div class="col-md-12"><h5 class="text-uppercase" id="whichKindReportTitle" style="">GENERATE YOUR REPORT</h5></div>
						  </div>
					   </div>
					   <div class="row" style="position:relative" id="generateSection">
					   <div id="waitingReportBox"></div>
					   <div id="wizardStep">
					   <section class="design-process-section" id="process-tab">
							  <div class="">
								<div class="row">
								  <div class="col-xs-12"> 
									<!-- design process steps--> 
									<!-- Nav tabs -->
									<ul class="nav nav-tabs process-model more-icon-preocess" role="tablist">
									  <li role="presentation" class="active"><a href="#period" aria-controls="period" role="tab" data-toggle="tab"><i class="fa fa-calendar" aria-hidden="true"></i>
										<p>Period</p>
										</a></li>
									 
									  <li role="presentation"><a href="#report_type" aria-controls="report_type" role="tab" data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i>
										<p>Report Type</p>
										</a></li>
									  <li role="presentation"><a href="#generate" aria-controls="generate" role="tab" data-toggle="tab" class="generate_tab"><i class="fa fa-clipboard" aria-hidden="true"></i>
										<p>Generate</p>
										</a></li>
									</ul>
									<!-- end design process steps--> 
									<!-- Tab panes -->
									<form method="post" action="#" id="frmForm2" class="" role="form">
									<div class="tab-content">
										
										  <div role="tabpanel" class="tab-pane active" id="period">
											<div class="design-process-content">
												<div class="row">
													<div class="col-sm-3"></div>
													<div class="col-sm-3">
														 <div class="form-group form-group-sm">
															<div class="col-sm-12 control-label">
															   <label class=""><strong class="txt-color-blue"><?php echo $this->translate('From'); ?></strong></label><span style="position: absolute; white-space: nowrap; z-index: 100;"><span style="color: red;"></span>
															</div>
															<div class="col-sm-12">
																<input type="text" id="fromDate" name="fromDate" class="form-control " >
															</div>
														 </div>
													 </div>
													 <div class="col-sm-3">
														 <div class="form-group form-group-sm">
															<div class="col-sm-12 control-label">
															   <label class=""><strong class="txt-color-blue"><?php echo $this->translate('To'); ?></strong></label><span style="position: absolute; white-space: nowrap; z-index: 100;"><span style="color: red;"></span>
															</div>
															<div class="col-sm-12">
																<input type="text" id="toDate" name="toDate" class="form-control " >
															</div>
														 </div>
													 </div>
													 <div class="col-sm-3"></div>
												</div>
											 </div>
										  </div>
										  <div role="tabpanel" class="tab-pane" id="report_type">
											<div class="design-process-content">
												<div class="row">
											<div class="col-md-8 col-md-offset-2">
												<div class="col-md-2"></div>
												<div class="col-md-4 text-center gaEvent" data-ga-event-type="type_single_report">
													<div data-toggle="tooltip" data-placement="left"  class="like-btn square150 reportTypeBtn like-btn-primary" id="useDedicatedReport" title="Generate a single report including your full catalog or filtered on your labels, artists, releases, tracks, platforms or countries.">
														<p class="littleTitleLikeButton">Single Report</p>
													</div>
												</div>

												<div class="col-md-4 text-center gaEvent" data-ga-event-type="type_multiple_report">
													<div data-toggle="tooltip" data-placement="left" class="multipleSquare like-btn" id="useSplitReport" title="Generate with two clicks multiple single reports split by labels, artists, releases, platforms or countries.">
														<div class="like-btn like-btn-primary-outline square150 subSquare_1 keepBtnRaw">
															<div class="like-btn like-btn-primary-outline square150 subSquare_2 keepBtnRaw">
																<div class="like-btn square150 subSquare_3 like-btn-primary-outline">
																	<p class="littleTitleLikeButton">Multiple Reports</p>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-2"></div>
												<input type="hidden" id="reportType" name="reportType" value="single">

												<!--DEDICATED REPORT-->
												<div class="row">
													<div class="col-md-12">
														<div id="dedicatedMyReport" style="display: block;">
															<div class="form-group">
																<div class="text-center single-report">
																	<label class="radio-inline dedicatedLabelForAllCatalog">
																		<input class="gaEvent" data-ga-event-type="type_full_catalog" data-report-option="single full" type="radio" name="dedicatedReport" value="allCatalog" checked="checked" id="dedicatedReportAllCatalog">
																		<span>Full catalog</span>
																	</label>
																	<label class="radio-inline dedicatedLabelForAtLeastLabel">
																		<input class="gaEvent" data-ga-event-type="type_select_labels" data-report-option="single labels" type="radio" name="dedicatedReport" value="atLeastLabel">
																		<span>Select labels</span>
																	</label>
																	<label id="artist-tooltip" data-type="artist" class="radio-inline" title="">
																		<input class="gaEvent" data-ga-event-type="type_select_artists" data-report-option="single artists" type="radio" name="dedicatedReport" value="atLeastArtist" data-toggle="tooltip-single">
																		<span>Select artists</span>
																	</label>
																	<label id="release-tooltip" data-type="release" class="radio-inline" title="">
																		<input class="gaEvent" data-ga-event-type="type_select_releases" data-report-option="single releases" type="radio" name="dedicatedReport" value="atLeastRelease" data-toggle="tooltip-single">
																		<span>Select releases</span>
																	</label>
																	<label class="radio-inline">
																		<input class="gaEvent" data-ga-event-type="type_select_tracks" data-report-option="single tracks" type="radio" name="dedicatedReport" value="atLeastTrack">
																		<span>Select tracks</span>
																	</label>
																	<label class="radio-inline">
																		<input class="gaEvent" data-ga-event-type="type_select_platform" data-report-option="single plateforms" type="radio" name="dedicatedReport" value="atLeastStore">
																		<span>Select platforms</span>
																	</label>
																	<!--<label class="radio-inline">
																		<input class="gaEvent" data-ga-event-type="type_select_countries" data-report-option="single countries regions" type="radio" name="dedicatedReport" value="atLeastCountry" id="dedicatedReportCountry">
																		<span>Select countries / regions</span>
																	</label> -->
																</div>
																<div id="selectDedicatedMyReport" class="col-md-10 col-md-offset-1">
																	<div class="atLeastLabel sel_report_fld"><select type="multiselect" name="atLeastLabel" id="atLeastLabel" class="select2" multiple placeholder="Select labels">
																	</select></div>
																	<div class="atLeastArtist sel_report_fld"><select type="multiselect" name="atLeastArtist" id="atLeastArtist" class="select2" multiple placeholder="Select artist">
																	</select></div>
																	<div class="atLeastRelease sel_report_fld"><select type="multiselect" name="atLeastRelease" id="atLeastRelease" class="select2" multiple placeholder="Select releases">
																	</select></div>
																	<div class="atLeastTrack sel_report_fld"><select type="multiselect" name="atLeastTrack" id="atLeastTrack" class="select2" multiple placeholder="Select tracks">
																	</select></div>
																	<div class="atLeastStore sel_report_fld"><select type="multiselect" name="atLeastStore" id="atLeastStore" class="select2" multiple placeholder="Select platforms">
																	</select></div>
																	
																</div>
															</div>
														</div>
													</div>
												</div>
												<!--END DEDICATED REPORT-->

												<!--SPLIT REPORT-->
												<div class="row">
													<div class="col-md-12">
														<div id="splitMyReport" style="display: none;">
															<div class="form-group">
																<div class="text-center multiple-report">
																	<label class="radio-inline splitLabelPerLabels">
																		<input class="gaEvent" data-ga-event-type="type_per_labels" data-report-option="multiple labels" type="radio" name="splitReport" value="perLabels" checked="checked">
																		<span>Per Labels</span>
																	</label>
																	<label id="multiple-artist-tooltip" data-type="artist" title="" class="radio-inline">
																		<input class="gaEvent" data-ga-event-type="type_per_artists" data-report-option="multiple artists" type="radio" name="splitReport" value="perArtists">
																		<span>Per Artists</span>
																	</label>
																	<label class="radio-inline" data-type="release" id="multiple-release-tooltip" title="">
																		<input class="gaEvent" data-ga-event-type="type_per_releases" data-report-option="multiple releases" type="radio" name="splitReport" value="perReleases">
																		<span>Per Releases</span>
																	</label>
																	<label class="radio-inline">
																		<input class="gaEvent" data-ga-event-type="type_per_platform" data-report-option="multiple plateforms" type="radio" name="splitReport" value="perStores">
																		<span>Per platform</span>
																	</label>
																	<!--<label class="radio-inline">
																		<input class="gaEvent" data-ga-event-type="type_per_countries" data-report-option="multiple countries" type="radio" name="splitReport" value="perCountries">
																		<span>Per countries</span>
																	</label>-->
																</div>
															</div>
														</div>
													</div>
												</div>
												<!--END SPLIT REPORT-->
											</div>
										</div>
											 </div>
										  </div>
										  <div role="tabpanel" class="tab-pane" id="generate">
											<div class="design-process-content">
												<div class="row">
													<div class="col-md-4">
														<h5><strong class="text-uppercase">Selected Period : </strong></h5>
														<div id="summaryPeriod"></div>
													</div>
													<div class="col-md-4">
														<h5><strong class="text-uppercase">Selected report : </strong></h5>
														<div id="summaryReport"><ul></ul></div>
													</div>
													<div class="col-md-4">
														<a href="javascript:;" role="menuitem" class="btn" id="generateReport" >Generate</a>
													</div>
												</div>
											 </div>
										  </div>
										
									</div>
									</form>
								  </div>
								</div>
							  </div>
							</section>

  
				<?php }?>
</div>
</div>
						</div>
<!-- end widget -->

