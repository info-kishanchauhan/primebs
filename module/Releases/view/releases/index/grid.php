

<style>
  
  hr {
    margin-top: 0px;
    margin-bottom: 20px;
    border: 0;
    border-top: 1px solid #eee;
}
  /* Table ke header aur rows ke liye font control */
  .table th {
    font-size: 12px;
    color: #4d4d4d;
}
 .table td {
    font-size: 13px;   /* chhota size set karen (default 14-16 hota hai) */
    line-height: 1.4;  /* spacing control */
    font-family: 'Roboto', Arial, sans-serif; /* clean font */
    color: #333;       /* text ka color */
    vertical-align: middle; /* text alignment improve */
    padding: 6px 10px; /* thoda compact banane ke liye */
}

/* Agar sirf title/artist wale column ko chhota karna ho */
.table td.title-artist {
    font-size: 12px;
    font-weight: 500;
}


/* Tablet only */
@media (min-width: 768px) and (max-width: 1024px) {
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .table {
    min-width: 800px;   /* 👈 table ki fixed min-width de do */
    font-size: 14px;    /* thoda text chhota */
  }
}
@media (min-width: 768px) and (max-width: 1024px) {
  .table th,
  .table td {
    padding: 6px 8px;   /* default se chhota */
    font-size: 13px;    /* text thoda chhota */
  }
}

  .catalog-filter-bar {
  display: flex;
  align-items: center;
  flex-wrap: nowrap !important;  /* force ek hi row */
}

.catalog-filter-bar > * {
  flex: 0 1 auto;   /* sabhi children shrink kar sake */
  min-width: 0;     /* search input shrink kar sake */
}

#search_text {
  flex: 1 1 auto !important;  /* search expand + shrink dono kare */
  min-width: 120px;
  
}


.filter-btn,
.filter-dropdown {
  flex: 0 0 auto;   /* filters fixed size */
}

.col-sm-3.pull-right {
  flex: 0 0 auto !important;  /* Options button row me hi rahe */
    /* 90px wali width hata do */
  margin-left: auto;          /* push to right */
}

/* jQuery UI datepicker */
.ui-datepicker{ font-size:12px; }

/* Dan Grossman daterangepicker */
.daterangepicker{ font-size:12px; }
.daterangepicker .calendar-table{ width:240px; }

/* flatpickr */
.flatpickr-calendar{ font-size:12px; }


  
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 11px;
    line-height: 1.42857143;
    vertical-align: top;
    border-top: 1px solid #ddd;
}  
 
  
/* Delivery pills responsive styling */
.delivery-cell {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}

.delivery-pill {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3px 12px 3px 22px;  /* left padding zyada so icon absolute ke liye jagah mile */
  border-radius: 2px;
  font-size: 11px;
  font-weight: 800;
  line-height: 1;
  border: 1px solid transparent;
  position: relative;
  text-align: center;
  min-width: 0;              /* allow shrinking */
  flex: 1 1 auto;            /* flex-shrink allow + grow little bit */
  max-width: 100%;           /* prevent overflow */
  box-sizing: border-box;
  white-space: nowrap;       /* keep in single line */
  overflow: hidden;
  text-overflow: ellipsis;   /* add ... if too long */
}

/* Make pills responsive on smaller screens */
@media (max-width: 768px) {
  .delivery-pill {
    padding: 3px 8px 3px 20px;  /* left padding thoda kam */
    font-size: 10px;
  }
  
  .delivery-pill i {
    left: 4px;                 /* icon closer */
    font-size: 10px;
  }
}

/* Specific styles for different pill states */
.delivery-pill.is-submitted {
  background: #fdfdfd;
  border-color: #d8d8d8;
  color: #5b5b5b;
}

.delivery-pill.is-empty {
  background: #f4f5f7;
  border: 1px solid #d2d1d1;
  color: #5e5e5e;
}

/* Icon positioning */
.delivery-pill i {
  position: absolute;
  left: 6px;              /* thoda flexible rakha */
  font-size: 12px;
  line-height: 1;
  color: #3c3c3c;
  margin: 0;
}

/* Hover effects */
.delivery-pill.is-submitted:hover,
.delivery-pill.is-empty:hover {
  background: #e9ecef;
  border-color: #bfc5ca;
  color: #5b5b5b;
  cursor: pointer;
}

  .row-correction {
  background-color: #ffd9d9 !important; /* Believe style light red */
}
.row-correction:hover {
  background-color:   #ffd0d0!important;
}

  /* In Review wale rows */
.row-inreview {
  background-color: #fcf8e3 !important;  /* light blue */
}
.row-inreview:hover {
  background-color: #f7f0c7 !important;
}
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
table {
  overflow: visible !important; /* make sure nothing clips the popover */
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
	border-radius: 5px;
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
  border-radius: 5px;
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
  width: 140%;
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
  .row_chk_process {
  margin-left: 4px;
}
  
  .delivery-popover .delivery-link .link-text,
.d_status_wrapper .delivery_trigger_text,
.d_status_wrapper .delivery_report_icon,
.d_status_wrapper .d_status {
  font-family: Rubik, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  font-weight: 500;
}
  

</style>

 <?php
// Admin check (aapke setup ke mutabik)
$IS_ADMIN = (
    (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1') ||
    (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 0)
);
?>
  
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
						<?php if ($IS_ADMIN): ?>
      <div class="divider"></div>
      <div class="title">In Process
        <span class="count inprocess_cnt"><?= (int)$this->INFO['in_process']; ?></span>
      </div>
    <?php endif; ?>
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
  <div class="catalog-filter-bar" style="display: flex; gap: 75px; flex-wrap: wrap; align-items: center; padding: 2px 12px; /*background: #fff;  border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.07);*/ margin-bottom: 0px;">

    <!-- 🔍 Search -->
    <div style="flex: 1 1 280px; position: relative;">
      <i class="material-icons" style="position: absolute; top: 50%; left: 12px; transform: translateY(-50%); color: #999;">search</i>
      <input
        class="form-control"
        name="search_text"
        id="search_text"
        type="text"
        value=""
        placeholder="Filter by release, artist, UPC, catalog number, etc."
        style="width: 109%; padding: 8px 12px 8px 38px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
      />
    </div>

    <!-- 🧾 Product Type -->
    <div class="filter-dropdown">
      <button class="filter-btn">Release Type ▾</button>
      <div class="filter-panel">
        <label><input type="checkbox" class="filter-type" value="digital"> Music</label>
        <label><input type="checkbox" class="filter-type" value="physical"> Physical</label>
        <label><input type="checkbox" class="filter-type" value="video"> Video</label>
      </div>
    </div>

   <!-- 📦 Product Status -->
<div class="filter-dropdown">
  <button class="filter-btn">Release Status ▾</button>
  <div class="filter-panel">
    <label><input type="checkbox" class="filter-status" value="draft"> 🟡 Draft Saved</label>
    <label><input type="checkbox" class="filter-status" value="inreview"> 🟠 Under Review</label>
    <label><input type="checkbox" class="filter-status" value="rejected"> 🔴 Rejected</label>
    <label><input type="checkbox" class="filter-status" value="delivered"> 🟢 Delivered</label>
    <label><input type="checkbox" class="filter-status" value="taken out"> 🔵 Taken Down</label>
  </div>
</div>

<!-- 📅 Release Date -->


<div style="position: relative; display: inline-block;">
  <input type="text" id="filter-release-date" class="form-control"
         style="width: 220px; padding: 8px 30px 8px 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; background: #f9f9f9; color: #333;"
         placeholder="Release Date ▾">

  <!-- ❌ Clear icon -->
  <span id="clear-date" 
        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
               cursor: pointer; font-size: 16px; color: #aaa; display: none;">✕</span>
</div>
    
	  <?php
		if($_GET['type'] == 'inprocess' )
		{
			?>
    <div class="col-sm-3 pull-right" style="  border-radius: 4px;padding: 8px 0px; background: linear-gradient(to right, #792b97, #5b34c8); margin-right: 0px;align-items: center;justify-content: space-evenly;width: 90px;">
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
      <i class="material-icons">fact_check</i>
      <span class="toggle-text" id="btnApproveImport">Validate Release</span>
       <span class="badge-beta">Approval</span>
    </a>
  </li>
		</ul>
						</div>
					  </div>
			<?php
		}
		else
		{
			if($_GET['type'] == 'draft' || !isset($_GET['type']))
			{
				?>
			 
					 <div class="col-sm-3 pull-right" style="  border-radius: 4px;padding: 8px 0px; background: linear-gradient(to right, #792b97, #5b34c8); margin-right: 0px;align-items: center;justify-content: space-evenly;width: 90px;">
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
						  <a href="javascript:;" class="btn-export-menu" id="btnExport">
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
				
    <div class="col-sm-3 pull-right" style="  border-radius: 4px;padding: 8px 0px; background: linear-gradient(to right, #792b97, #5b34c8); margin-right: 0px;align-items: center;justify-content: space-evenly;width: 90px;">
					  <div class="catalog-filter-export">
					  </div>
					<div class="dropdown list-unstyled page_action_collection pull-right filterContainer" style="position: relative;">
                   <div class="dropdown-toggle more_actions" data-toggle="dropdown">
                    <b style="color:#ffffff;">Options &nbsp;</b>
                    <i class="material-icons" style="color: #ffffff;">settings</i>
                     </div>

						  <ul class="dropdown-menu orchard-style-dropdown">
							<?php if ($_SESSION["user_id"] == "0" || $_SESSION['STAFFUSER'] == '1') { ?>
						  <li>
							<a href="javascript:;" class="trigger-metadata">
							  <i class="material-icons">offline_pin</i>
							  <span class="toggle-text">Metadata</span>
							   <span class="badge-beta">Export</span>
							</a>
							 </li>
							 
							 <?php if ($_SESSION["user_id"] == "0" ) { ?>
						  <li>
							<a href="javascript:;" class="trigger-assign-team">
							  <i class="material-icons">groups</i>
							  <span class="toggle-text">Assign Team</span>
							   <span class="badge-beta">QC Team</span>
							</a>
						  </li>
						 <?php } ?>
						  <li>
							<a href="javascript:;" class="trigger-move-processing">
							  <i class="material-icons">groups</i>
							  <span class="toggle-text">Move To Process</span>
							  <span class="badge-beta">Team</span>
							</a>
						  </li>
						<?php } ?>
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
     
    </div>
    <div class="clearfix"></div>
    <hr>
  </div>


<style>
/* ✅ Skeleton Animation Styles */
.skeleton-row {
   display: grid;
  grid-template-columns: 
  0%     /* ID */
  0%     /* Checkbox */
  1%     /* Status */
  1.8%     /* GAP */
  2.7%   /* Cover image */
  1.5%     /* GAP */
  25%    /* Title/Artist */
  8%     /* GAP */
  8%    /* Label */
  9%     /* GAP */
  5.6%    /* Date */
  2%     /* GAP */
  3%     /* Track count */
  1%     /* GAP */
  1%    /* ISRC */
  3%     /* GAP */
  11%    /* UPC */
  0%     /* GAP */
  5%    /* Delivry */
  5%     /* GAP */
  7%;    /* Actions */
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
  
#tblMasterList thead {
  position: sticky;
  top: 0;
  z-index: 5;
  background: #fff;
  border-top: 1px solid #d1d5db;     /* 🔼 Top border */
  border-bottom: 1px solid #d1d5db;  /* 🔽 Bottom border */
}

#tblMasterList thead th {
  background: #ffffff94;
    padding: 6px 11px;
      border-bottom: 1px solid #ddd !important;
}
#tblMasterList {
  border-collapse: separate;
  border-spacing: 0;
  overflow: hidden;
  width: 100%;
  background: #fff;
}
</style>

<div class="table-sticky-wrap" id="mlWrap">
  <div id="tableWrapper" style="position: relative;">
    
    <div id="catalogContent">
      <div class="table-border-wrapper">
    <table id="tblMasterList" class="table dataTable no-footer table-hover" style="width: 100%;">
      <colgroup>
        <col width="2%">
        <col width="2%">
        <col width="2%">
        <col width="16%">
        <col width="8%">
        <col width="4%">
        <col width="4%">
        <col width="5%">
        <col width="5%">
        <col width="2%">
        
      </colgroup>
      <thead>
        <tr>
          <th>ID</th>
          <th align="center"><input type="checkbox" class="form-check-input" id="select_all_chk"></th>
          <th>Status</th>
          <th></th>
          <th>Title / Artist</th>
          <th>Label</th>
          <th>Release Date</th>
          <th># of Track</th>
          <!--<th>ISRC Number</th>-->
          <th>UPC / Catalog Number</th>
          <th>Delivery Reports & Links</th>
          
          <th>
            <a href="javascript:;" class="btn btn-danger btn-sm pull-right btn-bull-delete" style="display:none;">
  ◉ Delete<span id="bulk_delete_cnt"></span>
</a>
			<?php if ($_SESSION["user_id"] == "0" && $_GET['type'] == 'review') { ?>
			 
			<?php } ?>
      		<a href="javascript:;" class="btn btn-primary btn-sm pull-right btn-bulk-assign-team" style="display:none;">
  ◉ Assign<span id="bulk_assign_team_cnt"></span>
</a>	
					    
         </th>
</tr>
</thead>
<tbody>
<?php foreach ($this->RELEASES as $row): ?>


<tr>
    <td><?php echo $row['id']; ?></td>

    <td>
        <input type="checkbox" class="form-check-input row_chk_delete rls_chkbx" data-id="<?php echo $row['id']; ?>" style="display:none;">
        <input type="checkbox" class="form-check-input row_chk_process" data-id="<?php echo $row['id']; ?>" style="display:none;">
    </td>

    <td><!-- status icon here --></td>

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
   <!-- <td><?php echo $row['isrc']; ?></td>-->
    <td>
        UPC: <?php echo $row['upc']; ?><br>
        Cat#: <?php echo $row['catalog_number']; ?>
    </td>
    <td><?php echo $row['delivery_status']; ?></td>

    <td>
        <a href="edit.php?id=<?php echo $row['id']; ?>"><i class="fa fa-pencil-square"></i></a>
        <a href="delete.php?id=<?php echo $row['id']; ?>"><i class="fa fa-trash"></i></a>
        <!-- yahan dobara condition ki zaroorat nahi -->
     
     
    </td>
</tr>
<?php endforeach; ?>
</tbody>

    </table>
        </div>
      
<div id="moveToProcessingBtnWrapper" style="text-align:right; display:none; margin-top: 15px;">
  <button id="submitMoveToProcessing" class="btn btn-primary">✔ Move Selected to Processing</button>
  <button id="cancelMoveToProcessing" class="btn btn-secondary" style="margin-left:10px;">Cancel</button>
</div>

<div id="metaDataBtnWrapper" style="text-align:right; display:none; margin-top: 15px;">
  <button id="submitMetaData" class="btn btn-primary">⬇️ Export Metadata</button>
  <button id="cancelMetaData" class="btn btn-secondary" style="margin-left:10px;">Cancel</button>
</div>

<div id="assignTeamDataBtnWrapper" style="text-align:right; display:none; margin-top: 15px;">
  <button id="selectAssignedTeam" class="btn btn-primary">Assign Team</button>
  <button id="cancelAssignTeam" class="btn btn-secondary" style="margin-left:10px;">Cancel</button>
</div>

  </div>
</div>
    
<!-- =========================
     REPLACE AUDIO (FINAL)
========================= -->
<div class="modal fade" id="replaceAudioModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="replaceAudioForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Upload Corrected Audio</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                  style="border:0;background:transparent;font-size:22px;line-height:1;">×</button>
        </div>

        <div class="modal-body">

          <!-- Guide to uploading (rules) -->
          <div class="ra-guide">
            <div class="ra-guide-title">Guide to uploading</div>
            <div class="ra-guide-grid">
              <div>
                <div class="ra-guide-label">Audio specs</div>
                <div class="ra-guide-text">16-bit or 24-bit WAV • 44.1–192 kHz • Stereo only</div>
              </div>
              <div>
                <div class="ra-guide-label">Audio consistency</div>
                <div class="ra-guide-text">Don’t mix bit-depth / sample rate across tracks in the same release</div>
              </div>
              <div>
                <div class="ra-guide-label">Silence</div>
                <div class="ra-guide-text">No long silence at the beginning or end</div>
              </div>
              <div>
                <div class="ra-guide-label">Clipping</div>
                <div class="ra-guide-text">Avoid clipping / distortion; files must be mastered properly</div>
              </div>
              <div>
                <div class="ra-guide-label">File naming</div>
                <div class="ra-guide-text">Use clear & consistent names (avoid special chars / trailing spaces)</div>
              </div>
            </div>
            <a href="/faq/article/88" target="_blank" class="ra-guide-link">Learn more about supported formats →</a>
          </div>

          <!-- File picker -->
          <div class="form-group" style="margin-bottom:10px;">
            <label class="control-label" style="font-weight:600;">Select WAV file</label>
            <input type="file" name="audio_upload" accept=".wav" required class="form-control">
          </div>
          <input type="hidden" name="track_id" id="track_id">

          <!-- Progress UI -->
          <div id="uploadProgressWrap" style="display:none; margin:8px 0;">
            <div class="progress" style="height:8px;">
              <div id="uploadProgressBar" class="progress-bar" role="progressbar" style="width:0%;"></div>
            </div>
            <small id="uploadProgressText" class="text-muted">0%</small>
          </div>
          <small id="uploadMsg" class="text-muted" style="display:none;"></small>

          <hr style="margin:16px 0 10px;">

          <!-- Process table -->
          <table class="table table-bordered" id="tableContainer" style="empty-cells:hide;margin-top:6px;">
            <thead>
              <tr>
                <th class="text-center">File Name</th>
                <th class="text-center">File Name</th>
                <th class="text-center">Upload</th>
                <th class="text-center">Check</th>
              </tr>
            </thead>
            <tbody id="tableContainerBody" class="files">
              <!-- JS will inject the single selected file row -->
            </tbody>
          </table>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-sm">Upload</button>
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Look & feel (matches your screenshots) */
#replaceAudioModal .modal-content{border-radius:10px;font-family:'Inter',sans-serif;}
#replaceAudioModal .modal-title{font-weight:600;}
#replaceAudioModal .form-control{border-radius:8px;font-size:14px;}
#uploadProgressBar{background-color:#4f46e5!important;}
#uploadMsg{font-size:13px;margin-top:6px;display:block}

/* Guide */
.ra-guide{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:12px}
.ra-guide-title{font-weight:700;font-size:14px;margin-bottom:8px}
.ra-guide-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 20px}
@media (max-width:720px){.ra-guide-grid{grid-template-columns:1fr}}
.ra-guide-label{font-weight:600;font-size:13px}
.ra-guide-text{font-size:13px;color:#4b5563}
.ra-guide-link{font-size:12px;display:inline-block;margin-top:6px}

/* Table */
#tableContainer th,#tableContainer td{vertical-align:middle}
#tableContainer .progress{margin-bottom:0}
#tableContainer .progress-bar{font-size:13px}
#tableContainer .btn{padding:4px 8px;font-size:12px}
</style>

<script>
/* ====== CONFIG ====== */
var DATATABLE_SELECTOR = '#tblMasterList';
var UPLOAD_ENDPOINTS = ['/releases/uploadaudio','/settings/uploadaudio']; // fallback list
var raIsUploading = false;
var raCurrentXHR = null;           // <- active request handle
var raUploadToken = 0;             // <- cancel via token

/* ====== Toast ====== */
function showToast(message='Done', icon='✅️'){
  try{
    const t=$('#toast-msg'); $('#toast-icon').text(icon); $('#toast-text').text(message);
    t.addClass('show'); setTimeout(()=>t.removeClass('show'),3000);
  }catch(e){ alert(message); }
}
const toastOK =(m)=>showToast(m,'✅️');
const toastERR=(m)=>showToast(m,'⚠️');

/* ====== Utils ====== */
function isWav(f){ return !!f && /\.wav$/i.test(f.name||'') && (f.type===''||/wav/i.test(f.type||'')); }
function safeParse(x){ if(typeof x==='object') return x; var s=(x||'')+''; try{return JSON.parse(s);}catch(e){ var k=s.lastIndexOf('}'); if(k>-1){ try{return JSON.parse(s.slice(0,k+1));}catch(_){} } return {status:'ERR'}; } }
function resetUI(){
  raIsUploading=false; raCurrentXHR=null;
  $('#uploadMsg').removeClass('text-danger text-success').hide().html('');
  $('#replaceAudioForm button[type="submit"]').prop('disabled',false).text('Upload');
  $('#tableContainerBody').empty();
}
$('#replaceAudioModal').on('hidden.bs.modal', resetUI);

/* ====== Open modal ====== */
$(document).on('click','.js-user-replace-audio',function(){
  $('#track_id').val($(this).data('track-id')||'');
  $('#replaceAudioForm')[0].reset();
  resetUI();
  $('#replaceAudioModal').modal('show');
});

/* ====== Process-table row ====== */
function mountRow(name){
  $('#tableContainerBody').html(
   '<tr>'
   +  '<td class="text-left">'+name+'</td>'
   +  '<td class="text-center"><div class="progress" style="height:21px;">'
   +      '<div class="progress-bar progress-bar-success progress-bar-striped active" '
   +      'style="width:0%;line-height:21px;color:#fff;font-size:13px">0%</div>'
   +    '</div></td>'
   +  '<td class="text-center" id="audio_file_check"><span class="label label-default">Pending</span></td>'
   +  '<td class="text-center"><button type="button" class="btn btn-default js-del-selected"><b>'
   +    '<i class="glyphicon glyphicon-remove"></i> Delete file</b></button></td>'
   +'</tr>'
  );
  // Delete => abort + cleanup
  $('.js-del-selected').off('click').on('click', function(){
    // cancel token so any late callbacks are ignored
    raUploadToken++;
    // abort active ajax if any
    if(raCurrentXHR && raIsUploading && raCurrentXHR.abort){ raCurrentXHR.abort(); }
    raIsUploading=false; raCurrentXHR=null;
    $('#replaceAudioForm input[name="audio_upload"]').val('');
    $('#tableContainerBody').empty();
    $('#uploadMsg').removeClass('text-danger').addClass('text-success').text('Upload cancelled.').show();
    toastERR('Upload cancelled');
    $('#replaceAudioForm button[type="submit"]').prop('disabled',false).text('Upload');
  });
}

/* ====== Auto-submit on choose ====== */
$(document).off('change.autoRA')
.on('change.autoRA','#replaceAudioForm input[name="audio_upload"]',function(){
  const f=this.files && this.files[0];
  if(!f) return;
  if(!isWav(f)){
    $('#uploadMsg').addClass('text-danger').text('Only .wav files are allowed.').show();
    return;
  }
  mountRow(f.name);
  if(!raIsUploading){ $('#replaceAudioForm').trigger('submit'); }
});

/* ====== Core AJAX (returns jqXHR) ====== */
function ajaxUpload(ep, fd, hooks){
  return $.ajax({
    url: ep, type:'POST', data: fd, processData:false, contentType:false, dataType:'text', cache:false,
    headers:{'X-Requested-With':'XMLHttpRequest'},
    xhr:function(){
      var x=$.ajaxSettings.xhr();
      if(x.upload){
        x.upload.addEventListener('progress',function(e){
          if(e.lengthComputable){
            var p=Math.round((e.loaded/e.total)*100);
            hooks.onProgress && hooks.onProgress(p);
          }
        });
      }
      return x;
    }
  }).done(function(r){ hooks.onSuccess && hooks.onSuccess(r); })
    .fail(function(x){ hooks.onError && hooks.onError(x); });
}

/* ====== Submit (auto) ====== */
$('#replaceAudioForm').off('submit.autoRA').on('submit.autoRA', function(e){
  e.preventDefault();
  if(raIsUploading) return;

  const trackId=($('#track_id').val()||'').trim();
  const inp=this.querySelector('input[name="audio_upload"]');
  const file=(inp && inp.files && inp.files[0])||null;

  if(!trackId){ $('#uploadMsg').addClass('text-danger').html('Missing <code>track_id</code>.').show(); return; }
  if(!file){ $('#uploadMsg').addClass('text-danger').text('Please select a WAV file.').show(); return; }
  if(!isWav(file)){ $('#uploadMsg').addClass('text-danger').text('Only .wav files are allowed.').show(); return; }

  raIsUploading=true;
  $('#replaceAudioForm button[type="submit"]').prop('disabled',true).text('Uploading…');

  const $rowBar=$('#tableContainerBody .progress-bar').first();
  const $rowCheck=$('#tableContainerBody #audio_file_check').first();

  // guard token for late callbacks
  const myToken = ++raUploadToken;

  const fd=new FormData();
  fd.append('audio_upload', file);
  fd.append('track_id', trackId);

  let idx=0;
  (function tryNext(){
    if(idx>=UPLOAD_ENDPOINTS.length){
      if(myToken!==raUploadToken) return; // cancelled
      raIsUploading=false; raCurrentXHR=null;
      $('#replaceAudioForm button[type="submit"]').prop('disabled',false).text('Upload');
      $('#uploadMsg').addClass('text-danger').html('Upload failed on all endpoints.').show();
      if($rowBar.length){ $rowBar.removeClass('active').addClass('progress-bar-danger'); }
      if($rowCheck.length){ $rowCheck.html('<span class="label label-danger">Failed</span>'); }
      return;
    }
    const ep=UPLOAD_ENDPOINTS[idx++];

    raCurrentXHR = ajaxUpload(ep, fd, {
      onProgress: function(p){
        if(myToken!==raUploadToken) return; // cancelled
        if($rowBar.length){ $rowBar.css('width',p+'%').text(p+'%'); }
      },
      onSuccess: function(text){
        if(myToken!==raUploadToken) return; // cancelled
        const res=safeParse(text||'');
        if(res.status==='OK' || res.success===true){
          if($rowBar.length){ $rowBar.removeClass('active').css('width','100%').text('100%'); }
          if($rowCheck.length){ $rowCheck.html('<span class="label label-success">Processed</span>'); }
          $('#uploadMsg').removeClass('text-danger').addClass('text-success')
                         .html(res.format_info?('Validated & saved.<br>'+res.format_info):'Validated & saved.').show();

          setTimeout(function(){
            if(myToken!==raUploadToken) return;
            $('#replaceAudioModal').modal('hide');
            toastOK('Audio Correction Completed');
            try{ $(DATATABLE_SELECTOR).DataTable().ajax.reload(null,false); }catch(e){ location.reload(); }
          }, 600);
        }else{
          // endpoint responded with fail => stop
          raIsUploading=false; raCurrentXHR=null;
          $('#replaceAudioForm button[type="submit"]').prop('disabled',false).text('Upload');
          $('#uploadMsg').addClass('text-danger').html((res.msg||'Upload failed.')+(res.format_info?('<br>'+res.format_info):'')).show();
          if($rowBar.length){ $rowBar.removeClass('active').addClass('progress-bar-danger'); }
          if($rowCheck.length){ $rowCheck.html('<span class="label label-danger">Failed</span>'); }
        }
      },
      onError: function(xhr){
        if(myToken!==raUploadToken) return; // cancelled
        if(xhr.status===404 || xhr.status===0){ tryNext(); return; }
        raIsUploading=false; raCurrentXHR=null;
        const j=safeParse(xhr.responseText||''); const msg=j.msg||j.message||('Upload error ('+xhr.status+').');
        $('#replaceAudioForm button[type="submit"]').prop('disabled',false).text('Upload');
        $('#uploadMsg').addClass('text-danger').text(msg).show();
        if($rowBar.length){ $rowBar.removeClass('active').addClass('progress-bar-danger'); }
        if($rowCheck.length){ $rowCheck.html('<span class="label label-danger">Failed</span>'); }
      }
    });
  })();
});
</script>








<div class="modal fade" id="assigneTeamModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      
        <div class="modal-header">
          <h5 class="modal-title">Assign Team</h5>
        </div>
        <div class="modal-body">
		
          <div class="form-group form-group-sm">
			<div class="row">
				<div class="col-sm-3 control-label">
				   <label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Select Staff'); ?></strong><span style="color: red;"></span></label>
				</div>
				<div class="col-sm-7">
					<select type="select" id="assign_team_id" name="assign_team_id" class="select2" >
					</select>
					<small></small>
				</div>
				</div>
				
				<div class="row ">
						<div class="col-md-12 existing_assign_list">
						</div>
				</div>
			</div>
			
        </div>
        <div class="modal-footer">
		  <button type="button" id="confAssignTeam" class="btn btn-primary btn-sm hide">Yes</button>
          <button type="button" id="submitAssignTeam" class="btn btn-primary btn-sm">Assign</button>
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        </div>
      
    </div>
  </div>
</div>

<script>

$(document).on('click', '#selectAssignedTeam', function () {
  if (assignTeamIds.length === 0) {
    alert('Please select at least one release.');
    return;
  }
  $('#assigneTeamModal').modal('show');
  $('.existing_assign_list').html('');
  $('#assign_team_id').select2('val',0);
  $('#confAssignTeam').addClass('hide');
  $('#submitAssignTeam').removeClass('hide');
});



</script>



<script> 
let bulkMode = false;

$(document).on('click', '.show_bulk_delete', function () {
  const $btn = $(this).find('.toggle-text');
  const isCancel = $btn.text().trim().toLowerCase().includes('cancel');

  if (!bulkMode) {
    // Activate bulk delete mode
    $('.row_chk').show(); // all row checkboxes
    $('#select_all_chk').show();
    $('.btn-bull-delete').show();
    $btn.text('Cancel');
    bulkMode = true;
  } else {
    // Cancel bulk delete mode
    $('.row_chk').prop('checked', false).hide();
    $('#select_all_chk').prop('checked', false).hide();
    $('.btn-bull-delete').hide();
    $btn.text('Delete Bulk Tracks');
    bulkMode = false;
  }
});
/*var selectedIdsArray='';
  $.ajax({
  url: '/releases/move-to-processing',
  type: 'POST',
  data: { ids: selectedIdsArray },
  success: function(response) {
    const res = JSON.parse(response);
    if (res.status === 'OK') {
      alert(res.updated + ' release(s) moved to Processing!');
      location.reload();
    } else {
      alert('Move failed: ' + res.error);
    }
  }
});*/
$('#yourDataTableID').DataTable({
    // other config...
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": "<?= $this->url('releases', ['action' => 'list']) ?>?type=review", // or whatever route you're using
        "data": function (d) {
            d.filter_status = $('#statusFilterDropdown').val(); // dropdown/select element ID
        }
    }
});
  
  $('#statusFilterDropdown').on('change', function () {
    $('#yourDataTableID').DataTable().draw();
});
  
  
</script>
       
       
