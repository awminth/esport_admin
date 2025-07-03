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
                <h3 class="content-header-title mb-0 d-inline-block">Wallet History</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Wallet History
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="product-detail">
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
                                    <a href="#" id='btnsearch'
                                        class="btn btn-social btn-block mr-1 mb-1 btn-yahoo"><span
                                            class="la la-search font-medium-3"></span> Search</a>
                                </div>
                                <div class="form-group  col-md-12">
                                    <label for="usr">Status</label>
                                    <select class=" form-control select2" name="chkstatus" id="chkstatus">
                                        <option value="">Choose Status</option>
                                        <option value="success">Success</option>
                                        <option value="fail">Fail</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12 pt-1">
                                    <form method="POST" action="wallethistory_action.php">
                                        <input type="hidden" name="hid">
                                        <input type="hidden" name="ser">
                                        <input type="hidden" name="dtfrom">
                                        <input type="hidden" name="dtto">
                                        <input type="hidden" name="status">
                                        <button type="submit" name="action" value="excel_topup"
                                            class="btn btn-social btn-block btn-dropbox excel_topup"><span
                                                class="la la-file-excel-o font-medium-3"></span> Excel</button>
                                        <button type="submit" name="action" value="pdf_topup"
                                            class="btn btn-social btn-block btn-dropbox pdf_topup"><span
                                                class="la la-file-pdf-o font-medium-3"></span> PDF</button>
                                        <button type="submit" name="action" value="excel_withdraw" style="display:none;"
                                            class="btn btn-social btn-block btn-dropbox excel_withdraw"><span
                                                class="la la-file-excel-o font-medium-3"></span> Excel</button>
                                        <button type="submit" name="action" value="pdf_withdraw" style="display:none;"
                                            class="btn btn-social btn-block btn-dropbox pdf_withdraw"><span
                                                class="la la-file-pdf-o font-medium-3"></span> PDF</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-underline no-hover-bg">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="base-limit" data-toggle="tab"
                                                aria-controls="limit" href="#limit" aria-expanded="true">Player
                                                Top-up</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="base-market" data-toggle="tab"
                                                aria-controls="market" href="#market" aria-expanded="false">Player
                                                Withdraw</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <div role="tabpanel" class="tab-pane active" id="limit" aria-expanded="true"
                                            aria-labelledby="base-limit">
                                            <table width="100%">
                                                <tr>
                                                    <td width="25%">
                                                        <div class="form-group row">
                                                            <label for="inputEmail3"
                                                                class="col-sm-4 col-form-label">Entry</label>
                                                            <div class="col-sm-8">
                                                                <select id="entry_topup" class="custom-select btn-sm">
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
                                                            <label for="inputEmail3"
                                                                class="col-sm-3 col-form-label">Search</label>
                                                            <div class="col-sm-9">
                                                                <input type="search" class="form-control"
                                                                    id="searching_topup" name="searching_topup"
                                                                    placeholder="Searching . . . . ">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div id="show_table_topup" class="table-responsive-sm">

                                            </div>
                                        </div>
                                        <div class="tab-pane" id="market" aria-labelledby="base-market">
                                            <table width="100%">
                                                <tr>
                                                    <td width="25%">
                                                        <div class="form-group row">
                                                            <label for="inputEmail3"
                                                                class="col-sm-4 col-form-label">Entry</label>
                                                            <div class="col-sm-8">
                                                                <select id="entry_withdraw"
                                                                    class="custom-select btn-sm">
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
                                                            <label for="inputEmail3"
                                                                class="col-sm-3 col-form-label">Search</label>
                                                            <div class="col-sm-9">
                                                                <input type="search" class="form-control"
                                                                    id="searching_withdraw" name="searching_withdraw"
                                                                    placeholder="Searching . . . . ">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div id="show_table_withdraw" class="table-responsive-sm">

                                            </div>
                                        </div>
                                    </div>
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


<?php include(root.'master/footer.php'); ?>

<script>
var wallethistory_url = "<?php echo roothtml.'wallet/wallethistory_action.php'; ?>";


$(document).ready(function() {
    function topup_load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        var dtfrom = $("[name='dtfrom']").val();
        var dtto = $("[name='dtto']").val();
        var status = $("[name='status']").val();
        $.ajax({
            type: "post",
            url: wallethistory_url,
            data: {
                action: 'show_table_topup',
                page_no: page,
                entryvalue: entryvalue,
                search: search,
                dtfrom: dtfrom,
                dtto: dtto,
                status: status
            },
            success: function(data) {
                $("#show_table_topup").html(data);
            }
        });
    }
    topup_load_page();

    $(document).on('click', '.tlink', function() {
        var pageid = $(this).data('page_number');
        topup_load_page(pageid);
    });

    $(document).on("change", "#entry_topup", function() {
        var entryvalue = $(this).val();
        $("[name='hid']").val(entryvalue);
        topup_load_page();
    });

    $(document).on("change", "#chkstatus", function() {
        var data = $(this).val();
        $("[name='status']").val(data);
        topup_load_page();
        withdraw_load_page();
    });

    $(document).on("keyup", "#searching_topup", function() {
        var serdata = $(this).val();
        $("[name='ser']").val(serdata);
        topup_load_page();
    });

    function withdraw_load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        var dtfrom = $("[name='dtfrom']").val();
        var dtto = $("[name='dtto']").val();
        var status = $("[name='status']").val();
        $.ajax({
            type: "post",
            url: wallethistory_url,
            data: {
                action: 'show_table_withdraw',
                page_no: page,
                entryvalue: entryvalue,
                search: search,
                dtfrom: dtfrom,
                dtto: dtto,
                status: status
            },
            success: function(data) {
                $("#show_table_withdraw").html(data);
            }
        });
    }
    withdraw_load_page();

    $(document).on('click', '.wlink', function() {
        var pageid = $(this).data('page_number');
        withdraw_load_page(pageid);
    });

    $(document).on("change", "#entry_withdraw", function() {
        var entryvalue = $(this).val();
        $("[name='hid']").val(entryvalue);
        withdraw_load_page();
    });

    $(document).on("keyup", "#searching_withdraw", function() {
        var serdata = $(this).val();
        $("[name='ser']").val(serdata);
        withdraw_load_page();
    });

    $(document).on("click", "#btnsearch", function(e) {
        e.preventDefault();
        var from = $("[name='from']").val();
        var to = $("[name='to']").val();
        $("[name='dtfrom']").val(from);
        $("[name='dtto']").val(to);
        topup_load_page();
        withdraw_load_page();
    });

    $(document).on("click", "#base-limit", function() {
        $("[name='searching_topup']").val("");
        $("[name='hid']").val("");
        $("[name='ser']").val("");
        $("[name='dtfrom']").val("");
        $("[name='dtto']").val("");
        $(".excel_topup").show();
        $(".pdf_topup").show();
        $(".excel_withdraw").hide();
        $(".pdf_withdraw").hide();
        topup_load_page();
    });

    $(document).on("click", "#base-market", function() {
        $("[name='searching_withdraw']").val("");
        $("[name='hid']").val("");
        $("[name='ser']").val("");
        $("[name='dtfrom']").val("");
        $("[name='dtto']").val("");
        $(".excel_topup").hide();
        $(".pdf_topup").hide();
        $(".excel_withdraw").show();
        $(".pdf_withdraw").show();
        withdraw_load_page();
    });







});
</script>