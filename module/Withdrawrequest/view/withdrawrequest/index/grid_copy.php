<style>
/* Top Summary Wrapper */
.summary-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

/* Each Card */
.summary-card {
  background: linear-gradient(135deg, #f9f9fb, #f4f4f6);
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 22px;
  flex: 1;
  min-width: 220px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.03);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.3s ease;
  cursor: default;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.06);
}

/* Title */
.summary-card .card-title {
  font-size: 14px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

/* Count */
.summary-card .card-value {
  font-size: 32px;
  font-weight: 800;
  color: #111827;
}

/* Icon */
.summary-card .card-icon {
  font-size: 28px;
  color: #5b46b3;
  margin-bottom: 8px;
}

/* Special colors */
.unpaid {
    background: linear-gradient(135deg, #ffe5e5, #ffd1d1);
}
.onhold {
    background: linear-gradient(135deg, #fff4d6, #ffe6a7);
}
.paid {
    background: linear-gradient(135deg, #d2f8d2, #a8f0bc);
}
.rejected {
    background: linear-gradient(135deg, #ffd6d6, #fca5a5);
}

/* Tabs */
.nav.nav-tabs {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    background: #f4f4f6;
    margin-bottom: 20px;
    padding: 6px 8px;
    display: flex;
    gap: 10px;
}
.nav.nav-tabs li a {
    background: #ffffff;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 13px;
    border-radius: 8px;
    color: #333;
    border: 1px solid #ddd;
    transition: all 0.2s ease;
}
.nav.nav-tabs li.active a {
    background: #5b46b3;
    color: white;
    border: none;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

/* Table */
.data-table table {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    font-size: 13px;
}
.data-table th {
    background: #f9f9fb;
    font-weight: 700;
    color: #333;
    text-transform: uppercase;
}
.data-table td {
    color: #444;
}
.tab-pane
{
	position:relative;
}
</style>

<div class="data-table" id="widGrid" role="widget">

<?php if($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1') { ?>

    <!-- ✅ NEW PREMIUM SUMMARY CARDS -->
    <div class="summary-cards">
        <div class="summary-card unpaid">
            <div class="card-icon"><i class="material-icons">hourglass_empty</i></div>
            <div class="card-title">Unpaid</div>
            <div class="card-value"><?php echo $this->INFO['request']; ?></div>
        </div>
        <div class="summary-card onhold">
            <div class="card-icon"><i class="material-icons">pause_circle_filled</i></div>
            <div class="card-title">On Hold</div>
            <div class="card-value"><?php echo $this->INFO['onhold']; ?></div>
        </div>
        <div class="summary-card paid">
            <div class="card-icon"><i class="material-icons">check_circle</i></div>
            <div class="card-title">Paid</div>
            <div class="card-value"><?php echo $this->INFO['paid']; ?></div>
        </div>
        <div class="summary-card rejected">
            <div class="card-icon"><i class="material-icons">cancel</i></div>
            <div class="card-title">Rejected</div>
            <div class="card-value"><?php echo $this->INFO['rejected']; ?></div>
        </div>
    </div>

    <!-- ✅ TABS -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active gaEvent"><a href="#request" aria-controls="request" role="tab" data-toggle="tab">Withdraw Request</a></li>
        <li role="presentation" class="gaEvent"><a href="#onhold" aria-controls="onhold" role="tab" data-toggle="tab">On Hold</a></li>
        <li role="presentation" class="gaEvent"><a href="#paid" aria-controls="paid" role="tab" data-toggle="tab">Paid</a></li>
        <li role="presentation" class="gaEvent"><a href="#rejected" aria-controls="rejected" role="tab" data-toggle="tab">Rejected</a></li>
    </ul>

    <!-- ✅ TAB CONTENT -->
    <div class="tab-content" id="availableReport">
        <div role="tabpanel" class="tab-pane active" id="request">
            <table id="tblMasterList" class="table dataTable no-footer" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $this->translate('Company Name'); ?></th>
                        <th><?php echo $this->translate('Label Name'); ?></th>
                        <th><?php echo $this->translate('Month Year'); ?></th>
                        <th><?php echo $this->translate('Payment Amount'); ?></th>
                        <th><?php echo $this->translate('Action'); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div role="tabpanel" class="tab-pane" id="onhold">
            <table id="tblMasterList2" class="table dataTable no-footer" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $this->translate('Company Name'); ?></th>
                        <th><?php echo $this->translate('Label Name'); ?></th>
                        <th><?php echo $this->translate('Month Year'); ?></th>
                        <th><?php echo $this->translate('Payment Amount'); ?></th>
                        <th><?php echo $this->translate('Action'); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div role="tabpanel" class="tab-pane" id="paid">
            <table id="tblMasterList3" class="table dataTable no-footer" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $this->translate('Company Name'); ?></th>
                        <th><?php echo $this->translate('Label Name'); ?></th>
                        <th><?php echo $this->translate('Month Year'); ?></th>
                        <th><?php echo $this->translate('Payment Amount'); ?></th>
                        <th><?php echo $this->translate('Action'); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div role="tabpanel" class="tab-pane" id="rejected">
            <table id="tblMasterList4" class="table dataTable no-footer" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $this->translate('Company Name'); ?></th>
                        <th><?php echo $this->translate('Label Name'); ?></th>
                        <th><?php echo $this->translate('Month Year'); ?></th>
                        <th><?php echo $this->translate('Payment Amount'); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

<?php } ?>
</div>
