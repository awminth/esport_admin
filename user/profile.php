<?php
    include('../config.php');
    include(root.'master/header.php');

    $img = (isset($_SESSION["esport_admin_img"])?$_SESSION["esport_admin_img"]:"");
    $temp = roothtml.'lib/images/user.png';
    if($img != "" || $img != NULL){
        $temp = roothtml.'lib/images/'.$img;
    }
?>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                <h3 class="content-header-title mb-0 d-inline-block">Profile</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'user/user.php'?>">User Control</a>
                            </li>
                            <li class="breadcrumb-item active">Profile
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-5">
                <div id="recent-transactions" class="col-sm-7">
                    <div class="card">
                        <div class="card-content p-2">
                            <form id="frmpassword" method="POST">
                                <input type="hidden" name="img" value="<?=$img?>">
                                <input type="hidden" name="action" value="changeprofile" >
                                <div class="row">
                                    <div class="col-12">
                                        <div class="media">
                                            <a href="javascript: void(0);">
                                                <img src="<?=$temp?>" id="profileImagePreview" class="rounded mr-75" alt="profile image"
                                                    height="64" width="64">
                                            </a>
                                            <div class="media-body mt-75">
                                                <div
                                                    class="col-12 px-0 d-flex flex-sm-row flex-column justify-content-start">
                                                    <label
                                                        class="btn btn-sm btn-primary ml-50 mb-50 mb-sm-0 cursor-pointer"
                                                        for="account-upload">Upload new photo</label>
                                                    <input type="file" id="account-upload" name="account-upload" hidden accept="image/jpeg, image/png, image/jpg">
                                                </div>
                                                <p class="text-muted ml-75 mt-50">
                                                    <small>Allowed JPG, JPEG or PNG.</small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="controls">
                                                <label for="account-old-password">Old Password</label>
                                                <input type="password" class="form-control" required=""
                                                    placeholder="Old Password"
                                                    value="<?=$_SESSION['esport_admin_userpassword']?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="controls">
                                                <label for="account-new-password">New Password</label>
                                                <input type="password" name="password" class="form-control"
                                                    placeholder="New Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="controls">
                                                <label for="account-retype-new-password">Retype New
                                                    Password</label>
                                                <input type="password" name="repassword" class="form-control"
                                                    data-validation-match-match="password" placeholder="New Password"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 d-flex flex-sm-row flex-column justify-content-end">
                                        <button type="submit" class="btn btn-primary mr-sm-1 mb-1 mb-sm-0">Save
                                            changes</button>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Content-->


<?php
    include(root.'master/footer.php');
?>

<script>
var user_url = "<?php echo roothtml.'user/user_action.php'; ?>";

$(document).ready(function() {
    $('#account-upload').change(function(e) {
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

    $("#frmpassword").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var pass = $("[name='password']").val();
        var repass = $("[name='repassword']").val();
        if (pass != repass) {
            Swal.fire({
                position: 'top-end',
                icon: 'warning',
                title: 'Password and retype password is not match.',
                showConfirmButton: false,
                timer: 3000,
                toast: true
            });
            return false;
        }
        $.ajax({
            type: "post",
            url: user_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Save change data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    window.location.reload();
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