<style>
.more_actions
{
	cursor:pointer;
	display:flex;
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
.dropdown-toggle.more_actions:hover {
    color: #1976E6;
}
</style>
<form>
  <input type="hidden" name="~formSubmitted" value="1">
  <div class="filterContainer">
    <div class="form-group">
      <div class="col-sm-4">
        <input class="form-control input-sm inputString inputBehavior-2 form-control" name="search_text" id="search_text" type="text" value="" style="">
        <span style="display:none" class="form-input-error help-inline control-label" rel="searchInfos-backstageMiscSearch-0"></span>
      </div>
      <div class="col-sm-2 no-padding filter-searches">
        <button class="btn btn-primary btn-sm inputBehavior-" id="btnSearch" type="button">
          <span class="glyphicon glyphicon-search"></span> Search </button>
        <span style="display:none" class="form-input-error help-inline control-label" ></span> &nbsp; 
      </div>
      <div class="col-sm-6 pull-right catalog-filter-import" style="  border: 2px solid #eee;border-radius: 5px;padding: 8px 0px;margin-right: 15px;display: flex;align-items: center;justify-content: space-evenly;width: 260px;">
			<a href="javascript:;" class="btn btn-primary  btn-sm pull-right" id="catelogBtn">Catelog Import</a>
			<div class=" dropdown list-unstyled page_action_collection pull-right" style="">
						<div class="dropdown-toggle more_actions"  data-toggle="dropdown">
							<b>More Actions</b> 
							<i class="material-icons">double_arrow</i>
						</div>
						<ul class="dropdown-menu">
							<li class="show_bulk_delete"><a href="javascript:; " ><i class="material-icons">delete</i> <span class="toggle-text">Delete Bulk release</span></a></li>
						</ul>
			</div>
			 
      </div>
    </div>
    <div class="clearfix"></div>
    <hr>
  </div>
</form>
<div id="catalogContent">
  
  <table id="tblMasterList" class="table dataTable no-footer" width="100%" style="display:none;">


<col width="3%">
<col width="8%">

<col width="22%">

<col width="12%">

<col width="12%">

<col width="10%">
<col width="10%">
<col width="15%">
<col width="5%">

                <thead>

                <tr>

                    <th>ID</th>

					 <th align="center"><input type="checkbox" class="form-check-input" id="select_all_chk"></th>
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

					<th><a href="javascript:;" class="btn btn-primary btn-sm pull-right btn-bull-delete" style="display:none;">Bulk Delete <span id="bulk_delete_cnt"></span></a></th>
					



                </tr>

                </thead>

                <tbody>

					

                </tbody>

  </table>
 </div>