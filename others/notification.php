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
                <h3 class="content-header-title mb-0 d-inline-block">Manage Notification</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Manage Notification
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
                        <button type="button" id="btnnewnotification" class="dropdown-item text-info"><span
                                class="la la-plus-circle font-medium-3 icon-left"></span>Add</button>
                        <div class="dropdown-divider" style="display: none;"></div>
                        <form method="POST" action="notification_action.php" style="display: none;">
                            <input type="hidden" name="hid">
                            <input type="hidden" name="dtfrom">
                            <input type="hidden" name="dtto">
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
            <div class="row mb-5">
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-content collapse show">
                            <div class="form-group col-md-12 pt-1">
                                <label for=" date12" class="filled">From</label>
                                <div class="position-relative has-icon-left">
                                    <input type="date" value="<?=date('Y-m-d')?>" id="timesheetinput3"
                                        class="form-control" name="from">
                                    <div class="form-control-position">
                                        <i class="ft-message-square"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12 ">
                                <label for=" date12" class="filled">To</label>
                                <div class="position-relative has-icon-left">
                                    <input type="date" value="<?=date('Y-m-d')?>" id="timesheetinput3"
                                        class="form-control" name="to">
                                    <div class="form-control-position">
                                        <i class="ft-message-square"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <a href="#" id='btnsearch' class="btn btn-social btn-block mr-1 mb-1 btn-yahoo"><span
                                        class="la la-search font-medium-3"></span> Search</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-9" id="show_table">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Content-->

<div class="modal fade text-left" id="newnotificationmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel25"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Add Notification</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmsave" method="POST">
                <input type="hidden" name="action" value="save" />
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label for="usr">Title</label>
                        <textarea required class="form-control" name="title" rows="3"
                            placeholder="Write Title"></textarea>
                    </div>
                    <div class="form-group col-12">
                        <label for="usr">Description</label>
                        <textarea required class="form-control" name="desc" rows="3"
                            placeholder="Write Description"></textarea>
                    </div>
                    <div class="form-group col-12">
                        <label for="usr">Date</label>
                        <input type="datetime-local" class="form-control" name="dt" placeholder="Insert Date" required
                            value="<?php echo date('Y-m-d H:i:s');?>">

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

<div class="modal fade text-left" id="editnotificationmodal" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabel25" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Edit Notification</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmedit" method="POST">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="eaid" />
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label for="usr">Title</label>
                        <textarea required class="form-control" name="etitle" rows="3"
                            placeholder="Write Title"></textarea>
                    </div>
                    <div class="form-group col-12">
                        <label for="usr">Description</label>
                        <textarea required class="form-control" name="edesc" rows="3"
                            placeholder="Write Description"></textarea>
                    </div>
                    <div class="form-group col-12">
                        <label for="usr">Date</label>
                        <input type="datetime-local" class="form-control" name="edt" placeholder="Insert Date" 
                        step="1">
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

<?php include(root.'master/footer.php'); ?>

<script>
$(document).ready(function() {
    var notification_url = "<?php echo roothtml.'others/notification_action.php' ?>";

    function load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var dtfrom = $("[name='dtfrom']").val();
        var dtto = $("[name='dtto']").val();
        $.ajax({
            type: "post",
            url: notification_url,
            data: {
                action: 'show',
                page_no: page,
                entryvalue: entryvalue,
                dtfrom: dtfrom,
                dtto: dtto
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

    $(document).on("click", "#btnsearch", function(e) {
        e.preventDefault();
        var from = $("[name='from']").val();
        var to = $("[name='to']").val();
        $("[name='dtfrom']").val(from);
        $("[name='dtto']").val(to);
        load_page();
    });

    $(document).on("click", "#btnnewnotification", function() {
        $("#newnotificationmodal").modal("show");
    });

    $("#frmsave").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $("#newnotificationmodal").modal("hide");
        $.ajax({
            type: "post",
            url: notification_url,
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

    $(document).on("click", "#btnedit", function(e) {
        e.preventDefault();
        var aid = $(this).data("aid");
        var title = $(this).data("title");
        var desc = $(this).data("desc");
        var dt = $(this).data("dt");
        $("[name='eaid']").val(aid);
        $("[name='etitle']").val(title);
        $("[name='edesc']").val(desc);
        $("[name='edt']").val(dt);
        $("#editnotificationmodal").modal("show");
    });

    $("#frmedit").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $("#editnotificationmodal").modal("hide");
        $.ajax({
            type: "post",
            url: notification_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Edit data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Edit data is error.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                }
            }
        });
    });

    $(document).on("click", "#btndelete", function(e) {
        e.preventDefault();
        var aid = $(this).data("aid");
        Swal.fire({
            title: 'Delete?',
            text: "Are you sure delete!",
            icon: 'error',
            showCancelButton: true,
            showCloseButton: false,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            allowEscapeKey: false,
            focusConfirm: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: notification_url,
                    data: {
                        action: 'delete',
                        aid: aid,
                    },
                    success: function(data) {
                        if (data == 1) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Delete data is successful.',
                                showConfirmButton: false,
                                timer: 3000,
                                toast: true
                            });
                            load_page();
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Delete data is failed.',
                                showConfirmButton: false,
                                timer: 3000,
                                toast: true
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", "#btnactive", function(e){
        var aid = $(this).data("aid");
        $.ajax({
            type: "POST",
            url: notification_url,
            data: {
                action: 'active',
                aid: aid,
            },
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Unactive data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Unactive data is error.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                }
            }
        });
    });

    $(document).on("click", "#btnunactive", function(e){
        var aid = $(this).data("aid");
        $.ajax({
            type: "POST",
            url: notification_url,
            data: {
                action: 'unactive',
                aid: aid,
            },
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Active data is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Active data is error.',
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