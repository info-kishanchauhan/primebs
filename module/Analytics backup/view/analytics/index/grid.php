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
    border-radius: 10px;
   
    padding: 20px;
    text-align: center;
    border: 1px solid #e1e1e1;
}

.icon-and-value {
    display: flex;
    align-items: center;
    gap: 24px;
   
}



.stat-value {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
	text-align:left;
}

.stat-label {
    color: #999;
    margin-bottom: 10px;
	font-size:16px;
	font-weight:600;
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
	border-radius: 10px;
    border: 1px solid #e1e1e1;
	border-collapse: separate;
}
.table>thead>tr>th
{
	border-bottom:0px;
	padding:16px;
	font-size:11px;
}
.table>tbody>tr>td
{
	font-size:14px;
	padding:16px;
}
thead th:first-child {
    border-top-left-radius: 10px; /* Rounded top-left corner */
}

thead th:last-child {
    border-top-right-radius: 10px; /* Rounded top-right corner */
}

tbody td:first-child {
    border-bottom-left-radius: 10px; /* Rounded bottom-left corner */
}

tbody td:last-child {
    border-bottom-right-radius: 10px; /* Rounded bottom-right corner */
}
.css-jsf2o5 {
    width: 1em;
    height: 1em;
    display: inline-block;
    fill: currentcolor;
    flex-shrink: 0;
    font-size: 18px;
    margin-right: 4px;
    margin-left: 8px;
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
</style>

<div class="row" style="max-width:1280px;margin: auto;">
	<div class="row" style="margin-bottom:20px;">
	<div class="filterContainer">
		<div class="form-group">
		  <div class="col-sm-3">
			<label><strong class="txt-color-blue">From Month</strong></label>
			<input class="form-control input-sm inputString inputBehavior-2 form-control" name="from_month" id="from_month" type="text" value="<?php echo $this->last_month;?>" style="">
		  </div>
		  <div class="col-sm-3">
			<label><strong class="txt-color-blue">To Month</strong></label>
			<input class="form-control input-sm inputString inputBehavior-2 form-control" name="to_month" id="to_month" type="text" value="<?php echo $this->last_month;?>" style="">
		  </div>
		  <div class="col-sm-1 no-padding filter-searches">
			<button class="btn btn-primary btn-sm inputBehavior- mt20" id="btnSearch" type="button">
			  <span class="glyphicon glyphicon-search"></span> Search </button>
		  </div>
		  <div class="col-sm-3">
		  <input type="search" class="form-control glyphicon modal-search-input" id="search" placeholder="Type and press Enter" style="font-family: system-ui">
		   </div>
		  <?php 
			if($_SESSION['user_id'] == '0')
			{
		  ?>
			<div class="col-md-2" style="margin-top: 20px;">
				<a href="javascript:;" class="btn btn-primary btn-sm pull-right btn-import" id="importpopup">Import</a>
			</div>
			<?php } ?>
		 </div>
	</div>
	</div>
	<hr>
	<div class="">
		<div class="stats-container">
			<div class="stat-box col-md-4">
				<div class="MuiBox-root css-plypk7"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of videos using this track that were created on short-form platforms."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></div>
				<div class="icon-and-value">
					<img src="public/img/social_group.PNG" alt="TikTok icon" class="icon">
					<div class="sec_flex">
						<p class="stat-value" id="tot_creation">0</p>
						<p class="stat-label">Creations</p>
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
					<img src="public/img/stream.PNG" alt="Audio icon" class="icon">
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
  
  
  <table id="tblMasterList" class="table dataTable no-footer" width="100%">



<col width="8%">
<col width="25%">
<col width="14%">
<col width="14%">
<col width="11%">
<col width="14%">
<col width="20%">

                <thead>

                <tr>
					<th></th>
					<th><?php echo $this->translate('TRACK'); ?></th>
					<th></th>
					<th><?php echo $this->translate('LAST RELEASE DATE'); ?></th>
					<th class="sort"><?php echo $this->translate('CREATIONS'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="UnfoldMoreIcon"><path d="M12 5.83 15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15z"></path></svg></span></th>
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
