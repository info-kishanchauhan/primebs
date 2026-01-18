
<style>
  /* === iPad side gutters (light left/right padding) === */
@media (min-width:768px) and (max-width:1024px){
  .ipad-gutters{
    padding-left: 24px !important;
    padding-right: 24px !important;
  }
}

/* (Optional) slightly larger gutters on small laptops up to 1200px */
@media (min-width:1025px) and (max-width:1200px){
  .ipad-gutters{
    padding-left: 28px !important;
    padding-right: 28px !important;
  }
}

  .dataTables_info {
    font-size: 18px;
    display: none;
    padding-bottom: 20px;
}
  #ui_daterange{
  background:#f4f6fc; border:1px solid #d1d5db; border-radius:12px;
  font:500 14px 'Poppins',system-ui; padding:10px 14px; width:260px;
}
#ui_daterange:focus{ outline:none; border-color:#3b82f6; box-shadow:0 2px 6px rgba(59,130,246,.3); background:#fff; }

  /* Base card with image */
/* Base card with image (forced) */
.pb-card--ghost {
  position: relative !important;
  border: 1px solid #eef2f7 !important;
  border-radius: 12px !important;
  overflow: hidden !important;
  background: #ffffff !important;
  background-image: url(https://img.freepik.com/premium-photo/defocused-image-illuminated-lights-night_1048944-26292677.jpg?w=900)!important;
  background-size: cover; !important;
  background-size: cover !important;
  background-repeat: no-repeat !important;
  background-position: center !important;
}

/* Color overlay on top (forced) */
.pb-card--ghost::before {
  content: "" !important;
  position: absolute !important;
  inset: 0 !important;
  border-radius: inherit !important;
 background: linear-gradient(135deg, rgb(0 0 0 / 35%) 0%, /* darker tint */ rgb(0 0 0 / 55%) 100%) !important;
  z-index: 0 !important;
}

/* Content above overlay (forced) */
.pb-card--ghost > * {
  position: relative !important;
  z-index: 1 !important;
}


 
  
 /* Professional pro-style date inputs */
.custom_filter input[type="text"] {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: #2c3e50;
    background-color: #f4f6fc; /* light blue-ish background */
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 10px 14px;
    width: 130px; /* fixed width */
    transition: all 0.3s ease;
}

/* Focus effect */
.custom_filter input[type="text"]:focus {
    outline: none;
    border-color: #3b82f6; /* primary blue */
    box-shadow: 0 2px 6px rgba(59,130,246,0.3);
    background-color: #ffffff;
}
a, .text-primary {
    color: #0e0e0e;
    font-size: 14;
}
/* Label style */
.custom_filter label strong {
    display: block;
    font-size: 13px;
    color: #1f2937;
    margin-bottom: 4px;
}

/* Align From/To inputs horizontally with gap */
.flex_div > div {
    display: flex;
    flex-direction: column;
}
.flex_div {
    gap: 12px;
    align-items: flex-start;
}
 
  
  
  
.stats-container {
    display: flex;
    gap: 20px;
    margin: 0px;
    letter-spacing: 0px;
    color: rgb(31, 31, 40);
    font-family: "Open Sans", Roboto, Arial, sans-serif;
    font-weight: 600;
    line-height: normal;
    font-size: 1.313rem;
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
    top: -5px;
    padding: 15px;
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
    font-size: 24px;
    font-weight: 800;
    margin: 0;
    line-height: 1.4;
    text-align: left;
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
h3 {
    margin-bottom: 5;
    font-family: "Open Sans", Roboto, Arial, sans-serif;
    font-weight: 600;
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
    padding-top: 20px;
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




<div class="row ipad-gutters" style="max-width:1280px;margin:auto;">

	<div class="row" style="margin-bottom:20px;">
	<div class="filterContainer">
		<div class="form-group">
		  <div class="col-sm-6">
			<label><h3>Insights <svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Track and analyze or music Performance, Revenue & Audience Metrics."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></h3></label><br>
			<span style="font-family: inter;
    font-size: 15px;
    font-weight: 400;
    margin-bottom: 10;
    /* color: #5a5a5a; */
    color: rgb(101, 108, 130);">
  Streaming Insights: Performance, Revenue & Audience Metrics
</span>

		  </div>
          
		  <!-- INLINE: Date range + Search + Import -->
<div class="row" style="margin-top:8px;">
  <div class="col-sm-6"></div>

  <div class="col-sm-6">
    <div class="pull-right" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <!-- Quick ranges -->
      <div class="switch-group" id="rangeSwitches">
        <div class="switch-button active" data-window="1M" onclick="setActive(this)">1M</div>
        <div class="switch-button" data-window="2M" onclick="setActive(this)">2M</div>
        <div class="switch-button" data-window="3M" onclick="setActive(this)">3M</div>
        <div class="switch-button" id="btnCustom" data-window="C" onclick="setActive(this)">Custom</div>
      </div>

      <!-- Inline Date Range (hidden until Custom) -->
      <input id="ui_daterange" type="text" class="form-control"
             placeholder="Select range"
             style="display:none; background:#f4f6fc; border:1px solid #d1d5db; border-radius:12px; padding:8px 12px; width:260px;"
             readonly>

      
    </div>
  </div>
</div>

<!-- Hidden fields backend ke liye (agar pehle se nahi hain to) -->
<input type="hidden" id="from_month" name="from_month">
<input type="hidden" id="to_month"   name="to_month">

	</div>
	</div>
	<?php if($_SESSION['user_id']=='0' || $_SESSION['STAFFUSER']=='1'){ ?>
  <div class="notice-bar" id="import-notice">
    <div class="notice-content">
      <span class="notice-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" role="img" aria-label="Notice">
          <path d="M12 2L15 8H9L12 2ZM11 10H13V14H11V10ZM11 16H13V18H11V16Z" fill="#2563eb"/>
        </svg>
      </span>

      <div class="notice-copy">
        <div class="notice-head">
          <span class="notice-text">Upload Financial Report & Analytics Data</span>
          <span class="notice-badge">Admin Only</span>
        </div>
        <div class="notice-sub">CSV files supported. Use this to update monthly statements, store splits, and Label splits.</div>
      </div>

      <button type="button" class="btn-import" id="importpopup">Import</button>
    </div>
  </div>
<?php } ?>
<!-- ===== Insights banner (Believe-style) — place this just ABOVE the charts table ===== -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet"/>

<style>
  /* Table title pill */
  .tbl-pill{
    display:inline-flex; align-items:center; gap:6px;
    margin-left:8px; padding:2px 8px;
    font:800 10.5px/1 Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
    letter-spacing:.3px; text-transform:uppercase;
    color:#0f172a; background:#ffffff; border:1px solid #e5e7eb; border-radius:999px;
    box-shadow:0 2px 6px rgba(16,24,40,.06);
    vertical-align:middle;
  }
  .tbl-pill--alt{ background:linear-gradient(90deg,#ffe4e6,#fff1f2); border-color:#fecdd3; color:#9f1239; }

  /* Scoped so it won't leak */
  .pb-insights-wrap{font-family:'Inter',system-ui,Arial,sans-serif;margin:10px 0 25px}
  .pb-insights-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}

  /* Card base */
  .pb-card{
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    background: linear-gradient(90deg, rgb(238 238 238 / 89%) 13.36%, rgb(237 237 237 / 35%) 93.83%);
    background-size:cover;
    background-image:url(https://backstage-apps-prd.cdn.believebackstage.fr/micro-apps/analytics/assets/images/short-form/card-insight-background.png);
  }

  /* Ghost (right) card */
  .pb-card.pb-card--ghost{
    background:linear-gradient(180deg,#f8fafc,#ffffff);
    border:1px solid #eef2f7;
  }

  .pb-card-inner{display:flex;gap:16px;align-items:stretch;padding:25px}
  .pb-badge{width:28px;height:28px;border-radius:999px;display:grid;place-items:center;background:#eef6ff;border:1px solid #e1edff;flex:none}
  .pb-badge svg{width:18px;height:18px;fill:#1e88e5}
  .pb-cover{width:86px;height:86px;border-radius:12px;overflow:hidden;flex:none;box-shadow:0 4px 10px rgba(2,6,23,.08)}
  .pb-cover img{width:100%;height:100%;object-fit:cover;display:block}
  .pb-meta{display:flex;flex-direction:column;gap:8px;min-width:0}
  .pb-kicker{font-size:12px;color:#64748b;font-weight:600;letter-spacing:.2px;text-transform:uppercase}
  .pb-headline{
    margin:0; letter-spacing:0; color:rgb(31,31,40);
    font-family:"Open Sans", Roboto, Arial, sans-serif; font-weight:700;
    line-height:normal; font-size:1.6rem;
  }
  .pb-sub{
    color:rgb(124,128,140);
    font-family:"Open Sans", Roboto, Arial, sans-serif;
    font-weight:600; line-height:24px; font-size:1.2rem;
    letter-spacing:0; text-decoration:none;
  }

  /* ====== CTA text-only (no background) ====== */
  .pb-card .pb-cta{
    display:inline-flex; align-items:center; gap:6px;
    font-weight:700; font-size:14px;
    background:transparent !important;
    border:0 !important; box-shadow:none !important;
    padding:0 !important; border-radius:0 !important;
    text-decoration:none; cursor:pointer;
    transition:opacity .15s ease;
  }
  /* Left/light card CTA = dark text */
  .pb-card:not(.pb-card--ghost) .pb-cta{ color:#646a7c !important; }
  /* Right/ghost card CTA = white text */
  .pb-card.pb-card--ghost .pb-cta{ color:#ffffff !important; }

  /* Arrow icon follows text color */
  .pb-card .pb-cta svg{ width:18px; height:18px; fill:currentColor !important; opacity:.9; }

  /* Hover affordance */
  .pb-card .pb-cta:hover{ opacity:.85; text-decoration:underline; }
  .pb-card .pb-cta:active{ opacity:1; text-decoration:none; }

  /* Right card body */
  .pb-resource{display:flex;flex-direction:column;gap:10px;padding:16px}
  .pb-resource .pb-row{display:flex;align-items:center;gap:10px}
  .pb-resource .pb-row svg{width:20px;height:20px;fill:#ffffff}
  .pb-resource-title{font-weight:700;color:#ffffff}
  .pb-resource-sub{font-size:15px;font-weight:800;color:#ffffff}

  /* Pills row spacer harmony (optional) */
  .plaB-platforms + .pb-insights-wrap{margin-top:14px}

  /* Responsive */
  @media (max-width:1024px){
    .pb-insights-grid{grid-template-columns:1fr}
  }
.notice-bar{
  background:#e8f0fe; border:1px solid #bfdbfe; border-radius:10px;
  padding:12px 18px; margin-bottom:20px;
  display:flex; align-items:center; justify-content:center;
  font-family:'Inter',system-ui,sans-serif;

}

.notice-content{ display:flex; align-items:center; gap:14px; width:100%; }
.notice-icon{ flex-shrink:0; display:inline-flex; }

.notice-copy{ display:flex; flex-direction:column; gap:4px; flex:1; min-width:0; }
.notice-head{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }

.notice-text{
  color:#0f172a; font-size:14px; font-weight:700; letter-spacing:.2px;
}
.notice-badge{
  font-size:10px; font-weight:800; letter-spacing:.3px; text-transform:uppercase;
  padding:2px 8px; border-radius:999px; color:#fff;
  background:linear-gradient(90deg,#f97316,#ef4444); /* orange → red */
  box-shadow:0 2px 6px rgba(239,68,68,.25);
}

.notice-sub{
  color:#1e3a8a; font-size:12.5px; font-weight:500; opacity:.9;
  white-space:normal;
}

.btn-import{
  cursor:pointer; background:linear-gradient(90deg,#2563eb,#3b82f6); color:#fff;
  border:none; border-radius:6px; padding:8px 16px; font-size:13px; font-weight:700;
  transition:transform .15s ease, box-shadow .2s ease, background-position .25s ease;
  flex-shrink:0;
}
.btn-import:hover{
  background:linear-gradient(90deg,#1d4ed8,#2563eb);
  box-shadow:0 4px 10px rgba(37,99,235,.28);
  transform: translateY(-1px);
}

</style>

<?php
  // ===== Server data =====
  $top = [
    'title'      => $topTrack['title']      ?? '—',
    'artist'     => $topTrack['artist']     ?? '',
    'creations'  => isset($topTrack['creations']) ? (float)$topTrack['creations'] : 0,
    'release_id' => $topTrack['release_id'] ?? 0,
    'cover'      => $topTrack['cover']      ?? '',
    'note'       => $topTrack['note']       ?? 'Get detailed insights on its performance',
    'link'       => $topTrack['link']       ?? $this->url('analytics', ['action'=>'index']),
  ];

  // agar koi bhi real data nahi hai (release id 0 ya creations 0) to block hide karna
  $hasTopData = ($top['release_id'] > 0) && ($top['creations'] > 0);

  // format creation count like 105.6K / 29.6K
  $fmtKM = function($n){
    if ($n >= 1000000) return rtrim(rtrim(number_format($n/1000000,1),'0'),'.').'M';
    if ($n >= 1000)    return rtrim(rtrim(number_format($n/1000,1),'0'),'.').'K';
    return (string)(int)$n;
  };
  $crea = $fmtKM($top['creations']);

  // cover mapping (local or remote)
  $cover = trim((string)$top['cover']);
  if ($cover && !preg_match('~^(?:https?:)?//|^data:~i', $cover)) {
      $cover = $this->basePath().'/public/uploads/'.ltrim($cover, '/');
  }
  if (!$cover) {
      $cover = $this->basePath().'/public/img/no-cover.svg';
  }

  // deep link with release id and 2-month delay from current month
  $from = $this->escapeHtmlAttr($_GET['from_month'] ?? '');
  $to   = $this->escapeHtmlAttr($_GET['to_month'] ?? '');

  if ($from === '' || $to === '') {
      $delayedMonthStart = date('Y-m-01', strtotime('-2 months'));
      $delayedMonthEnd   = date('Y-m-t',  strtotime('-2 months'));
      $from = $delayedMonthStart;
      $to   = $delayedMonthEnd;
  }

  $viewLink = $this->basePath().'/analytics/view?id='
             . $this->escapeHtmlAttr($top['release_id'])
             . '&from_month=' . $from
             . '&to_month='   . $to;
?>

<?php if ($hasTopData): ?>
<div class="pb-insights-wrap" role="region" aria-label="Highlights">
  <div class="pb-insights-grid">

    <!-- Left: main highlight card -->
    <div class="pb-card">
      <div class="pb-card-inner">

        <div class="pb-cover">
          <img src="<?php echo $this->escapeHtmlAttr($cover); ?>" alt="Cover" loading="lazy" referrerpolicy="no-referrer">
        </div>

        <div class="pb-meta">
          <div class="pb-headline">
            <?php echo $this->escapeHtml($top['title']); ?> was used in <?php echo $this->escapeHtml($crea); ?> short-form videos!
          </div>

          <div class="pb-sub">
            This track by <?php echo $this->escapeHtml($top['artist']); ?> is your most popular track.
          </div>

          <a class="pb-cta" href="<?php echo $viewLink; ?>">
            <span><?php echo $this->escapeHtml($top['note']); ?></span>
            <svg viewBox="0 0 24 24"><path d="M10 6 8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
          </a>
        </div>

      </div>
    </div>

    <!-- Right: resource/learn card -->
    <div class="pb-card pb-card--ghost">
      <div class="pb-resource">
        <div class="pb-row">
          <!-- book icon -->
          <svg viewBox="0 0 24 24"><path d="M21 5c-1.1-.3-2.3-.5-3.5-.5-2 0-4.1.4-5.5 1.5-1.4-1.1-3.5-1.5-5.5-1.5S2.5 4.9 1 6v14.6c0 .3.3.5.5.5l.2-.1C3.1 20.5 5.1 20 6.5 20c2 0 4.1.4 5.5 1.5 1.3-.9 3.8-1.5 5.5-1.5 1.7 0 3.3.3 4.7 1.1.1.1.2.1.3.1.3 0 .5-.3.5-.5V6c-.6-.5-1.3-.8-2-1zM21 18.5c-1.1-.3-2.3-.5-3.5-.5-1.7 0-4.2.6-5.5 1.5V8c1.3-.9 3.8-1.5 5.5-1.5 1.2 0 2.4.2 3.5.5V18.5z"/></svg>
          <div class="pb-resource-title">Resources</div>
        </div>
        <div class="pb-resource-sub">
          Breakout Track — identify when a track is going viral.
        </div>
        <a class="pb-cta" href="https://www.primebackstage.in/faq/category/25">
          Read more
          <svg viewBox="0 0 24 24"><path d="M10 6 8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
        </a>
      </div>
    </div>

  </div>
</div>
<?php endif; ?>

</div>


  
	<div class="">
  <div class="stats-container">
    <div class="stat-box col-md-4">
      <div class="MuiBox-root css-plypk7">
        <svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of videos using this track that were created on short-form platforms.">
          <path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path>
        </svg>
      </div>

      <div class="icon-and-value" style="display:flex;align-items:center;gap:25px;">
        <img src="public/img/social_group.PNG" alt="TikTok icon" class="icon" style="object-fit:contain;display:block;">

        <!-- Row: Creations | Views -->
        <div class="sec_flex" style="display:flex;align-items:center;gap:6px;">
          
          <!-- Creations -->
          <div class="stat-value" style="display:flex;flex-direction:column;align-items:flex-start;line-height:1;">
            <span id="tot_creation" style="font-weight:800;font-size:24px;line-height:1;">0</span>
            <span class="stat-label" style="    margin-top: 7px;
    font-size: 14px;
    font-weight: 510;
    letter-spacing: .3px;
    font-family: Open Sans, Roboto, Arial, sans-serif;
    text-transform: uppercase;
    color: #6b7280;
    line-height: 1.2;
">Creations</span>
          </div>

          <!-- Divider (aapka original pipe; chahe to rehne do) -->
          <div class="stat-pipe" style="font-size:26px;padding:0 7px;color:#aba2a2;font-weight:700;line-height:1;">|</div>

          <!-- Views -->
          <div class="stat-value" style="display:flex;flex-direction:column;align-items:flex-start;line-height:1;">
            <span id="tot_view" style="font-weight:800;font-size:24px;line-height:1;">0</span>
            <span class="stat-label" style="margin-top: 7px;
    font-size: 14px;
    font-weight: 510;
    letter-spacing: .3px;
    font-family: Open Sans, Roboto, Arial, sans-serif;
    text-transform: uppercase;
    color: #6b7280;
    line-height: 1.2;">Views</span>
          </div>

          <p id="tot_creation_comp" style="margin:0;display:none;"></p>
        </div>
      </div>
    </div>



			

			<div class="stat-box col-md-4">
				<div class="MuiBox-root css-plypk7"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of audio streams generated by your music."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></div>
				<div class="icon-and-value">
					<img src="public/img/streamsa.png" alt="Audio icon" class="icon">
					<div class="sec_flex">
						<p class="stat-value" id="tot_stream">0</p>
						<p class="stat-label">Audio streams</p>
						<p id="tot_stream_comp"></p>
						<p></p>
					</div>
					
				</div>
				
			</div>
          <div class="stat-box col-md-4">
				<div class="MuiBox-root css-plypk7"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-558cd0" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-toggle="tooltip" data-placement="top" data-original-title="Number of Revenue using this track that were created on short-form platforms."><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"></path></svg></div>
				<div class="icon-and-value">
					<img src="public/img/revenuea.png" alt="Revenue icon" class="icon" width="80" style="padding: 10px;border: 1px solid #eee;border-radius: 10px;">
					<div class="sec_flex">
						<p class="stat-value" id="tot_revenue">0</p>
						<p class="stat-label">Net Revenue</p>
						<p id="tot_revenue_comp"></p>
						<p></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	
  
<div id="catalogContent" style="margin-top:4px;">
  
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
					<th><?php echo $this->translate('RELEASE DATE'); ?></th>
					<th class="sort"><?php echo $this->translate('CREATIONS'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="UnfoldMoreIcon"><path d="M12 5.83 15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15z"></path></svg></span></th>
					<th class="sort"><?php echo $this->translate('VIEWS'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="UnfoldMoreIcon"><path d="M12 5.83 15.17 9l1.41-1.41L12 3 7.41 7.59 8.83 9zm0 12.34L8.83 15l-1.41 1.41L12 21l4.59-4.59L15.17 15z"></path></svg></span></th>
					<th><?php echo $this->translate('SALES MONTH'); ?></th>
					<th  class="sort"><?php echo $this->translate('NET REVENUE'); ?><span class="gicon"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium MuiTableSortLabel-icon MuiTableSortLabel-iconDirectionDesc css-jsf2o5 active" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="SouthIcon"><path d="m19 15-1.41-1.41L13 18.17V2h-2v16.17l-4.59-4.59L5 15l7 7z"></path></svg></span></th>
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
<script>
// Ensure jQuery loaded and no prior errors
(function($){
  // Delegated handler = works even if notice bar later add hota
  $(document).on('click', '#importpopup', function(e){
    e.preventDefault(); // just in case
    e.stopPropagation();

    // 1) Agar Bootstrap modal use kar rahe ho (aapke project me #importmodal mention hai)
    if (typeof $().modal === 'function' && $('#importmodal').length){
      $('#importmodal').modal('show');
      return;
    }

    // 2) Agar custom function hai
    if (typeof window.openImportModal === 'function'){
      window.openImportModal();
      return;
    }

    // 3) Fallback: console log (agar upar dono nahi mile)
    console.log('Import clicked: hook your modal/function here');
  });

  // Quick self-test: agar kahin aur JS error hai to yeh run nahi hoga
  console.debug('[Import Notice] handler bound');
})(jQuery);
</script>
