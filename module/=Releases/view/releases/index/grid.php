<style>
.delivery_report_icon
{
	width:11px;height:11px;border-radius:100%;background-color:#17ad41;float: left;margin-right: 5px;
}
.d_status
{
	display: flex;
    align-items: center;
    flex-wrap: nowrap;
    width: fit-content;
    padding: 3px 10px;
    border-radius: 20px;
	margin: 0 auto;
	cursor:pointer;
}
.d_status:hover
{
	box-shadow: 0px 0px 3px 1px rgba(0, 0, 0, 0.1);
	background: #fff;
}
.d_status_wrapper {
  position: relative;
  display: inline-block;
  width:100%;
}
.delivery-popover {
  display: none;
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-bottom: 8px;
  background: #fff;
  border: 1px solid rgb(0 0 0 / 27%);
  border-radius: .3rem;
  box-shadow: 0 0 10px 0 #9da6af;
  padding: 10px;
  width: 280px;
  z-index: 999;
  white-space: normal;
  /* optional smooth fade-in */
  transition: opacity 0.2s ease-in-out;
}

.delivery-popover a {
  
 
  align-items: center;
  text-decoration: none;
 
  margin: 6px 0;
}
.p-title {
  
  font-weight: 600;
  font-size: 13px;
  font-family: rubik, -apple-system, blinkmacsystemfont, Segoe UI, roboto, Helvetica Neue, arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  padding-bottom: 8px;
  border-bottom: 1px solid #d5d9dd;
  text-align: left;
}

.delivery-link {
  display: flex;
  align-items: center;
  margin: 1px 0;
  gap: 10px;
}

.service-icon {
  width: 15px;
  height: 15px;
  object-fit: contain;
  flex-shrink: 0;
}

.link-text {
  flex-grow: 1;
  text-decoration: none;
  font-weight: 500;
  font-family: rubik, -apple-system, blinkmacsystemfont, Segoe UI, roboto, Helvetica Neue, arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 12px;
}
.view-delivery-report {
  text-align: center;
  display: block;
  font-weight: 600;
  font-size: 11px;
  font-family: rubik, -apple-system, blinkmacsystemfont, Segoe UI, roboto, Helvetica Neue, arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  margin-top: 12px;
  cursor: pointer;
  user-select: none;
  text-decoration: none;
}


.copy-btn {
  background: none;
  border: none;
  font-weight: 600;
  font-family: rubik, -apple-system, blinkmacsystemfont, Segoe UI, roboto, Helvetica Neue, arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  color: #0070e0;
  cursor: pointer;
  padding: 0 6px;
  font-size: 9px;
}

/* Optional: Add a little arrow pointing down */
.delivery-popover::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -6px;
  border-width: 6px;
  border-style: solid;
  border-color: #ffffff transparent transparent transparent;
}

.delivery-popover::before {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -7px;
  border-width: 7px;
  border-style: solid;
  border-color: #ccc #00000000 transparent transparent;
 
}
.summary
{
	display: flex;
	gap: 16px;
	padding: 7px 8px;
	border-radius: 10px;
	background: linear-gradient(145deg, #f5f6f7, #e6e7e8); /* Subtle light grey blend */
	/*box-shadow:
		4px 4px 12px rgba(0, 0, 0, 0.06),     
		-4px -4px 12px rgba(255, 255, 255, 0.8), 
		inset 0 1px 2px rgba(255, 255, 255, 0.6);op inner edge */
	/*border: 1px solid #dcdedf; /* Slightly darker than #EFF0F1 for depth */
	background-color: #EFF0F1;
	transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.summary_info
{
	display: flex;
    /*padding:20px 20px;*/
    background-color: #fff;
    border-radius: 7px;
    box-shadow: 1px 1px 1px 1px #e1dcdc;
    margin-bottom: 0px;
    font-size: 14px;
    font-weight: 560;
    gap: 20px;
	color: #000;
	padding-left: 20px;
    padding-right: 20px;
   
}
.summary_info .title
{
	width:100px;
	color:#424141;
    font-weight: 450;
    font-family: rubik, -apple-system, blinkmacsystemfont, Segoe UI, roboto, Helvetica Neue, arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
}
.count
{
    margin: 0px;
    font-family: "Open Sans", Roboto, Arial, sans-serif;
    font-weight: 600;
    line-height: normal;
    font-size: 1.2rem;
    letter-spacing: 0em;
    text-decoration: none;
    color: #4d4a4f
}
.summary_info div{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-content: center;
    align-items: center;
}

.catalog
{
	display: flex;
	padding: 15px;
    align-items: center;
	background-color: #fff;
    border-radius: 7px;
    box-shadow: 1px 1px 1px 1px #e1dcdc;
    margin-bottom: 0px;
    font-size: 16px;
    gap: 30px;
    font-weight: 550;
	gap:10px;
}
#catalogContent
{
	margin-top:12px;
}
hr
{
	border:none;
}
#search_text
{
	width: 550px;
    background-color: #f7f7f7;
    line-height: 29px;
    height: 36px;
    font-size: 15px;
	border-radius: 10px;
}
 .divider {
            width: 2px;
            background-color: #EFF0F1;
            height: 99%;
        }
.more_actions
{
	cursor:pointer;
	display:flex;
  
}

.filterContainer .material-icons {
    margin-right: 8px;
    font-size: 18px;
}
.orchard-style-dropdown {
  list-style: none;
  margin: 0;
  padding: 10px 0;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  min-width: 230px;
  position: absolute;
  top: 42px;
  right: 0;
  z-index: 1000;
  font-family: "Inter", sans-serif;
  font-size: 14px;
}

.orchard-style-dropdown li a {
  display: flex;
  align-items: center;
  padding: 10px 16px;
  color: #1f2937;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s ease;
}

.orchard-style-dropdown li a:hover {
  background-color: #f9fafb;
}

.orchard-style-dropdown i.material-icons {
  font-size: 20px;
  margin-right: 10px;
  color: #dc2626; /* red for delete */
}

.orchard-style-dropdown .toggle-text {
  font-size: 14px;
}
  .badge-beta {
  font-size: 11px;
  font-weight: 600;
  background-color: #e0f2ff;
  color: #2563eb;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 6px;
  vertical-align: middle;
  text-transform: uppercase;
}
.search-wrapper {
  position: relative;
  display: inline-block;
  margin-top:6px;
}

.search-wrapper .search-icon {
  position: absolute;
  top: 50%;
  left: 27px;
  transform: translateY(-50%);
  color: #888;
}

.search-wrapper input {
  padding: 8px 8px 8px 36px; /* Add left padding for icon */
  border: 1px solid #ccc;
  border-radius: 4px;
}
.dropdown-toggle.more_actions:hover {
    color: #1976E6;
}
.toast-msg {
  position: fixed;
  bottom: 30px;
  right: -400px; /* Start completely off-screen */
  min-width: 260px;
  max-width: 340px;
  background: #1f1f1f;
  color: #fff;
  padding: 14px 20px;
  border-radius: 10px;
  font-family: 'Inter', sans-serif;
  font-size: 15px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.3);
  z-index: 99999;
  transition: right 0.5s ease, opacity 0.4s ease;
  opacity: 0;
}
.toast-msg.show {
  right: 30px; /* Slide into view */
  opacity: 1;
}
.toast-icon {
  font-size: 18px;
}
/* Smooth background transition on hover */
.col-sm-3.pull-right[style*="background: #7f4daa"] {
  transition: background-color 0.4s ease, box-shadow 0.3s ease;
}

/* Hover effect */
.col-sm-3.pull-right[style*="background: #7f4daa"]:hover {
  background-color: #6d3cb4; /* slightly darker purple */
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
}
  
.filter-dropdown {
  position: relative;
  flex: 0 1 160px;
}
.filter-btn {
  width: 100%;
  padding: 8px 12px;
  background: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 8px;
  text-align: left;
  font-size: 12px;
  cursor: pointer;
  transition: background 0.2s ease;
}
.filter-btn:hover { background: #f1f1f1; }
.filter-panel {
  display: none;
  position: absolute;
  top: 110%;
  left: 0;
  background: #fff;
  padding: 10px 14px;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  z-index: 99;
  min-width: 160px;
}
.filter-panel label {
  display: block;
  font-size: 13px;
  margin-bottom: 8px;
  cursor: pointer;
}
.filter-dropdown:hover .filter-panel {
  display: block;
}
</style>

 
  
<?php
	if(!isset($_GET['type']))
	{
	?>
				<div class="summary">
					<div class="catalog"><img src="http://www.primebackstage.in/public/img/icons/Home Catalog.png" alt="Catalog" style="width: 26px; height: 26px; margin-right: 8px;" /> Catalog</div>
					<div class="summary_info">
						<div id="" class="title">Total Tracks<span class="count"><?php echo $this->INFO['total_tracks'];?></span></div>	
						<div class="divider"></div>
						<div id="" class="title">Under Review<span class="count"><?php echo $this->INFO['in_review'];?></span></div>
						<div class="divider"></div>
						<div id="" class="title">In Process<span class="count"><?php echo $this->INFO['in_process'];?></span></div>
						<div class="divider"></div>
						<div id="" class="title">Delivered<span class="count"><?php echo $this->INFO['live_tracks'];?></span></div>	
						<div class="divider"></div>
						<div id="" class="title">Taken Down<span class="count"><?php echo $this->INFO['taken_down'];?></span></div>										
					</div>
				</div>
				
	<?php
	} 
	if($_GET['type'] == 'inprocess')
	{
	?>
				<div class="summary">
					<div class="catalog"><img src="http://www.primebackstage.in/public/img/icons/Home Catalog.png" alt="Catalog" style="width: 26px; height: 26px; margin-right: 8px;" /> Catalog</div>
					<div class="summary_info">
						<div id="" class="title">In Process<span class="count"><?php echo $this->INFO['in_process'];?></span></div>									
					</div>
				</div>
	<?php
	} 
	if($_GET['type'] == 'review')
	{
	?>
				<div class="summary">
					<div class="catalog"><img src="http://www.primebackstage.in/public/img/icons/Home Catalog.png" alt="Catalog" style="width: 26px; height: 26px; margin-right: 8px;" /> Catalog</div>
					<div class="summary_info">
						<div id="" class="title">In Review<span class="count"><?php echo $this->INFO['in_review'];?></span></div>							
					</div>
				</div>

	<?php
	} 
	if($_GET['type'] == 'draft')
	{
	?>
				<div class="summary">
					<div class="catalog"><img src="http://www.primebackstage.in/public/img/icons/Home Catalog.png" alt="Catalog" style="width: 26px; height: 26px; margin-right: 8px;" /> Catalog</div>
					<div class="summary_info">
						<div id="" class="title">Drafts<span class="count"><?php echo $this->INFO['draft'];?></span></div>							
					</div>
				</div>
	<?php
	} 
	?>
     <div>
  <div id="toast-msg" class="toast-msg">
  <span id="toast-icon" class="toast-icon">✅️</span>
  <span id="toast-text">Exporting...</span>
</div>
  
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  
<br>
<div class="filterContainer">
  <div class="catalog-filter-bar" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center; padding: 2px 12px; /*background: #fff;  border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.07);*/ margin-bottom: 0px;">

    <!-- 🔍 Search -->
    <div style="flex: 1 1 280px; position: relative;">
      <i class="material-icons" style="position: absolute; top: 50%; left: 12px; transform: translateY(-50%); color: #999;">search</i>
      <input
        class="form-control"
        name="search_text"
        id="search_text"
        type="text"
        value=""
        placeholder="Filter by release, artist, UPCs, etc."
        style="width: 70%; padding: 8px 12px 8px 38px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
      />
    </div>

    <!-- 🧾 Product Type -->
    <div class="filter-dropdown">
      <button class="filter-btn">Release Type ▾</button>
      <div class="filter-panel">
        <label><input type="checkbox" class="filter-type" value="digital"> Digital</label>
        <label><input type="checkbox" class="filter-type" value="physical"> Physical</label>
        <label><input type="checkbox" class="filter-type" value="video"> Video</label>
      </div>
    </div>

   <!-- 📦 Product Status -->
<div class="filter-dropdown">
  <button class="filter-btn">Release Status ▾</button>
  <div class="filter-panel">
    <label><input type="checkbox" class="filter-status" value="inreview"> 🟠 In Review</label>
    <label><input type="checkbox" class="filter-status" value="taken out"> 🔵 Taken Down</label>
    <label><input type="checkbox" class="filter-status" value="draft"> 🟡 Draft</label>
    <label><input type="checkbox" class="filter-status" value="rejected"> 🔴 Rejected</label>
    <label><input type="checkbox" class="filter-status" value="delivered"> 🟢 Delivered</label>
  </div>
</div>

<!-- 📅 Release Date -->
<div style="flex: 0 1 180px;">
  <input type="date" id="filter-release-date" class="form-control"
         style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
</div>
	  
    
	  <?php
		if($_GET['type'] == 'inprocess' )
		{
			?>
			<div class="col-sm-1 pull-right catalog-filter-export">
				<a href="javascript:;" class="btn btn-success btn-sm pull-right" id="btnApproveImport" >Approve Release</a>
		    </div>
			
			<?php
		}
		else
		{
			if($_GET['type'] == 'draft' || !isset($_GET['type']))
			{
				?>
			 
					 <div class="col-sm-3 pull-right" style="  border-radius: 8px;padding: 8px 0px; background: linear-gradient(to right, #7d2491, #0082ff); margin-right: 15px;align-items: center;justify-content: space-evenly;width: 90px;">
					  <div class="catalog-filter-export">
					  </div>
					<div class="dropdown list-unstyled page_action_collection pull-right filterContainer" style="position: relative;">
                   <div class="dropdown-toggle more_actions" data-toggle="dropdown">
                    <b style="color:#ffffff;">Options &nbsp;</b>
                    <i class="material-icons" style="color: #ffffff;">settings</i>
                     </div>

  <ul class="dropdown-menu orchard-style-dropdown">
    <li class="show_bulk_delete">
      <a href="javascript:;">
        <i class="material-icons">delete</i>
        <span class="toggle-text">Delete Bulk Tracks</span>
      </a>
      
    </li>
  <li>
    <a href="javascript:;">
      <i class="material-icons">edit</i>
      <span class="toggle-text">Submit Bulk Tracks</span>
       <span class="badge-beta">Beta</span>
    </a>
  </li>
  <li>
    <a href="javascript:;">
        <i class="material-icons">groups</i>
        <span class="toggle-text">Assign to Team</span>
      <span class="badge-beta">Beta</span>
      </a>
    </li>
 <li>
  <a href="javascript:;" class="btn-export-menu">
    <i class="material-icons">file_download</i>
    <span class="toggle-text">Export</span>
  </a>
</li>
                      
                      </ul>
						</div>
					  </div>
			<?php
			}
			else
			{
				?>
				
    <div class="col-sm-3 pull-right" style="  border-radius: 8px;padding: 8px 0px; background: linear-gradient(to right, #7d2491, #0082ff); margin-right: 15px;align-items: center;justify-content: space-evenly;width: 90px;">
					  <div class="catalog-filter-export">
					  </div>
					<div class="dropdown list-unstyled page_action_collection pull-right filterContainer" style="position: relative;">
                   <div class="dropdown-toggle more_actions" data-toggle="dropdown">
                    <b style="color:#ffffff;">Options &nbsp;</b>
                    <i class="material-icons" style="color: #ffffff;">settings</i>
                     </div>

  <ul class="dropdown-menu orchard-style-dropdown">
  <li>
    <a href="javascript:;">
      <i class="material-icons">edit</i>
      <span class="toggle-text">Metadata</span>
       <span class="badge-beta">Beta</span>
    </a>
  </li>
  
 <li>
  <a href="javascript:;" class="btn-export-menu">
    <i class="material-icons">file_download</i>
    <span class="toggle-text">Export </span>
  </a>
</li>
                      
                      </ul>
						</div>
					  </div>
				<?php
			}
		}			
		
	  ?>
     



<style>
/* ✅ Skeleton Animation Styles */
.skeleton-row {
   display: grid;
  grid-template-columns: 
  0%     /* ID */
  0%     /* Checkbox */
  1%     /* Status */
  1.5%     /* GAP */
  2.7%   /* Cover image */
  3.8%     /* GAP */
  17%    /* Title/Artist */
  1.5%     /* GAP */
  7%    /* Label */
  3.8%     /* GAP */
  5.8%    /* Date */
  5%     /* GAP */
  3%     /* Track count */
  8%     /* GAP */
  7%    /* ISRC */
  1%     /* GAP */
  12%    /* UPC */
  3%     /* GAP */
  5%    /* Delivry */
  7%     /* GAP */
  5%;    /* Actions */
  column-gap: 3px; /* 🔘 Adjust horizontal spacing between columns */
  align-items: center;
  background: #ffffff;
  border-radius: 5px;
  padding: 14px 12px;
  margin-bottom: 10px;
  box-shadow: 0 2px 6px rgb(0 0 0 / 14%);
  animation: pulse 1.6s ease-in-out infinite;
}
.skeleton-box {
  position: relative;
  background: #e0e0e0;
  border-radius: 6px;
  overflow: hidden;
}
.skeleton-box::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  height: 100%;
  width: 100%;
  background: linear-gradient(90deg, #f0f0f0 0%, #eaeaea 50%, #f0f0f0 100%);
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
#tableWrapper {
  min-height: 900px; /* Adjust based on table size */
  position: relative;
}
  #skeletonLoader {
  margin-top: 40px;
  position: relative; /* ⛔ REMOVE absolute */
  background: #fff;
  padding: 22px;
  z-index: 10;
}
  #tblMasterList thead th {
  position: sticky;
  top: 0;
  background: #fff;
  z-index: 2;
}
  table {
  table-layout: fixed !important;
  width: 100%;
}
  #catalogContent {
  opacity: 0;
  transition: opacity 0.2s ease;
}

#catalogContent.loaded {
  opacity: 1;
}
</style>


  <div id="tableWrapper" style="position: relative;">

    <div id="skeletonLoader" style="margin-top:12px; position: absolute; top: 0; left: 0; width: 100%; z-index: 10; background: #fff; padding: 22px;">

      <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="skeleton-row">
  <div class="skeleton-box"></div> <!-- ID -->
  <div class="skeleton-box"></div> <!-- Checkbox -->
  <div class="skeleton-box" style="height: 14px;"></div> <!-- Status -->
   <div></div> <!-- GAP 1 -->
  <div class="skeleton-box" style="height: 43px;"></div> <!-- Cover image -->
  <div></div> <!-- GAP 1 -->
  <div>
    <div class="skeleton-box" style="width: 90%; height: 14px; margin-bottom: 4px;"></div>
    <div class="skeleton-box" style="width: 70%; height: 12px;"></div>
  </div> <!-- Title/Artist -->
  <div></div> <!-- GAP 2 -->
  <div class="skeleton-box" style="height: 12px;"></div> <!-- Label -->
          <div></div> <!-- GAP 5 -->
  <div class="skeleton-box" style="height: 12px;"></div> <!-- Date -->
  <div></div> <!-- GAP 3 -->
  <div class="skeleton-box" style="height: 12px;"></div> <!-- Track count -->
          <div></div> <!-- GAP 3 -->
  <div class="skeleton-box" style="height: 12px;"></div> <!-- ISRC -->
          <div></div> <!-- GAP 3 -->
          <div>
    <div class="skeleton-box" style="width: 60%; height: 14px; margin-bottom: 4px;"></div>
    <div class="skeleton-box" style="width: 50%; height: 12px;"></div><!-- UPC -->
  </div>
  
  <div></div> <!-- GAP 4 -->
          <div>
    <div class="skeleton-box" style="width: 90px; height: 14px; margin-bottom: 4px;"></div>
    <div class="skeleton-box" style="width: 120px; height: 12px;"></div>
  </div> <!-- Delivry -->

           <div></div> <!-- GAP 4 -->
  <div>
    <div class="skeleton-box" style="width: 18px; height: 16px; margin-bottom: 4px;"></div>
    <div class="skeleton-box" style="width: 18px; height: 16px;"></div>
  </div> <!-- Actions -->
</div>
      <?php } ?>
    </div>

    <!-- ✅ Real Table -->
    <div id="catalogContent">
    <table id="tblMasterList" class="table dataTable no-footer table-hover" style="width: 100%;">
      <colgroup>
        <col width="3%">
        <col width="5%">
        <col width="6%">
        <col width="17%">
        <col width="10%">
        <col width="10%">
        <col width="10%">
        <col width="7%">
        <col width="12%">
        <col width="12%">
        <col width="5%">
      </colgroup>
      <thead>
        <tr>
          <th>ID</th>
          <th><input type="checkbox" class="form-check-input" id="select_all_chk"></th>
          <th>Status</th>
          <th></th>
          <th>Title / Artist</th>
          <th>Label</th>
          <th>Release Date / Time</th>
          <th># of Track</th>
          <th>ISRC</th>
          <th>UPC / Catalog Number</th>
          <th>Delivery Reports & Links</th>
          <th>
            <a href="javascript:;" class="btn btn-danger btn-sm pull-right btn-bull-delete" style="display:none;">
              Bulk Delete <span id="bulk_delete_cnt"></span>
            </a>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->RELEASES as $row) { ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><input type="checkbox" class="form-check-input row_chk" value="<?php echo $row['id']; ?>"></td>
            <td>
              <!-- status icon here -->
            </td>
            <td>
              <img src="<?php echo $row['cover_image']; ?>" width="40" style="border-radius:6px;">
            </td>
            <td>
              <strong><?php echo $row['title']; ?></strong><br>
              <span>By <?php echo $row['artist']; ?></span>
            </td>
            <td><?php echo $row['label']; ?></td>
            <td><?php echo $row['release_date']; ?></td>
            <td><?php echo $row['track_count']; ?></td>
            <td><?php echo $row['isrc']; ?></td>
            <td>UPC: <?php echo $row['upc']; ?><br>Cat#: <?php echo $row['catalog_number']; ?></td>
            <td><?php echo $row['delivery_status']; ?></td>
            <td>
              <a href="edit.php?id=<?php echo $row['id']; ?>"><i class="fa fa-pencil-alt"></i></a>
              <a href="delete.php?id=<?php echo $row['id']; ?>"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

  </div>
</div>

<!-- ✅ JS: Fade out skeleton and reveal table -->
<script>
$(document).ready(function () {
  setTimeout(function () {
    $('#skeletonLoader').fadeOut(350);
    $('#catalogContent').addClass('loaded'); // fade in real table
  }, 1800); // Adjust based on load time
});
  


function showToast(message = 'Processing...', icon = '✅️') {
  const toast = $('#toast-msg');
  $('#toast-icon').text(icon);
  $('#toast-text').text(message);

  toast.addClass('show');

  setTimeout(() => {
    toast.removeClass('show'); // slide out
  }, 3500);
}

$(document).on('click', '.btn-export, .btn-export-menu', function () {
  const search = $('#search_text').val() || '';
  const type = '<?php echo $_GET["type"]; ?>';

  showToast('Exporting file... Please wait', '✅️');

  setTimeout(() => {
    window.location.href = '/releases/export?type=' + encodeURIComponent(type) + '&search=' + encodeURIComponent(search);
  }, 1200);
});

</script>

  </table>
 </div>