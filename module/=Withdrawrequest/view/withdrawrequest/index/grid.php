<!-- Widget ID (each widget will need unique ID)-->

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
                     <div class="panel-heading clearfix ">
						  <div class="row">
							 <div class="col-md-12">
								<div class="status-container">
									 <div class="status-box active" id="box-withdraw-request">
										<h3>Withdraw Request</h3>
										<p class="count"><?php echo $this->INFO['request'];?></p>
									</div>
									<div class="status-box" id="box-on-hold">
										<h3>On Hold</h3>
										<p class="count"><?php echo $this->INFO['onhold'];?></p>
									</div>
									<div class="status-box" id="box-paid">
										<h3>Paid</h3>
										<p class="count"><?php echo $this->INFO['paid'];?></p>
									</div>
									<div class="status-box" id="box-rejected">
										<h3>Rejected</h3>
										<p class="count"><?php echo $this->INFO['rejected'];?></p>
									</div>
								</div>
							 </div>
						  </div>
					   </div>
  
				<ul class="nav nav-tabs hide" role="tablist" style="">
                    <li role="presentation" class="active gaEvent" >
                        <a href="#request" aria-controls="request" role="tab" data-toggle="tab">Withdraw Request</a>
                    </li>
                    <li role="presentation" class="gaEvent">
                        <a href="#onhold" aria-controls="onhold" role="tab" data-toggle="tab">On Hold</a>
                    </li>
					<li role="presentation" class="gaEvent">
                        <a href="#paid" aria-controls="paid" role="tab" data-toggle="tab">Paid</a>
                    </li>
					<li role="presentation" class="gaEvent">
                        <a href="#rejected" aria-controls="rejected" role="tab" data-toggle="tab">Rejected</a>
                    </li>
                </ul>
				<div class="tab-content" id="availableReport">
                    <div role="tabpanel" class="tab-pane active" id="request">
						<table id="tblMasterList" class="table dataTable no-footer" width="100%">

							<col width="25%">
							<col width="25%">
							<col width="15%">
							<col width="20%">
							<col width="15%">

							<thead>
								<tr>
									<th>ID</th>
									<th><?php echo $this->translate('Company Name'); ?></th>
									<th><?php echo $this->translate('Label Name'); ?></th>
									<th ><?php echo $this->translate('Month Year'); ?></th>
									<th><?php echo $this->translate('Payment Amount'); ?></th>
									<th> <?php echo $this->translate('Action'); ?> </th>
								</tr>
							</thead>

							<tbody>
							</tbody>
						</table>
					</div>
					<div role="tabpanel" class="tab-pane" id="onhold">
						<table id="tblMasterList2" class="table dataTable no-footer" width="100%">

							<col width="25%">
							<col width="25%">
							<col width="15%">
							<col width="20%">
							<col width="15%">

							<thead>
								<tr>
									<th>ID</th>
									<th><?php echo $this->translate('Company Name'); ?></th>
									<th><?php echo $this->translate('Label Name'); ?></th>
									<th ><?php echo $this->translate('Month Year'); ?></th>
									<th><?php echo $this->translate('Payment Amount'); ?></th>
									<th> <?php echo $this->translate('Action'); ?> </th>
								</tr>
							</thead>

							<tbody>
							</tbody>
						</table>
					</div>
					<div role="tabpanel" class="tab-pane" id="paid">
						<table id="tblMasterList3" class="table dataTable no-footer" width="100%">

							<col width="30%">
							<col width="30%">
							<col width="20%">
							<col width="20%">
							

							<thead>
								<tr>
									<th>ID</th>
									<th><?php echo $this->translate('Company Name'); ?></th>
									<th><?php echo $this->translate('Label Name'); ?></th>
									<th ><?php echo $this->translate('Month Year'); ?></th>
									<th><?php echo $this->translate('Payment Amount'); ?></th>
								</tr>
							</thead>

							<tbody>
							</tbody>
						</table>
					</div>
					<div role="tabpanel" class="tab-pane" id="rejected">
						<table id="tblMasterList4" class="table dataTable no-footer" width="100%">

							<col width="30%">
							<col width="30%">
							<col width="20%">
							<col width="20%">
							

							<thead>
								<tr>
									<th>ID</th>
									<th><?php echo $this->translate('Company Name'); ?></th>
									<th><?php echo $this->translate('Label Name'); ?></th>
									<th ><?php echo $this->translate('Month Year'); ?></th>
									<th><?php echo $this->translate('Payment Amount'); ?></th>
								</tr>
							</thead>

							<tbody>
							</tbody>
						</table>
					</div>
				</div>
				
				<?php }?>
				
</div>
</div>
						</div>
<!-- end widget -->

