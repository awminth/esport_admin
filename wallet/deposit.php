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
                <h3 class="content-header-title mb-0 d-inline-block">Manage Top-up</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Manage Top-up
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
                        <button type="button" id="btntopup" class="dropdown-item text-info"><span
                                class="la la-plus-circle font-medium-3 icon-left"></span>New</button>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="deposit_action.php">
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
<div class="modal fade text-left" id="topupmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel25"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Add Player Top-up</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmsave" method="POST">
                <input type="hidden" name="action" value="save" />
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usr">Account Name</label>
                        <select required class=" form-control select2" name="playerid">
                            <option value="">Choose Account Name</option>
                            <?=load_player();?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usr">Amount</label>
                        <input type="number" required class="form-control" name="amount" placeholder="Enter number">
                    </div>
                    <div class="form-group">
                        <label for="usr">Pay Type</label>
                        <select required class=" form-control select2" name="paytype">
                            <option value="">Choose User Type</option>
                            <?php for($i=0;$i<count($pay_type);$i++){ ?>
                            <option value="<?=$pay_type[$i]?>"><?=$pay_type[$i]?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usr">Pay Name</label>
                        <input type="text" required class="form-control" name="payname" placeholder="Enter pay name">
                    </div>
                    <div class="form-group">
                        <label for="usr">Pay PhoneNo</label>
                        <input type="number" required class="form-control" name="payphno" placeholder="Enter pay phone no">
                    </div>
                    <div class="form-group">
                        <label for="usr">Transition Code</label>
                        <input type="text" required class="form-control" name="transitioncode"
                            placeholder="Enter transition code">
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
var ajax_url = "<?php echo roothtml.'wallet/deposit_action.php'; ?>";


$(document).ready(function() {
    function load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        $.ajax({
            type: "post",
            url: ajax_url,
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
    setInterval(load_page, 10000);

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

    $(document).on("click", "#btnprepaidconfirm", function(e) {
        e.preventDefault();
        var aid = $(this).data("aid");
        var playerid = $(this).data("playerid");
        var playername = $(this).data("playername");
        var amount = $(this).data("amount");
        Swal.fire({
            title: 'Check Success or Fail?',
            text: "This information cannot be retrieved!",
            icon: 'warning',
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Top-up Success',
            cancelButtonText: 'Top-up Fail',
            allowOutsideClick: false,
            allowEscapeKey: false,
            focusConfirm: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // success Button ကိုနှိပ်လျှင်
                $.ajax({
                    type: "post",
                    url: ajax_url,
                    data: {
                        action: 'success',
                        aid: aid,
                        playerid: playerid,
                        playername: playername,
                        amount: amount,
                    },
                    success: function(data) {
                        if (data == 1) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Top-up was successful.',
                                showConfirmButton: false,
                                timer: 3000,
                                toast: true
                            });
                            load_page();
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'Top-up failed. Please try again.',
                                showConfirmButton: false,
                                timer: 3000,
                                toast: true
                            });
                        }
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // fail (မဖျက်မည်) Button ကိုနှိပ်လျှင်
                $.ajax({
                    type: "post",
                    url: ajax_url,
                    data: {
                        action: 'fail',
                        aid: aid,
                        playerid: playerid,
                        playername: playername,
                        amount: amount,
                    },
                    success: function(data) {
                        if (data == 1) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Top-up canceled successfully.',
                                showConfirmButton: false,
                                timer: 3000,
                                toast: true
                            });
                            load_page();
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'Top-up canceled failed. Please try again.',
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

    $(document).on("click", "#btntopup", function() {
        $("#topupmodal").modal("show");
    });

    $("#frmsave").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $("#topupmodal").modal("hide");
        $.ajax({
            type: "post",
            url: ajax_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data == 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Save top-up is successful.',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true
                    });
                    load_page();
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Save top-up is failed.',
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