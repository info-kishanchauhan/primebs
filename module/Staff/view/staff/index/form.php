
<style>
  .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    line-height: 2;!important;
}
  .h4, h4 {
    font-size: 18px;
    font-family: Rubik, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
    font-weight: 500;
    color: #4a4a4a;
}
  
 </style>
<div id="defaultContainer" style="padding: 20px;">
<div class="jarviswidget hide panel panel-default panel-hovered panel-stacked mb30" id="widForm"
     data-widget-colorbutton="false"
     data-widget-editbutton="false"
     data-widget-togglebutton="false"
     data-widget-deletebutton="false"
     data-widget-fullscreenbutton="false"
     data-widget-custombutton="false"
     role="widget" style=""
     xmlns="http://www.w3.org/1999/html">
     
     
  
    <!-- widget div-->
    <div class="panel-body">
      
        <div class="widget-body">

			<div class="jarviswidget-editbox">
			<div class="row">
			 <div class="buttons flex right">
				<a id="btnBack2" href="javascript:;" class="bg-white border uppercase text-sm border-blue-500 text-blue-500 py-2 mr-2 px-10 rounded-full" style="">Back</a>
					
			</div></div>
			</div>
            <div id="myTabContent">
                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="general">
                    <form id="frmForm" class="smart-form" novalidate="novalidate" enctype="multipart/form-data">
							<input type="hidden" name="photohidden" id="photohidden">
									<div class="row">
										<div class="col-md-3">
											<fieldset style="padding-top: 5px">
												<legend>👤 LOGIN INFORMATION</legend>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Login:'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="login_name" name="login_name" class="form-control " >
																<small></small>
															</label>
														</section>
													
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Email'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="email" name="email" class="form-control " >
																<small></small>
															</label>
														</section>
													
												</div>
												
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Password:'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="password" id="password" name="password" class="form-control " >
																<small></small>
															</label>
														</section>
													
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Confirm Password:'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="password" id="confm_password" name="confm_password" class="form-control " >
																<small></small>
															</label>
														</section>
													
												</div>
												
											</fieldset>
											<fieldset style="margin-top: 15px;">
												<legend>ℹ️ BASIC INFORMATION</legend>
												
												<div class="row">
													<section class="form-group form-group-sm clearfix">
														<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Photo'); ?></strong></label>
														<label class="col-md-12"> 
															<input type="file" name="photo_file" id="photo_file" class="form-control ">
															<img src="" width="150" id="photo_file_img" style="padding-top:10px;display:none;">
															<label class="progress hide" style="height:21px;width: 100%;margin-top: 5px;" id="customfileupload">

																<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%;line-height:21px;color:#FFFFFF;font-size:14px">File uploading
																</div>

															</label>
														</label>
													</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('First Name'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="first_name" name="first_name" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Last Name'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="last_name" name="last_name" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Nick Name'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="nick_name" name="nick_name" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Mobile Number'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="mobile_number" name="mobile_number" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Address'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="address" name="address" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Satate'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="state" name="state" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('City'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="city" name="city" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Pincode'); ?></strong></label>
															<label class="col-md-12"> 
																<input type="text" id="pincode" name="pincode" class="form-control " >
																<small></small>
															</label>
														</section>
												</div>
												<div class="row">
														<section class="form-group form-group-sm clearfix">
															<label class="col-md-12 control-label"><strong class="txt-color-blue"><?php echo $this->translate('Country / Region'); ?></strong></label>
															<label class="col-md-12"> 
												 <select type="select" class="select2" name="isoCountry" id="isoCountry" tabindex="-1" title="Country / Region" >
													<option value="0">Please select...</option>
													<option value="AF">Afghanistan</option>
													<option value="AX">Aland Islands</option>
													<option value="AL">Albania</option>
													<option value="DZ">Algeria</option>
													<option value="AS">American Samoa</option>
													<option value="AD">Andorra</option>
													<option value="AO">Angola</option>
													<option value="AI">Anguilla</option>
													<option value="AQ">Antarctica</option>
													<option value="AG">Antigua And Barbuda</option>
													<option value="AR">Argentina</option>
													<option value="AM">Armenia</option>
													<option value="AW">Aruba</option>
													<option value="AU">Australia</option>
													<option value="AT">Austria</option>
													<option value="AZ">Azerbaijan</option>
													<option value="BS">Bahamas</option>
													<option value="BH">Bahrain</option>
													<option value="BD">Bangladesh</option>
													<option value="BB">Barbados</option>
													<option value="BY">Belarus</option>
													<option value="BE">Belgium</option>
													<option value="BZ">Belize</option>
													<option value="BJ">Benin</option>
													<option value="BM">Bermuda</option>
													<option value="BT">Bhutan</option>
													<option value="BO">Bolivia</option>
													<option value="BA">Bosnia And Herzegovina</option>
													<option value="BW">Botswana</option>
													<option value="BV">Bouvet Island</option>
													<option value="BR">Brazil</option>
													<option value="IO">British Indian Ocean</option>
													<option value="BN">Brunei Darussalam</option>
													<option value="BG">Bulgaria</option>
													<option value="BF">Burkina Faso</option>
													<option value="BI">Burundi</option>
													<option value="KH">Cambodia</option>
													<option value="CM">Cameroon</option>
													<option value="CA">Canada</option>
													<option value="CV">Cape Verde</option>
													<option value="KY">Cayman Islands</option>
													<option value="CF">Central African Republic</option>
													<option value="TD">Chad</option>
													<option value="CL">Chile</option>
													<option value="CN">China Mainland</option>
													<option value="CX">Christmas Island</option>
													<option value="CC">Cocos (keeling) Islands</option>
													<option value="CO">Colombia</option>
													<option value="KM">Comoros</option>
													<option value="CK">Cook Islands</option>
													<option value="CR">Costa Rica</option>
													<option value="CI">Cote Ivoire</option>
													<option value="HR">Croatia</option>
													<option value="CU">Cuba</option>
													<option value="CY">Cyprus</option>
													<option value="CZ">Czech Republic</option>
													<option value="DK">Denmark</option>
													<option value="DJ">Djibouti</option>
													<option value="DM">Dominica</option>
													<option value="DO">Dominican Republic</option>
													<option value="EC">Ecuador</option>
													<option value="EG">Egypt</option>
													<option value="SV">El Salvador</option>
													<option value="GQ">Equatorial Guinea</option>
													<option value="ER">Eritrea</option>
													<option value="EE">Estonia</option>
													<option value="SZ">Eswatini</option>
													<option value="ET">Ethiopia</option>
													<option value="FO">Faeroe Islands</option>
													<option value="FK">Falkland Islands</option>
													<option value="FJ">Fiji</option>
													<option value="FI">Finland</option>
													<option value="FR">France</option>
													<option value="GF">French Guiana</option>
													<option value="PF">French Polynesia</option>
													<option value="TF">French Southern Territories</option>
													<option value="GA">Gabon</option>
													<option value="GM">Gambia, The</option>
													<option value="GE">Georgia</option>
													<option value="DE">Germany</option>
													<option value="GH">Ghana</option>
													<option value="GI">Gibraltar</option>
													<option value="GR">Greece</option>
													<option value="GL">Greenland</option>
													<option value="GD">Grenada</option>
													<option value="GP">Guadeloupe</option>
													<option value="GU">Guam</option>
													<option value="GT">Guatemala</option>
													<option value="GN">Guinea</option>
													<option value="GW">Guinea-bissau</option>
													<option value="GY">Guyana</option>
													<option value="HT">Haiti</option>
													<option value="HM">Heard Island And Mcdonald Islands</option>
													<option value="VA">Holy See (vatican City State)</option>
													<option value="HN">Honduras</option>
													<option value="HK">Hong Kong</option>
													<option value="HU">Hungary</option>
													<option value="IS">Iceland</option>
													<option value="IN">India</option>
													<option value="ID">Indonesia</option>
													<option value="IR">Iran</option>
													<option value="IQ">Iraq</option>
													<option value="IE">Ireland</option>
													<option value="IL">Israel</option>
													<option value="IT">Italy</option>
													<option value="JM">Jamaica</option>
													<option value="JP">Japan</option>
													<option value="JO">Jordan</option>
													<option value="KZ">Kazakhstan</option>
													<option value="KE">Kenya</option>
													<option value="KI">Kiribati</option>
													<option value="KW">Kuwait</option>
													<option value="KG">Kyrgyzstan</option>
													<option value="LA">Lao People's Democratic Republic</option>
													<option value="LV">Latvia</option>
													<option value="LB">Lebanon</option>
													<option value="LS">Lesotho</option>
													<option value="LR">Liberia</option>
													<option value="LY">Libya</option>
													<option value="LI">Liechtenstein</option>
													<option value="LT">Lithuania</option>
													<option value="LU">Luxembourg</option>
													<option value="MO">Macao</option>
													<option value="MK">Macedonia</option>
													<option value="MG">Madagascar</option>
													<option value="MW">Malawi</option>
													<option value="MY">Malaysia</option>
													<option value="MV">Maldives</option>
													<option value="ML">Mali</option>
													<option value="MT">Malta</option>
													<option value="MH">Marshall Islands</option>
													<option value="MQ">Martinique</option>
													<option value="MR">Mauritania</option>
													<option value="MU">Mauritius</option>
													<option value="YT">Mayotte</option>
													<option value="MX">Mexico</option>
													<option value="FM">Micronesia, Federated States Of</option>
													<option value="MD">Moldova</option>
													<option value="MC">Monaco</option>
													<option value="MN">Mongolia</option>
													<option value="MS">Montserrat</option>
													<option value="MA">Morocco</option>
													<option value="MZ">Mozambique</option>
													<option value="MM">Myanmar</option>
													<option value="NA">Namibia</option>
													<option value="NR">Nauru</option>
													<option value="NP">Nepal</option>
													<option value="NL">Netherlands</option>
													<option value="AN">Netherlands Antilles</option>
													<option value="NC">New Caledonia</option>
													<option value="NZ">New Zealand</option>
													<option value="NI">Nicaragua</option>
													<option value="NE">Niger</option>
													<option value="NG">Nigeria</option>
													<option value="NU">Niue</option>
													<option value="NF">Norfolk Island</option>
													<option value="KP">North Korea</option>
													<option value="MP">Northern Mariana Islands</option>
													<option value="NO">Norway</option>
													<option value="OM">Oman</option>
													<option value="PK">Pakistan</option>
													<option value="PW">Palau</option>
													<option value="PS">Palestinian Territories</option>
													<option value="PA">Panama</option>
													<option value="PG">Papua New Guinea</option>
													<option value="PY">Paraguay</option>
													<option value="PE">Peru</option>
													<option value="PH">Philippines</option>
													<option value="PN">Pitcairn</option>
													<option value="PL">Poland</option>
													<option value="PT">Portugal</option>
													<option value="PR">Puerto Rico</option>
													<option value="QA">Qatar</option>
													<option value="CG">Republic Of Congo</option>
													<option value="RE">Reunion</option>
													<option value="RO">Romania</option>
													<option value="RU">Russian Federation</option>
													<option value="RW">Rwanda</option>
													<option value="SH">Saint Helena</option>
													<option value="KN">Saint Kitts And Nevis</option>
													<option value="LC">Saint Lucia</option>
													<option value="PM">Saint Pierre And Miquelon</option>
													<option value="VC">Saint Vincent And The Grenadines</option>
													<option value="WS">Samoa</option>
													<option value="SM">San Marino</option>
													<option value="ST">Sao Tome And Principe</option>
													<option value="SA">Saudi Arabia</option>
													<option value="SN">Senegal</option>
													<option value="CS">Serbia And Montenegro</option>
													<option value="SC">Seychelles</option>
													<option value="SL">Sierra Leone</option>
													<option value="SG">Singapore</option>
													<option value="SK">Slovakia</option>
													<option value="SI">Slovenia</option>
													<option value="SB">Solomon Islands</option>
													<option value="SO">Somalia</option>
													<option value="ZA">South Africa</option>
													<option value="GS">South Georgia And The South Sandwich Islands</option>
													<option value="KR">South Korea</option>
													<option value="ES">Spain</option>
													<option value="LK">Sri Lanka</option>
													<option value="SD">Sudan</option>
													<option value="SR">Suriname</option>
													<option value="SJ">Svalbard And Jan Mayen</option>
													<option value="SE">Sweden</option>
													<option value="CH">Switzerland</option>
													<option value="SY">Syrian Arab Republic</option>
													<option value="TW">Taiwan</option>
													<option value="TJ">Tajikistan</option>
													<option value="TZ">Tanzania</option>
													<option value="TH">Thailand</option>
													<option value="CD">The Democratic Republic Of The Congo</option>
													<option value="TL">Timor-leste</option>
													<option value="TG">Togo</option>
													<option value="TK">Tokelau</option>
													<option value="TO">Tonga</option>
													<option value="TT">Trinidad And Tobago</option>
													<option value="TN">Tunisia</option>
													<option value="TR">Turkey</option>
													<option value="TM">Turkmenistan</option>
													<option value="TC">Turks And Caicos Islands</option>
													<option value="TV">Tuvalu</option>
													<option value="UG">Uganda</option>
													<option value="UA">Ukraine</option>
													<option value="AE">United Arab Emirates</option>
													<option value="GB">United Kingdom</option>
													<option value="US">United States</option>
													<option value="UM">United States Minor Outlying Islands</option>
													<option value="UY">Uruguay</option>
													<option value="UZ">Uzbekistan</option>
													<option value="VU">Vanuatu</option>
													<option value="VE">Venezuela</option>
													<option value="VN">Viet Nam</option>
													<option value="VG">Virgin Islands, British</option>
													<option value="VI">Virgin Islands, U.s.</option>
													<option value="WF">Wallis And Futuna</option>
													<option value="EH">Western Sahara</option>
													<option value="YE">Yemen</option>
													<option value="ZM">Zambia</option>
													<option value="ZW">Zimbabwe</option>
												</select>
															<small></small>	
															</label>
														</section>
												</div>
											</fieldset>
										</div>
										<div class="col-md-3">
											<fieldset style="padding-top: 5px;padding-bottom:20px;">
												<legend>🔐 PERMISSIONS & ROLES</legend>
                                              <div class="info-box">
<div style="background: #eef7ff; padding: 18px 22px; border-left: 5px solid #007bff; border-radius: 10px; font-size: 12px; color: #2c3e50; line-height: 1.6; ">
  
  • <strong>Roles :</strong> Toggle visibility for Catalog, Insights, Rights Manager, Accounting, and more.<br>

</div>
                                              
                                              
                                              <!-- Label Manager Email Field -->
												<h4>Permission</h4>
    												<div class="row">
													   
														
															<label class="col-md-12"> 
																	<label class="radio-inline">
																	<input type="radio" class="" name="permission_type" id="permission_type1" value="Full Access" tabindex="4" checked> Full Access</label>
																	<label class="radio-inline">
																	<input type="radio" class="" name="permission_type" id="permission_type2" value="Controlled Access" tabindex="4">Controlled Access</label>
																	<small></small>
															</label>
														
													</div>												
   



                                                <br>
												<div class="controlled_access_div hide">
													<h4>Daily Auto Assign Limit</h4>
													<input type="number" min="0" id="release_limit" name="release_limit" class="form-control">
													<br>
												</div>
												
												
												<h4>Backstage</h4>
												<h5><a href="javascript:;" id="c_a_all" >Check/Uncheck All</a></h5>
												<h3 class="section-title-with-tooltip">
													  Release Builder
													  <span data-toggle="tooltip" title="Used to create new music releases" class="tooltip-icon">
														<!-- ✅ SVG info icon -->
														<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" viewBox="0 0 24 24">
														  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
															10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
														</svg>
													  </span>
												</h3>

											    <div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="new_release" value="New Release" > Create Release
															</label>
														</div>
												</div><br>
												
												<h3 class="section-title-with-tooltip">
													  Catalog
													  <span data-toggle="tooltip" title="All releases, drafts and under review releases." class="tooltip-icon">
														<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" viewBox="0 0 24 24">
														  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
															10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
														</svg>
													  </span>
												</h3>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="all_releases" value="All Releases" > All Releases
															</label>
														</div>
												</div>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="drafts" value="Drafts" > Drafts Saved
															</label>
														</div>
												</div>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="inreview" value="Inreview" > Under-Review
															</label>
														</div>
												</div>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="inprocess" value="Inprocess" > In-Process
															</label>
														</div>
												</div>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="catalogimport" value="Catalog Import" > Catalog Import
															</label>
														</div>
												</div>
												<br>
												<h4>Insights</h4>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="analytics" value="Analytics" > Insights
															</label>
														</div>
												</div><br>
												<h4>Accounting</h4>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="accounting" value="Accounting" > Accounting
															</label>
														</div>
												</div><br>
												<h4>Banking</h4>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="banking" value="Banking" > Banking
															</label>
														</div>
												</div><br>
												<h4>Payment Request</h4>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="paymentrequest" value="Payment Request" > Payment Request
															</label>
														</div>
												</div><br>
												<h4>Labels</h4>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="label" value="Labels" > Labels
															</label>
														</div>
												</div><br>
												<h4>News</h4>
												<div class="col-md-12">
														<div class="checkbox">
															<label for="easyEntryEditForm-isCompil-0">
																	<input type="checkbox" class="" name="user_access[]" id="news" value="News" > News
															</label>
														</div>
												</div><br>
												
												<h4>Settings</h4>
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="dacsettings" value="DAC Settings" > DAC Settings
																</label>
															</div>
													</div>
													
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="usermanage" value="User Management" > User Management
																</label>
															</div>
													</div>
													
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="rejectreason" value="Add Reject Reason" > Add Reject Reason
																</label>
															</div>
													</div>
													
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="releasingnetwork" value="Add Releasing Network" > Add Releasing Network
																</label>
															</div>
													</div>
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="internal_notes" value=" Add Internal Notes" > Add Internal Notes
																</label>
															</div>
													</div>
												<br>
												<div class="row col-md-12">
												
												
												<h3 class="section-title-with-tooltip">
													  Support System
													  <span data-toggle="tooltip" title="Access support tickets, FAQ, and rights requests" class="tooltip-icon">
														<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" viewBox="0 0 24 24">
														  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
															10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
														</svg>
													  </span>
													</h3>
													
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="rightsmanager" value="Rights Manager" > Rights Manager
																</label>
															</div>
													</div>
													
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="tickets" value="Tickets & History" > Tickets & History
																</label>
															</div>
													</div>
													
													<div class="col-md-12">
															<div class="checkbox">
																<label for="easyEntryEditForm-isCompil-0">
																		<input type="checkbox" class="" name="user_access[]" id="faq" value="FAQ" > FAQ
																</label>
															</div>
													</div>
												</div>
												
												
											</fieldset>
										</div>
										<div class="col-md-6 full_access_div">
											<fieldset style="padding-top: 5px">
												<legend>🌎 ASSIGN LABELS & ARTISTS</legend>
												<div class="info-box">
													<div style="background: #eef7ff; padding: 18px 22px; border-left: 5px solid #007bff; border-radius: 10px; font-size: 12px; color: #2c3e50; line-height: 1.6; margin-bottom: 25px;">
													  <span style="color:#c0392b;"><strong>Note:</strong> Labels and Artists are separate — assigning one doesn’t limit the other.</span><br>

													  • <strong>Labels</strong>: Grants full access to all content & Artists under selected labels.<br>
													  • <strong>Artists</strong>: Unlocks all data linked to the artist, across all labels.<br>

													</div>
												</div>
												<div class="row">
													<section class="form-group form-group-sm clearfix">
														<label class="col-md-12 control-label">
														  <strong class="txt-color-blue section-title-with-tooltip">
															<?php echo $this->translate('Labels'); ?>
															<span data-toggle="tooltip" title="Gives access to all content and artists under selected label(s)" class="tooltip-icon">
															  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" viewBox="0 0 24 24">
																<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
																10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
															  </svg>
															</span>
														  </strong>
														<a class="left btn btn-success btn-icon-inline btn-sm mb15 addEventBtn" id="label_select_all"><i class="ion ion-plus"></i> Select All</a>
														<a class="left btn btn-success btn-icon-inline btn-sm mb15 addEventBtn" id="label_remove_all"><i class="ion ion-minus"></i> Remove All</a>
														</label>
														<label class="col-md-12"> 
															<select type="multiselect" id="labels" name="labels" class="select2" multiple>
																
															</select>
															<small></small>
														</label>
														
													</section>
												</div>
												<div class="row">
													<section class="form-group form-group-sm clearfix">
														<label class="col-md-12 control-label">
															  <strong class="txt-color-blue section-title-with-tooltip">
																<?php echo $this->translate('Artists'); ?>
																<span data-toggle="tooltip" title="Grants access to all data linked to this artist across labels" class="tooltip-icon">
																  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" viewBox="0 0 24 24">
																	<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
																	10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
																  </svg>
																</span>
															  </strong>
														<a class="left btn btn-success btn-icon-inline btn-sm mb15 addEventBtn" id="artist_select_all"><i class="ion ion-plus"></i> Select All</a>
														<a class="left btn btn-success btn-icon-inline btn-sm mb15 addEventBtn" id="artist_remove_all"><i class="ion ion-minus"></i> Remove All</a>
														</label>
														<label class="col-md-12"> 
															<select type="multiselect" id="artist" name="artist" class="select2" multiple>
																
															</select>
															<small></small>
														</label>
														
													</section>
												</div>
												
											</fieldset>
										</div>
										
										
									</div>					
                </form>

                </div>
            </div>
            <!-- end general tab pane -->

        </div>
		<div class="buttons flex mt-10 right">
			<img style="margin: 10px 30px;" src="/public/img/loader.gif" class="nf-loader hide ">
			<a id="btnBack" href="javascript:;" class="bg-white border uppercase text-sm border-blue-500 text-blue-500 py-2 mr-2 px-10 rounded-full" style="">Cancel</a><button id="btnSave" class="btn-primary" form="attributeVerification" aria-label="Continue"   style="">Save & Email Login

</button>
			
		  </div>
        
    </div>
    </div>
</div>
  
  <style>
.section-title-with-tooltip {
  display: flex;
  align-items: center;
  
  font-size: 18px;
  font-family: Rubik, sans-serif;
  font-weight: 500;
 
  line-height: 1.4;
}

.tooltip-icon {
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  margin-left: 5px;
  vertical-align: middle;
}

.tooltip-inner {
  max-width: 250px;
  text-align: left;
  font-size: 12px;
  padding: 8px 10px;
}
</style>
  
  <script>
  $(function () {
    $('[data-toggle="tooltip"]').tooltip({ placement: 'top' });
  });
</script>


<!-- end widget div -->
