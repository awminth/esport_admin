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
                <h3 class="content-header-title mb-0 d-inline-block">Manage Player</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Manage Player
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="btn-group float-md-right" role="group" aria-label="Button group with nested dropdown">
                    <button class="btn btn-info round dropdown-toggle dropdown-menu-right box-shadow-2 px-2 mb-1"
                        id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false"><i class="ft-settings icon-left"></i> Settings</button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" x-placement="bottom-start"
                        style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 41px, 0px);">
                        <button type="button" id="btnnewplayer" class="dropdown-item text-info"><span
                                class="la la-plus-circle font-medium-3 icon-left"></span>New</button>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="player_action.php">
                            <input type="hidden" name="hid">
                            <input type="hidden" name="ser">
                            <button type="submit" name="action" value="excel" class="dropdown-item text-danger"><span
                                    class="la la-file-excel-o font-medium-3 icon-left"></span>Excel</button>
                            <div class="dropdown-divider"></div>
                            <button type="submit" name="action" value="pdf" class="dropdown-item text-danger"><span
                                    class="la la-file-pdf-o font-medium-3 icon-left"></span>PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="product-detail">
                <div class="row mb-5">
                    <div id="recent-transactions" class="col-12">
                        <div class="card">
                            <div class="card-content p-2">
                                <table width="100%">
                                    <tr>
                                        <td width="20%">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-5 col-form-label">Show</label>
                                                <div class="col-sm-7">
                                                    <select id="entry" class="custom-select btn-sm">
                                                        <option value="10" selected>10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td width="55%" class="float-right">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Search</label>
                                                <div class="col-sm-10">
                                                    <input type="search" class="form-control" id="searching"
                                                        placeholder="Searching . . . . . ">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <div class="table-responsive" id="show_table">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Content-->

<!-- new Modal -->
<div class="modal fade text-left" id="btnnewmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel25"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Add New Player</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmsave" method="POST">
                <input type="hidden" name="action" value="save" />
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usr">User Name</label>
                        <span class="text-danger" id="usernameerror">(Number, Letter and _ only.)(must be 6 to 40
                            characters)</span>
                        <input type="text" required class="form-control" name="username" placeholder="Enter username"
                            id="username">
                    </div>
                    <div class="form-group">
                        <label for="usr">Password</label>
                        <span class="text-danger" id="passworderror">(Number, Letter only.)(must be 6 to 20
                            characters)</span>
                        <input type="password" required class="form-control" name="password" id="password"
                            placeholder="Enter password">
                    </div>
                    <div class="form-group">
                        <label for="usr">PhoneNo</label>
                        <input type="text" required class="form-control" name="phoneno" 
                            placeholder="Enter PhoneNo">
                    </div>
                    <div class="form-group">
                        <label for="usr">Email</label>
                        <input type="email" required class="form-control" name="email" 
                            placeholder="Enter Email">
                    </div>
                    <div class="form-group">
                        <label for="usr">NRC</label>
                        <input type="text" required class="form-control" name="nrc" 
                            placeholder="Enter NRC No">
                    </div>
                    <div class="form-group">
                        <label for="usr">Choose Agent</label>
                        <select required class=" form-control select2" name="agentid">
                            <option value="">Choose Agent</option>
                            <?php echo load_agent(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usr">Display Name</label>
                        <input type="text" required class="form-control" name="displayname"
                            placeholder="Enter Display Name">
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


<?php  include(root.'master/footer.php');  ?>

<script>
$(document).ready(function() {
    function load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        $.ajax({
            type: "post",
            url: "<?php echo roothtml.'player/player_action.php' ?>",
            data: {
                action: 'show',
                page_no: page,
                entryvalue: entryvalue,
                search: search
            },
            success: function(data) {
                $("#show_table").html(data);
            }
        });
    }
    load_page();

    $(document).on('click', '.page-link', function() {
        var pageid = $(this).data('page_number');
        load_page(pageid);
    });

    $(document).on("change", "#entry", function() {
        var entryvalue = $(this).val();
        $("[name='hid']").val(entryvalue);
        load_page();
    });

    $(document).on("keyup", "#searching", function() {
        var serdata = $(this).val();
        $("[name='ser']").val(serdata);
        load_page();
    });

    $(document).on("click", "#btnnewplayer", function() {
        $("#btnnewmodal").modal("show");
    });

    $("#username").on("input", function() {
        var username = $(this).val();
        var regex = /^[a-zA-Z0-9_]{6,40}$/; // Letters, Numbers, and Underscore, 6 to 40 characters

        if (regex.test(username)) {
            $("#usernameerror").hide(); // Valid input
        } else {
            $("#usernameerror").show(); // Show error if invalid
        }
    });

    $("#password").on("input", function() {
        var password = $(this).val();
        var regex = /^[a-zA-Z0-9]{6,20}$/; // Letters, Numbers, and Underscore, 6 to 40 characters

        if (regex.test(password)) {
            $("#passworderror").hide(); // Valid input
        } else {
            $("#passworderror").show(); // Show error if invalid
        }
    });

    $("#frmsave").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var username = $("[name='username']").val();
        var regex_username = /^[a-zA-Z0-9_]{6,40}$/;
        //Check Username
        if (!regex_username.test(username)) {
            Swal.fire("Warning", "(Number, Letter and _ only.)(Username must be 6 to 40 characters)",
                "warning");
            return false;
        }
        //Check Password
        var password = $("[name='password']").val();
        var regex_password = /^[a-zA-Z0-9]{6,20}$/;
        if (!regex_password.test(password)) {
            Swal.fire("Warning", "(Number, Letter only.)(Password must be 6 to 20 characters)",
                "warning");
            return false;
        }        
        $("#btnnewmodal").modal("hide");
        $.ajax({
            type: "post",
            url: "<?php echo roothtml.'player/player_action.php' ?>",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                // Swal.fire(data);
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
                } else if (data == 2) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'info',
                        title: 'Server Request no response.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                } else if (data == 0) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Save data is error.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });

                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: data,
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                }
            }
        });
    });

    //Active agent
    $(document).on("click", "#btnactive", function() {
        var aid = $(this).data("aid");
        var serverid = $(this).data("serverid");
        var username = $(this).data("username");
        var status = $(this).data("status");
        $.ajax({
            type: "post",
            url: "<?php echo roothtml.'player/player_action.php' ?>",
            data: {
                action: 'editplayer',
                aid: aid,
                serverid: serverid,
                username: username,
                status: status
            },
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Update Status data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else if (data == 2) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'info',
                        title: 'Server Request no response.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: data,
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                }
            }
        });
    });

    //Suspend Agent
    $(document).on("click", "#btnsuspend", function() {
        var aid = $(this).data("aid");
        var serverid = $(this).data("serverid");
        var username = $(this).data("username");
        var status = $(this).data("status");
        $.ajax({
            type: "post",
            url: "<?php echo roothtml.'player/player_action.php' ?>",
            data: {
                action: 'editplayer',
                aid: aid,
                serverid: serverid,
                username: username,
                status: status
            },
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Update Status data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else if (data == 2) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'info',
                        title: 'Server Request no response.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: data,
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                }
            }
        });
    });

    //Close Agent
    $(document).on("click", "#btnclose", function() {
        var aid = $(this).data("aid");
        var serverid = $(this).data("serverid");
        var username = $(this).data("username");
        var status = $(this).data("status");
        $.ajax({
            type: "post",
            url: "<?php echo roothtml.'player/player_action.php' ?>",
            data: {
                action: 'editplayer',
                aid: aid,
                serverid: serverid,
                username: username,
                status: status
            },
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Update Status data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else if (data == 2) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'info',
                        title: 'Server Request no response.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: data,
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