<style>
.summary
{
	display: flex;
	gap: 6px;
	padding:10px;
	border-radius:10px;
	background-color:#EFF0F1;
	
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
	width:90px;
	color:#424141
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
	padding: 19px;
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
	margin-top:15px;
}
hr
{
	border:none;
}
#search_text
{
	width: 350px;
    background-color: #f7f7f7;
    line-height: 29px;
    height: 40px;
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
}

.filterContainer .material-icons {
    margin-right: 8px;
    font-size: 18px;
}
.filterContainer .dropdown-menu a
{
	display:flex;
	font-weight:bold;
}
.filterContainer .dropdown-menu
{
	padding:12px 12px;
	border-radius:10px;
	    top: 40px;
    right: -25px;
	border: none;
	z-index:0;
}
.filterContainer .dropdown-menu::before {
    content: "";
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 0 10px 10px 10px;
    border-style: solid;
    border-color: transparent transparent white transparent;
}
.filterContainer .dropdown-menu>li>a:focus, .dropdown-menu>li>a:hover {
    color: #262626;
    text-decoration: none;
    background-color: #fff;
}
.search-wrapper {
  position: relative;
  display: inline-block;
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
</style>

 
  <div class="filterContainer">
    <div class="row">
      <div class="col-sm-3 search-wrapper">
	    <span class="search-icon material-icons">search</span>
        <input class="form-control input-sm inputString inputBehavior-2 form-control" name="search_text" id="search_text" type="text" value="" style="" placeholder="Search">
        <span style="display:none" class="form-input-error help-inline control-label" rel="searchInfos-backstageMiscSearch-0"></span>
      </div>
      <!--<div class="col-sm-1 no-padding filter-searches">
        <button class="btn btn-primary btn-sm inputBehavior-" id="btnSearch" type="button">
          <span class="glyphicon glyphicon-search"></span> Search </button>
        <span style="display:none" class="form-input-error help-inline control-label" ></span> &nbsp; 
      </div>-->
	  
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
			 
					 <div class="col-sm-3 pull-right" style="   border: 2px solid #eee;border-radius: 5px;padding: 8px 0px;margin-right: 15px;display: flex;align-items: center;justify-content: space-evenly;width: 220px;">
					  <div class="catalog-filter-export">
						<a href="javascript:;" class="btn btn-primary btn-sm  btn-export" id="btnExport" > Export </a>
					  </div>
					  
					 
					  <div class=" dropdown list-unstyled page_action_collection pull-right" style="">
						<div class="dropdown-toggle more_actions"  data-toggle="dropdown">
							<b>More Actions</b> 
						</div>
						<ul class="dropdown-menu">
							<li class="show_bulk_delete"><a href="javascript:; " ><i class="material-icons">delete</i> <span class="toggle-text">Delete Bulk release</span></a></li>
						</ul>
						</div>
					  </div>
			<?php
			}
			else
			{
				?>
				 <div class="col-sm-1 catalog-filter-export pull-right">
						<a href="javascript:;" class="btn btn-primary btn-sm  btn-export  pull-right" id="btnExport" >Export</a>
				</div>
				<?php
			}
		}			
		
	  ?>
     
    </div>
    <div class="clearfix"></div>
    <hr>
  </div>

<?php
	if(!isset($_GET['type']))
	{
	?>
				<div class="summary">
					<div class="catalog"><i class="material-icons">store</i> Catalog</div>
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
					<div class="catalog"><i class="material-icons">store</i> Catalog</div>
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
					<div class="catalog"><i class="material-icons">store</i> Catalog</div>
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
					<div class="catalog"><i class="material-icons">store</i> Catalog</div>
					<div class="summary_info">
						<div id="" class="title">Drafts<span class="count"><?php echo $this->INFO['draft'];?></span></div>							
					</div>
				</div>
	<?php
	} 
	?>
	
<div id="catalogContent">
  
  <table id="tblMasterList" class="table dataTable no-footer table-hover" width="100%" style="display:none;">

<col width="3%">

<col width="5%">

<col width="6%">

<col width="22%">

<col width="12%">

<col width="12%">

<col width="10%">
<col width="7%">
<col width="15%">
<col width="5%">

                <thead>

                <tr>

                    <th>ID</th>
					
                    <th align="center"><input type="checkbox" class="form-check-input" id="select_all_chk"></th>
					
					<th><?php echo $this->translate('Status'); ?></th>

					<th>

                    </i>  <?php echo $this->translate(''); ?></th>
					
					<th>

                    </i>  <?php echo $this->translate('Title / Artist'); ?></th>
					
					<th>

                    </i>  <?php echo $this->translate('Label'); ?></th>
					
					<th>

                    </i>  <?php echo $this->translate('Release date / Hour / Time zone'); ?></th>
					
					<th>

                    </i>  <?php echo $this->translate('# of track'); ?></th>
					
					
					
					<th>

                    </i>  <?php echo $this->translate('ISRC'); ?></th>
					
					<th>

                    </i>  <?php echo $this->translate('UPC / Catalog Number'); ?></th>


					
					<th><a href="javascript:;" class="btn btn-danger btn-sm pull-right btn-bull-delete" style="display:none;">Bulk Delete <span id="bulk_delete_cnt"></span></a></th>
						

                </tr>

                </thead>

                <tbody>

					

                </tbody>

  </table>
 </div>