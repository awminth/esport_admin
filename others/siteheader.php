<?php
    include('../config.php');
    include(root.'master/header.php');
?>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                <h3 class="content-header-title mb-0 d-inline-block">Manage Main Header</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Manage Main Header
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body" id="show_card">

        </div>
    </div>
</div>
<!-- END: Content-->

<!-- new Modal -->
<div class="modal fade text-left" id="Editmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel25"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Edit Main Header</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmedit" method="POST">
                <input type="hidden" name="action" value="save" />
                <input type="hidden" name="aid" />
                <input type="hidden" name="img" />
                <input type="hidden" name="oldimg" />
                <div class="modal-body">
                    <div class="media">
                        <a href="javascript: void(0);">
                            <img src="" id="profileImagePreview"
                                class="rounded mr-75" alt="profile image" height="64" width="64">
                        </a>
                        <div class="media-body mt-75">
                            <div class="col-12 px-0 d-flex flex-sm-row flex-column justify-content-start">
                                <label class="btn btn-sm btn-primary ml-50 mb-50 mb-sm-0 cursor-pointer"
                                    for="uploadimg">Upload new photo</label>
                                <input type="file" id="uploadimg" name="uploadimg" hidden
                                    accept="image/jpeg, image/png, image/jpg">
                            </div>
                            <p class="text-muted ml-75 mt-50">
                                <small>Allowed JPG, JPEG or PNG.</small>
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <label for="account-old-password">Title</label>
                            <input type="text" class="form-control" required placeholder="Title"
                                name="title">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <label for="account-old-password">Description</label>
                            <textarea class="form-control" rows="5" required placeholder="Description"
                                name="description"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-primary"><i class="la la-save"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
    include(root.'master/footer.php');
?>

<script>
var siteheader_url = "<?php echo roothtml.'others/siteheader_action.php'; ?>";

$(document).ready(function() {

    function load_page() {
        $.ajax({
            type: "post",
            url: siteheader_url,
            data: {
                action: 'show',
            },
            success: function(data) {
                $("#show_card").html(data);
            }
        });
    }
    load_page();

    $(document).on('click', '#btnEdit', function() {
        var aid = $(this).data('aid');
        var title = $(this).data('title');
        var description = $(this).data('description');
        var img = $(this).data('img');
        $("[name='aid']").val(aid);
        $("[name='title']").val(title);
        $("[name='description']").val(description);
        $("[name='oldimg']").val(img);
        var img_url = "<?php echo roothtml; ?>lib/images/" + img;
        $('#profileImagePreview').attr('src', img_url);
        $('#Editmodal').modal('show');
    });

    $('#uploadimg').change(function(e) {
        // Check if file is selected
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            var file = this.files[0];
            $("[name='img']").val(file.name);
            reader.onload = function(e) {
                $('#profileImagePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }else{
            $("[name='img']").val('');
        }
    });

    $("#frmedit").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('#Editmodal').modal('hide');
        $.ajax({
            type: "post",
            url: siteheader_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Save data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Save data is error.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                }
            }
        });
    });



});
</script>