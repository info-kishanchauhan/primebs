<style>
.stats-container {
    display: flex;
    gap: 20px;
}
.mt20
{
	margin-top:20px;
}
.tooltip
{
	width:300px;
	padding:15px;
}
.css-plypk7 {
    position: absolute;
    right: 0px;
    top: 0px;
    padding: 24px;
}
.css-558cd0 {
    user-select: none;
    display: inline-block;
    fill: currentcolor;
    flex-shrink: 0;
    font-size: 1.5rem;
    color: rgb(101, 108, 130);
    width: 20px;
    height: 20px;
    padding: 0px;
    transition: 300ms cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-box {
    background-color: white;
    border-radius: 20px;
   
    padding: 20px;
    text-align: center;
    border: 1px solid #e6e6e6b5;
}

.icon-and-value {
    display: flex;
    align-items: center;
    gap: 24px;
   
}
.switch-button:hover {
    background-color: #ecf1ff;
}


.stat-value {
    font-size: 21px;
    font-weight:700;
    margin: 0;
	text-align:left;
}

.stat-label {
    color: rgb(124, 128, 140);
    margin-bottom: 10px;
  font-family: "Open Sans", Roboto, Arial, sans-serif;
	font-size:16px;
	font-weight:510;
	text-align:left;
}

.stat-change {
    display: flex;
    justify-content: center;
    align-items: center;
}

.stat-percentage {
    font-size: 12px;
    padding: 5px;
    border-radius: 5px;
    font-weight: bold;
}


.table{
	border-radius: 20px;
    border: 1px solid #e6e6e6b5;
	border-collapse: separate;
}
.table>thead>tr>th {
    border-bottom: 0px;
    padding: 16px;
    font-family: "Open Sans", Roboto, Arial, sans-serif;
    color: rgb(84, 88, 97);
    font-size: 0.900rem;
}
  .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 12.5px;
    line-height: 1.42857143;
    vertical-align: top;
    border-top: 1px solid #e6e6e6b5;
}
.table>tbody>tr>td
{
	font-size:13px;
	padding:16px;
}
thead th:first-child {
    border-top-left-radius: 20px; /* Rounded top-left corner */
}

thead th:last-child {
    border-top-right-radius: 20px; /* Rounded top-right corner */
}

tbody td:first-child {
    border-bottom-left-radius: 20px; /* Rounded bottom-left corner */
}

tbody td:last-child {
    border-bottom-right-radius: 20px; /* Rounded bottom-right corner */
}
.css-jsf2o5 {
    width: 1em;
    height: 1em;
    display: inline-block;
    fill: currentcolor;
    flex-shrink: 0;
    font-size: 18px;
    margin-right: 4px;
    margin-left: 4px;
    opacity: 1;
    user-select: none;
    transform: rotate(0deg);
    transition: opacity 200ms cubic-bezier(0.4, 0, 0.2, 1), transform 200ms cubic-bezier(0.4, 0, 0.2, 1);
}
th.sort
{
	cursor:pointer;
}
.css-jsf2o5.active {
    color: rgb(38, 153, 251);
}
.modal-search-input{
    border-radius: 16px;
    background-color: #EAECF6;
    border: 0;
    height: 35px;
    text-align: left;
	margin-top: 17px;
}
h3{
    margin: 0px;
    font-family: "Open Sans", Roboto, Arial, sans-serif;
    font-weight: 700;
    line-height: normal;
    font-size: 2.4rem;
    letter-spacing: 0em;
    text-decoration: none;
    color: rgb(31, 31, 40);
}
 .switch-group {
            display: inline-flex;
            gap: 10px;
            padding: 5px;
            border-radius: 20px;
            
        }
        .switch-button {
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            background-color: transparent;
            transition: all 0.2s ease;
        }
        .switch-button.active {
            background-color: #e0e7ff;
            color: #000;
        }
		.custom_filter
		{
			position: absolute;
			top: 100%;
			/* left: 0; */
			right: 0;
			z-index: 1000;
			display: none;
			float: left;
			min-width: 160px;
			 padding: 20px 10px;
			margin: 2px 0 0;
			font-size: 14px;
			text-align: left;
			list-style: none;
			background-color: #fff;
			-webkit-background-clip: padding-box;
			background-clip: padding-box;
			border: 1px solid #ccc;
			/* border: 1px solid rgba(0, 0, 0, .15); */
			border-radius: 15px;
			-webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
			box-shadow: 2px 2px 10px 5px rgba(0, 0, 0, .175);
		}
		.custom_filter::before {
			content: "";
			position: absolute;
			top: -9px;
			right:22px;
			transform: translateX(-50%);
			border-width: 0 10px 10px 10px;
			border-style: solid;
			border-color: transparent transparent white transparent;
		}
		.filter-searches
		{
			margin-top: 4px;
		}
		.flex_div
		{
			display: flex;
			justify-content: space-evenly;
			gap:8px;
		}

.month-selector button:hover {
    background-color: #007bff !important; /* Change to primary blue */
    color: white !important; /* Ensure text is readable */
    border-radius: 5px; /* Smooth rounded corners */
    transition: 0.3s ease-in-out;
}

#bs-main-content-container {
    padding-top: 55px;
    padding-bottom: 30px;
}
.insights-text {
    font-weight: bold !important;
}

/* 🔵 Hover effect on table rows */
.table tbody tr:hover {
    background-color: #f0f6ff;
    transition: background-color 0.1s ease;
    cursor: pointer;
}

</style>

<div class="row" style="max-width:1280px;margin: auto;">
	<div class="row" style="margin-bottom:20px;">
	<div class="filterContainer">
		<div class="form-group">
		  <div class="col-sm-6">
			<label><h3>Insights <svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Track and analyze or music Performance, Revenue & Audience Metrics."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></h3></label><br>
			<span style="font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 500; color: #5a5a5a;">
  Streaming Insights: Performance, Revenue & Audience Metrics
</span>

		  </div>
		  <div class="col-sm-6">
			<div class="MuiBox-root css-0 row pull-right">
				<div class="switch-group">
					<div class="switch-button active" onclick="setActive(this)">1M</div>
					<div class="switch-button" onclick="setActive(this)">2M</div>
					<div class="switch-button" onclick="setActive(this)">3M</div>
					<div class="switch-button" onclick="setActive(this)">Custom</div>
				</div>
			</div>
		 </div>
		 </div>
		 
		 <div class="col-sm-6 pull-right">
		    <div class="custom_filter row">
		  
			  <div class="flex_div">
			  <div class="">
				<label><strong class="txt-color-blue">From Month</strong></label>
				<input class="form-control input-sm inputString inputBehavior-2 form-control" name="from_month" id="from_month" type="text" value="" style="">
			  </div>
			  <div class="">
				<label><strong class="txt-color-blue">To Month</strong></label>
				<input class="form-control input-sm inputString inputBehavior-2 form-control" name="to_month" id="to_month" type="text" value="" style="">
			  </div>
			  
				  <?php 
					if($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1')
					{
				  ?>
						<div class=" no-padding filter-searches">
							<button class="btn btn-primary btn-sm inputBehavior- mt20" id="btnSearch" type="button"><span class="glyphicon glyphicon-search"></span> Search </button>
							<a href="javascript:;" class="btn btn-primary btn-sm pull-right btn-import mt20" id="importpopup">Import</a>
						</div>
				<?php }
						else
					{
						?>
						<div class=" no-padding filter-searches">
							<button class="btn btn-primary btn-sm inputBehavior- mt20" id="btnSearch" type="button"><span class="glyphicon glyphicon-search"></span> Search </button>
							
						</div>
						<?php
					}

				?>
			  
		  </div>
		  </div>
		 </div>
	</div>
	</div>
	
	<div class="">
		<div class="stats-container">
			<div class="stat-box col-md-4">
				<div class="MuiBox-root css-plypk7"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of videos using this track that were created on short-form platforms."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></div>
				<div class="icon-and-value">
					<img src="public/img/social_group.PNG" alt="TikTok icon" class="icon">
					<div class="sec_flex" style="display:flex;">
						<div class="stat-value" style="line-height:22px">
						<span id="tot_creation">0</span><br><span class="stat-label">Creations</span>
						
						</div>
						<div class="stat-pipe" style="font-size: 26px;padding: 0px 7px;color: #aba2a2;font-weight: 700;"> | </div>
						<div class="stat-value" style="line-height:22px">
						
						<span id="tot_view">0</span><br><span class="stat-label">Views</span>
						</div>
						<p id="tot_creation_comp"></p>
					</div>
				</div>
			</div>

			<div class="stat-box col-md-4">
				<div class="MuiBox-root css-plypk7"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of Revenue using this track that were created on short-form platforms."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></div>
				<div class="icon-and-value">
					<img src="public/img/revenue.png" alt="Revenue icon" class="icon" width="80" style="padding: 10px;border: 1px solid #eee;border-radius: 10px;">
					<div class="sec_flex">
						<p class="stat-value" id="tot_revenue">0</p>
						<p class="stat-label">Revenue</p>
						<p id="tot_revenue_comp"></p>
						<p></p>
					</div>
				</div>
			</div>

			<div class="stat-box col-md-4">
				<div class="MuiBox-root css-plypk7"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of audio streams generated by your music."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></div>
				<div class="icon-and-value">
					<img src="public/img/stream.png" alt="Audio icon" class="icon">
					<div class="sec_flex">
						<p class="stat-value" id="tot_stream">0</p>
						<p class="stat-label">Audio streams</p>
						<p id="tot_stream_comp"></p>
						<p></p>
					</div>
					
				</div>
				
			</div>
		</div>
	</div>
	
<div id="catalogContent" style="margin-top:20px;">
  
   <div class="col-sm-3 right" >
		  <input type="search" class="form-control glyphicon modal-search-input" id="search" placeholder="Type and press Enter" style="font-family: system-ui;margin-bottom: 20px;">
   </div>
  <table id="tblMasterList" class="table dataTable no-footer" width="100%">



<col width="8%">
<col width="20%">
<col width="12%">
<col width="12%">
<col width="12%">
<col width="11%">
<col width="12%">
<col width="20%">

                <thead>

                <tr>
					<th></th>
					<th><?php echo $this->translate('TRACK'); ?></th>
					<th></th>
					<th><?php echo $this->translate('LAST RELEASE DATE'); ?></th>
					<th class="sort"><?php echo $this->translate('CREATIONS'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="UnfoldMoreIcon"><path d="M12 5.83 15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15z"></path></svg></span></th>
					<th class="sort"><?php echo $this->translate('VIEWS'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="UnfoldMoreIcon"><path d="M12 5.83 15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15z"></path></svg></span></th>
					<th><?php echo $this->translate('SALES MONTH'); ?></th>
					<th  class="sort"><?php echo $this->translate('REVENUE'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5 active" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="SouthIcon"><path d="m19 15-1.41-1.41L13 18.17V2h-2v16.17l-4.59-4.59L5 15l7 7z"></path></svg></span></th>
					<th  class="sort" style="display: inline-flex;white-space: nowrap;align-items: center;"><?php echo $this->translate('AUDIO STREAMS &nbsp;'); ?>
						<img src="public/img/stream-icon.png">
						<span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="UnfoldMoreIcon"><path d="M12 5.83 15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15z"></path></svg></span>
					</th> 
					


                </tr>

                </thead>

                <tbody>

					

                </tbody>

  </table>
 </div>
</div>
