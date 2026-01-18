<style>
.profile-container.admin-profile {
  max-width: 1500px;
  margin: 0 auto;
  padding: 40px;
  background: #f9fafb;
  font-family: 'Inter', sans-serif;
}

.summary-cards {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.summary-card {
  flex: 1;
  min-width: 220px;
  border-radius: 18px;
  padding: 24px 26px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: relative;
  transition: all 0.3s ease;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

.summary-card .card-icon {
  font-size: 30px;
  color: #5b46b3;
  margin-bottom: 12px;
}

.summary-card .card-title {
  font-size: 13px;
  text-transform: uppercase;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 6px;
}

.summary-card .card-value {
  font-size: 34px;
  font-weight: 800;
  color: #111827;
}

.summary-card.unpaid {
  background: linear-gradient(135deg, #fff5f5, #ffe8e8);
}
.summary-card.onhold {
  background: linear-gradient(135deg, #fffdea, #fff1c5);
}
.summary-card.paid {
  background: linear-gradient(135deg, #e6fcef, #b6f3d7);
}
.summary-card.rejected {
  background: linear-gradient(135deg, #fff0f0, #fca5a5);
}

.tabbed-wrapper {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  padding: 30px 30px 20px;
}

.nav-tabs {
  border: none;
  background: #f4f4f6;
  border-radius: 10px;
  padding: 6px;
  display: flex;
  gap: 10px;
  margin-bottom: 24px;
}

.nav-tabs li {
  list-style: none;
}
    #bs-main {
  background-color: #f7f7f8 !important;
}
.nav-tabs li a {
  background: #fff;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  color: #333;
  border: 1px solid #ddd;
  display: inline-block;
}

.nav-tabs li.active a {
  background: #5b46b3 !important;
  color: #fff !important;
  border: none !important;
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

.data-table table {
  width: 100%;
  font-size: 13px;
  border-collapse: collapse;
}

.data-table th, .data-table td {
  padding: 12px 14px;
  text-align: left;
}

.data-table thead th {
  background: #f4f4f6;
  text-transform: uppercase;
  font-weight: 700;
  color: #374151;
}

.data-table tbody td {
  background: #fff;
  border-bottom: 1px solid #eee;
  color: #444;
}
</style>

<div class="profile-container admin-profile">
<?php if($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1') { ?>

  <!-- ✅ TOP SUMMARY CARDS -->
  <div class="summary-cards">
    <div class="summary-card unpaid">
      <div class="card-icon"><i class="material-icons">hourglass_empty</i></div>
      <div class="card-title">Unpaid</div>
      <div class="card-value"><?= $this->INFO['request'] ?></div>
    </div>
    <div class="summary-card onhold">
      <div class="card-icon"><i class="material-icons">pause_circle_filled</i></div>
      <div class="card-title">On Hold</div>
      <div class="card-value"><?= $this->INFO['onhold'] ?></div>
    </div>
    <div class="summary-card paid">
      <div class="card-icon"><i class="material-icons">check_circle</i></div>
      <div class="card-title">Paid</div>
      <div class="card-value"><?= $this->INFO['paid'] ?></div>
    </div>
    <div class="summary-card rejected">
      <div class="card-icon"><i class="material-icons">cancel</i></div>
      <div class="card-title">Rejected</div>
      <div class="card-value"><?= $this->INFO['rejected'] ?></div>
    </div>
  </div>

  <!-- ✅ TABS + TABLES -->
  <div class="tabbed-wrapper">
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" class="active"><a href="#request" aria-controls="request" role="tab" data-toggle="tab">Withdraw Request</a></li>
      <li role="presentation"><a href="#onhold" aria-controls="onhold" role="tab" data-toggle="tab">On Hold</a></li>
      <li role="presentation"><a href="#paid" aria-controls="paid" role="tab" data-toggle="tab">Paid</a></li>
      <li role="presentation"><a href="#rejected" aria-controls="rejected" role="tab" data-toggle="tab">Rejected</a></li>
    </ul>

    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="request">
        <div class="data-table">
          <table id="tblMasterList">
            <thead>
              <tr>
                <th>ID</th>
                <th><?= $this->translate('Company Name') ?></th>
                <th><?= $this->translate('Label Name') ?></th>
                <th><?= $this->translate('Due Payments') ?></th>   <!-- NEW -->

                <th><?= $this->translate('Requested Month') ?></th>
                <th><?= $this->translate('Payment Amount') ?></th>
                <th><?= $this->translate('Action') ?></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane" id="onhold">
        <div class="data-table">
          <table id="tblMasterList2">
            <thead>
              <tr>
                <th>ID</th>
                <th><?= $this->translate('Company Name') ?></th>
                <th><?= $this->translate('Label Name') ?></th>
                <th><?= $this->translate('Due Payments') ?></th>
                <th><?= $this->translate('Requested Month') ?></th>
                <th><?= $this->translate('Payment Amount') ?></th>
                <th><?= $this->translate('Action') ?></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane" id="paid">
        <div class="data-table">
          <table id="tblMasterList3">
            <thead>
              <tr>
                <th>ID</th>
                <th><?= $this->translate('Company Name') ?></th>
                <th><?= $this->translate('Label Name') ?></th>
                <th><?= $this->translate('Due Payments') ?></th>
                <th><?= $this->translate('Requested Month') ?></th>
                <th><?= $this->translate('Payment Amount') ?></th>
                <th><?= $this->translate('Action') ?></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane" id="rejected">
        <div class="data-table">
          <table id="tblMasterList4">
            <thead>
              <tr>
                <th>ID</th>
                <th><?= $this->translate('Company Name') ?></th>
                <th><?= $this->translate('Label Name') ?></th>
                <th><?= $this->translate('Due Payments') ?></th>
                <th><?= $this->translate('Requested Month') ?></th>
                <th><?= $this->translate('Payment Amount') ?></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
</div>