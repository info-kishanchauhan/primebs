<div class="jarviswidget panel panel-default panel-hovered panel-stacked mb30" id="widForm"
     data-widget-colorbutton="false"
     data-widget-editbutton="false"
     data-widget-togglebutton="false"
     data-widget-deletebutton="false"
     data-widget-fullscreenbutton="false"
     data-widget-custombutton="false"
     role="widget">
     
    <div class="panel-body">
        <div class="widget-body">
            <div id="myTabContent">
                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="general">
                    <form id="frmForm" class="smart-form" novalidate="novalidate" enctype="multipart/form-data">
                        <input type="hidden" name="MASTER_KEY_ID" id="MASTER_KEY_ID" value="0">
                        
                        <div class="row">
                            <!-- Store Name -->
                            <div class="col-md-4">
                                <fieldset style="padding-top: 5px">
                                    <div class="row">
                                        <section class="form-group form-group-sm clearfix">
                                            <label class="col-md-12 control-label">
                                                <strong class="txt-color-blue">Store Name</strong>
                                                <span class="required">*</span>
                                            </label>
                                            <label class="col-md-12"> 
                                                <input type="text" id="name" name="name" class="form-control" placeholder="Enter store name">
                                                <small></small>
                                            </label>
                                        </section>
                                    </div>
                                </fieldset>
                            </div>
                            
                            <!-- Store Image -->
                            <div class="col-md-4">
                                <fieldset style="padding-top: 5px">
                                    <div class="row">
                                        <section class="form-group form-group-sm clearfix">
                                            <label class="col-md-12 control-label">
                                                <strong class="txt-color-blue">Store Image</strong>
                                            </label>
                                            <label class="col-md-12"> 
                                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                                <small class="text-muted">Max size: 2MB (JPG, PNG, GIF)</small>
                                            </label>
                                        </section>
                                    </div>
                                </fieldset>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="col-md-4">
                                <div class="buttons flex mt-8">
                                    <button id="btnSave" type="button" class="btn-primary mr-2" aria-label="Continue">Save</button>
                                    <a id="btnClear" href="javascript:void(0)" class="bg-white border uppercase text-sm border-blue-500 text-blue-500 py-2 ml-2 px-10 rounded-full">Clear</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Preview Row -->
                        <div class="row mt-3">
                            <div class="col-md-8">
                                <fieldset style="padding-top: 5px">
                                    <div class="row">
                                        <section class="form-group form-group-sm clearfix">
                                            <label class="col-md-12 control-label">
                                                <strong class="txt-color-blue">Image Preview</strong>
                                            </label>
                                            <label class="col-md-12">
                                                <div id="imagePreview" class="image-preview-container">
                                                    <div class="no-image-preview">
                                                        <i class="fa fa-image fa-2x text-muted"></i>
                                                        <div class="text-muted small mt-2">No image selected</div>
                                                    </div>
                                                </div>
                                                <div id="imageName" class="text-muted small mt-2"></div>
                                            </label>
                                        </section>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.image-preview-container {
    width: 70px;
    height: 70px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9f9f9;
    overflow: hidden;
}

.no-image-preview {
    text-align: center;
    color: #999;
}

.image-preview-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
</style>

<script>
$(document).ready(function() {
    // Image preview functionality
    $('#image').change(function() {
        var file = this.files[0];
        var previewContainer = $('#imagePreview');
        var imageName = $('#imageName');
        
        if (file) {
            // Validate file type
            var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                mySmallAlert('Error', 'Please select a valid image file (JPG, PNG, GIF)', 0);
                $(this).val('');
                return;
            }
            
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                mySmallAlert('Error', 'Image size should be less than 2MB', 0);
                $(this).val('');
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.html('<img src="' + e.target.result + '" alt="Preview">');
            };
            reader.readAsDataURL(file);
            
            imageName.text('Selected: ' + file.name);
        } else {
            previewContainer.html('<div class="no-image-preview"><i class="fa fa-image fa-2x text-muted"></i><div class="text-muted small mt-2">No image selected</div></div>');
            imageName.text('');
        }
    });
    
    // Clear button functionality
    $('#btnClear').click(function() {
        clearForm('frmForm');
        strActionMode = "ADD";
        iActiveID = 0;
        $('#imagePreview').html('<div class="no-image-preview"><i class="fa fa-image fa-2x text-muted"></i><div class="text-muted small mt-2">No image selected</div></div>');
        $('#imageName').text('');
    });
    
    // Save button functionality
    $("#btnSave").click(function (e) {
        e.stopPropagation();
        $("#frmForm").submit();
    });
});
</script>