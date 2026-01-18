<!-- ✅ CSS Links 
<link rel="stylesheet" type="text/css" href="<?php echo $this->basePath(); ?>/public/css/index.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $this->basePath(); ?>/public/css/anlytics_view.css">
<link rel="stylesheet" type="text/css" href="<?php echo $this->basePath(); ?>/public/css/report.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">-->

<!-- ✅ JS Libraries 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>-->

<!-- ✅ Custom Inline Styles -->
<style>
body {
  font-family: 'Inter', sans-serif;
  background: #f4f5fa;
  margin: 0;
  padding: 0;
}

  #bs-main {
  background-color: #f7f7f8 !important;
}
.custom-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 14px;
  background: #fff;
  border-radius: 6px;
  margin: 0 auto 20px auto;
  max-width: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  flex-wrap: wrap;
  gap: 20px;
}

.song-info-section {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
}

.statement-block {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.song-artist {
  font-size: 16px;
  color: #2a2a2a;
  display: flex;
  align-items: center;
  gap: 6px;
}

.song-artist a {
  color: #2a2a2a;
  text-decoration: none;
}

.song-artist span {
  color: #777;
}

.statement-meta {
  font-size: 13px;
  color: #4a4a4a;
  display: flex;
  align-items: center;
  gap: 6px;
}

.custom-box svg {
  min-width: 14px;
  min-height: 14px;
}

.section-card {
  background: #ffffff;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 14%);
  margin:30px;
  
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.section-header h5 {
  font-size: 20px;
  color: #2d2d2d;
  margin: 0;
}

.button-purple, #generateReport {
    background: #76319c;
    border: none;
    padding: 10px 11px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
}
.nav-tabs {
  border: none;
  margin-bottom: 10px;
}

.nav-tabs > li {
  margin-right: 8px;
}

.nav-tabs > li > a {
  background: #f1f0fa;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  color: #3f3f3f;
  /*transition: all 0.3s ease;*/
}

.nav-tabs > li.active > a,
.nav-tabs > li > a:hover {
  background: #76319c !important;
  color: #fff !important;
}

.table {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  margin-top: 10px;
}

.table thead th {
  background: #f9f9fb;
  font-size: 13px;
  color: #333;
  padding: 14px 18px;
  border-bottom: 1px solid #e4e4e4;
  text-transform: uppercase;
}

.table tbody td {
  font-size: 14px;
  padding: 14px 18px;
  color: #374151;
  border-top: 1px solid #f0f0f0;
  vertical-align: middle;
}

.modal-dialog.modal-lg {
  max-width: 800px;
}

.modal-body .form-control {
  height: 45px;
  font-size: 15px;
  border-radius: 8px;
}

.nav-tabs.process-model {
  display: flex;
  justify-content: center;
  border-bottom: none;
  margin-bottom: 20px;
}

.nav-tabs.process-model > li {
  width: auto;
  margin: 0 20px;
  text-align: center;
}

.nav-tabs.process-model > li > a {
  color: #6d3bbd;
  border: none;
  background: #f1f0fa;
  padding: 12px 20px;
  border-radius: 10px;
}

.nav-tabs.process-model > li.active > a {
  background-color: #76319c !important;
  color: #fff !important;
  border-radius: 10px;
}
</style>

<!-- ✅ Info Box with Report Button -->
<div class="custom-box">
  <div class="song-info-section">
    <div class="statement-block">
      <div class="song-artist">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#7d2491">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 
                   10 10 10-4.48 10-10S17.52 2 12 2zm0 
                   18c-4.41 0-8-3.59-8-8s3.59-8 
                   8-8 8 3.59 8 8-3.59 8-8 
                   8zm0-10a2 2 0 110 4 2 2 0 010-4z"/>
        </svg>
        <strong>Account Name:</strong> <?php echo "N/A"; ?>
      </div>
      <div class="statement-meta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#888">
          <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 
                   0-2 .9-2 2v16c0 1.1.9 2 
                   2 2h14c1.1 0 2-.9 
                   2-2V5c0-1.1-.9-2-2-2zm0 
                   18H5V8h14v13z"/>
        </svg>
        <strong>Currency:</strong> <?php echo "EURO"; ?>
      </div>
    </div>
  </div>
  <div class="text-right" style="margin-bottom: 0px;">
    <button class="button-purple" id="showGenerateReportWizard">+ Generate Report</button>
  </div>
</div>

<!-- ✅ Report Management Section -->
<div class="section-card">
  <div class="section-header">
    <h5>Report Management</h5>
    <?php if ($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1') { ?>
    <div style="display: flex; gap: 10px;">
      <button class="button-purple" id="btnNewReport">Upload Report</button>
    </div>
    <?php } ?>
  </div>

  <ul class="nav nav-tabs">
    <?php if ($_SESSION['SUBUSER'] == '0' ) { ?>
		<li role="presentation" class="active"><a href="#monthlyGeneratedReports" data-toggle="tab">Auto-Generated Statements</a></li>
	<?php }?>
    <li role="presentation" class="<?php echo ($_SESSION['SUBUSER'] == '1')?'active hide':'';  ?>"><a href="#requestedReports" data-toggle="tab" class="generatereporttab">User Generated Reports</a></li>
    <?php if ($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1') { ?>
    <li role="presentation"><a href="#userReports" data-toggle="tab">Hold Payments</a></li>
    <?php } ?>
  </ul>

  <div class="tab-content">
    <div role="tabpanel" class="tab-pane <?php echo ($_SESSION['SUBUSER'] == '0')?'active':'';  ?>" id="monthlyGeneratedReports">
	 <div class="catalogContent">
      <table id="tblMasterList" class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Period</th>
            <th>Report Type</th>
            <th>Royalty Amount</th>
            <th>Generation Date</th>
            <th>Payment Status</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
	  </div>
    </div>
    <div role="tabpanel" class="tab-pane <?php echo ($_SESSION['SUBUSER'] == '1')?'active':'';  ?>" id="requestedReports">
	 <div class="catalogContent">
      <table id="tblMasterList2" class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Period</th>
            <th>Report Type</th>
            <th>Royalty Amount</th>
            <th>Generation Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
	   </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="userReports">
	 <div class="catalogContent">
      <table id="tblMasterList3" class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Company Name</th>
            <th>Label Name</th>
            <th>Sales Month</th>
            <th>Royalty Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
	  </div>
    </div>
  </div>
</div>

<!-- Hidden Inline Wizard Section -->
<div id="generateReportWizardBox" >
  <div class=" panel-default" style="padding: 20px 12px; border-radius: 12px;padding-bottom:60px;">
    <div style="background: #eef7ff; padding: 18px 22px; border-left: 5px solid #007bff; border-radius: 10px; font-size: 14px; color: #2c3e50; line-height: 1.6; margin-bottom: 25px;">
  <strong>📊 GENERATE REPORT</strong><br>
  • <strong>Note:</strong> Each report is generated in real-time and may take a few seconds depending on the data size.
</div>
<?php
					//if($_SESSION['user_id'] == '0')
					//{
				?>
						
					   <div  style="position:relative" id="generateSection">
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
									 
									  <li role="presentation"><a href="#f_report_type" aria-controls="report_type" role="tab" data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i>
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
													<div class="col-md-12">
														
													</div>
												</div>
												<div class="row" style="padding: 5px;">
													
													<div class="col-sm-6">
														 <div class="form-group form-group-sm">
															<div class="control-label">
															   <label class=""><strong class="txt-color-blue"><?php echo $this->translate('From'); ?></strong></label>
															   <span style="position: absolute; white-space: nowrap; z-index: 100;"><span style="color: red;"></span></span>
															</div>
															<div class="">
																<input type="text" id="fromDate" name="fromDate" class="form-control " >
															</div>
														 </div>
													 </div>
													 <div class="col-sm-6">
														 <div class="form-group form-group-sm">
															<div class="control-label">
															   <label class=""><strong class="txt-color-blue"><?php echo $this->translate('To'); ?></strong></label><span style="position: absolute; white-space: nowrap; z-index: 100;"><span style="color: red;"></span>
															</div>
															<div class="">
																<input type="text" id="toDate" name="toDate" class="form-control " >
															</div>
														 </div>
													 </div>
													
												</div>
											 </div>
										  </div>
										  <div role="tabpanel" class="tab-pane" id="f_report_type">
											<div class="design-process-content">
												<div class="row">
													<div class="col-md-12">
														
													</div>
												</div>
												<div class="row">
											
												
												<div class="col-md-6 text-center gaEvent" data-ga-event-type="type_single_report">
													<div data-toggle="tooltip" data-placement="left"  class="like-btn square150 reportTypeBtn like-btn-primary" id="useDedicatedReport" title="Generate a single report including your full catalog or filtered on your labels, artists, releases, tracks, platforms or countries.">
														<p class="littleTitleLikeButton">Single Report</p>
													</div>
												</div>

												<div class="col-md-6 text-center gaEvent" data-ga-event-type="type_multiple_report">
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
												
												<input type="hidden" id="reportType" name="reportType" value="single">

												<!--DEDICATED REPORT-->
												
													<div class="col-md-12">
														<div id="dedicatedMyReport" style="display: block;">
															<div class="form-group">
																<div class="single-report" style="display:grid;grid-template-columns: 1fr 1fr;">
																	<label class="radio-inline dedicatedLabelForAllCatalog" style="margin-left: 10px;">
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
																<div id="selectDedicatedMyReport" class="col-md-12">
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
										
										   
										  <div role="tabpanel" class="tab-pane" id="generate">
											<div class="design-process-content">
												<div class="row" style="padding: 5px;">
													<div class="col-md-12">
														<strong class="text-uppercase">Selected Period : </strong><span id="summaryPeriod"></span>
														
													</div>
													
												</div>
												<div class="row" style="margin-top:10px;padding: 5px;">
													<div class="col-md-12">
														<strong class="text-uppercase">Selected report : </strong><span id="summaryReport"></span>
													</div>
													
													<a href="javascript:;" role="menuitem" class="btn" id="generateReport" >Generate</a>
												</div>
											 </div>
										  </div>
										
									</div>
									</form>
								  </div>
								</div>
							  </div>
							</section>

				<?php //}?>
</div>
</div>
						</div>
						<div class="sidebar-footer">
							<button  class="btn btn-default closewidget">Close</button>
                          
                       <!-- ✅ Final working button -->
  <button type="button" id="footerNextButton" class="btn btn-primary" style="background: linear-gradient(to right, #7d2491, #3493ef); border: none; border-radius: 8px; font-weight: 500;"> ›› Next</button>
</div>
				
</div>
<!-- end widget -->



<!-- Step Wizard Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const nextBtn = document.getElementById("footerNextButton");

  const tabSequence = ['#period', '#f_report_type', '#generate'];

  function getCurrentTabIndex() {
    const activeTab = document.querySelector('.nav-tabs.process-model li.active');
    const tabs = document.querySelectorAll('.nav-tabs.process-model li');
    return Array.from(tabs).indexOf(activeTab);
  }

  function updateNextButtonVisibility() {
    const currentIndex = getCurrentTabIndex();
    if (currentIndex >= tabSequence.length - 1) {
      nextBtn.style.display = 'none';
    } else {
      nextBtn.style.display = 'inline-block';
    }
  }

  function validateStep(index) {
    if (tabSequence[index] === '#period') {
      const from = document.getElementById('fromDate').value.trim();
      const to = document.getElementById('toDate').value.trim();
      if (!from || !to) {
        alert("Please select both 'From' and 'To' dates before continuing.");
        return false;
      }
    }

    if (tabSequence[index] === '#f_report_type') {
      const selected = document.querySelector('input[name="reportType"]:checked');
      if (!selected) {
        alert("Please select a Report Type before continuing.");
        return false;
      }
    }

    return true;
  }

  nextBtn?.addEventListener("click", function () {
    const currentIndex = getCurrentTabIndex();

    // Validate before advancing
    if (!validateStep(tabSequence[currentIndex])) {
      return;
    }

    if (currentIndex < tabSequence.length - 1) {
      const nextTabId = tabSequence[currentIndex + 1];
      const nextTabLink = document.querySelector(`.nav-tabs li a[href="${nextTabId}"]`);
      nextTabLink?.click();
    }

    // If moving to generate step, fill summary
    if (tabSequence[currentIndex + 1] === '#generate') {
      const from = document.getElementById("fromDate")?.value || "N/A";
      const to = document.getElementById("toDate")?.value || "N/A";
      const reportType = document.querySelector('input[name="reportType"]:checked')?.value || "N/A";

      document.getElementById("summaryPeriod").innerText = `${from} to ${to}`;
      document.getElementById("summaryReport").innerText = reportType;
    }

    // Update button visibility after moving tab
     updateNextButtonVisibility();
  });

  // Also update visibility on tab click
  const allTabs = document.querySelectorAll('.nav-tabs.process-model li a');
  allTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      updateNextButtonVisibility();
    });
  });

  updateNextButtonVisibility();
});
</script>

<!-- Optional CSS to make tabs nice -->
<style>
.nav-tabs.process-model {
  display: flex;
  justify-content: center;
  border-bottom: none;
  margin-bottom: 20px;
}

.nav-tabs.process-model > li {
  margin: 0 5px;
    text-align: center;
    width: 115px;
}

.nav-tabs.process-model > li > a {
  color: #6d3bbd;
  font-weight: bold;
  border: none;
  background: #f1f0fa;
  padding: 12px 20px;
  border-radius: 10px;
}

.nav-tabs.process-model > li.active > a {
  background-color: #76319c !important;
  color: #fff !important;
  border-radius: 10px;
}

</style>                      