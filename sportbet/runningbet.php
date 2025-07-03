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
                <h3 class="content-header-title mb-0 d-inline-block">Running</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Running
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
                        <form method="POST" action="runningbet_action.php">
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

<div class="modal fade text-left" id="detailmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel25"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Bet Detail</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"  id="show_detail">
                <p>
                    TransferCode : 123456 <br>
                    BetType : Single <br>
                    BetDate : 2023-10-01 12:00:00
                </p>
                <div id="accordionWrap4" role="tablist" aria-multiselectable="true">
                    <div class="card accordion collapse-icon accordion-icon-rotate">
                        <a id="heading41" class="card-header py-1 bg-secondary secondary" data-toggle="collapse"
                            href="#accordion41" aria-expanded="false" aria-controls="accordion41">
                            <div class="white">
                                <div class="row">
                                    <div class="col-1 pr-0">
                                        <span><i class="ft-check-circle h4 align-middle success"></i></span>
                                    </div>
                                    <div class="col-11 pl-1">
                                        <span>Under 2.75 </span>
                                        <span class="text-warning">&nbsp;|&nbsp;</span>
                                        <span>Venezia vs Fiorentina</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div id="accordion41" role="tabpanel" data-parent="#accordionWrap4" aria-labelledby="heading41"
                            class="border-success card-collapse collapse" aria-expanded="false">
                            <div class="card-content">
                                <div class="card-body p-1">
                                    TransferCode : 123456 <br>
                                    BetType : Single <br>
                                    BetDate : 2023-10-01 12:00:00
                                </div>
                            </div>
                        </div>
                        <a id="heading42" class="card-header  py-1 bg-secondary secondary" data-toggle="collapse"
                            href="#accordion42" aria-expanded="false" aria-controls="accordion42">
                            <div class="white">
                                <div class="row">
                                    <div class="col-1 pr-0">
                                        <span><i class="ft-x-circle h4 align-middle danger"></i></span>
                                    </div>
                                    <div class="col-11 pl-1">
                                        <span>Fiorentina (-1.5)</span>
                                        <span class="text-warning">&nbsp;|&nbsp;</span>
                                        <span>Venezia vs Fiorentina</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div id="accordion42" role="tabpanel" data-parent="#accordionWrap4" aria-labelledby="heading42"
                            class="border-danger card-collapse collapse" aria-expanded="false">
                            <div class="card-content">
                                <div class="card-body">
                                    this is detail
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(root.'master/footer.php'); ?>

<script>
var runningbet_url = "<?php echo roothtml.'sportbet/runningbet_action.php'; ?>";

$(document).ready(function() {
    function load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        $.ajax({
            type: "post",
            url: runningbet_url,
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

    $(document).on("click", "#btndetail_view", function() {
        var transfercode = $(this).data("transfercode");
        $.ajax({
            type: "post",
            url: runningbet_url,
            data: {
                action: 'detail_view',
                transfercode: transfercode,
            },
            success: function(data) {
                $("#show_detail").html(data);
                $("#detailmodal").modal("show");
            }
        });
        // $("#detailmodal").modal("show");
    });




});
</script>