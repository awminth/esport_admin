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
                <h3 class="content-header-title mb-0 d-inline-block">Settled Report</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Settled Report
                            </li>
                        </ol>
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
                            <hr>
                            <div class="form-group col-md-12 text-center">
                                <fieldset class="checkboxsas" style="display: inline-block; margin-right: 20px;">
                                    <label>
                                        <input type="radio" id="searchRadiobox" name="searchRadiobox" value="sport" checked style="transform: scale(1.5);">
                                        <span class="">&nbsp;&nbsp;Sport</span>
                                    </label>
                                </fieldset>
                                <fieldset class="checkboxsas ml-1" style="display: inline-block;">
                                    <label>
                                        <input type="radio" id="searchRadiobox" name="searchRadiobox" value="casino" style="transform: scale(1.5);">
                                        <span class="">&nbsp;&nbsp;Casino</span>
                                    </label>
                                </fieldset>
                                <fieldset class="checkboxsas ml-1" style="display: inline-block;">
                                    <label>
                                        <input type="radio" id="searchRadiobox" name="searchRadiobox" value="all" style="transform: scale(1.5);">
                                        <span class="">&nbsp;&nbsp;All</span>
                                    </label>
                                </fieldset>
                            </div>
                            <hr>
                            <div class="form-group col-md-12">
                                <select required class=" form-control select2" id="playeraccount">
                                    <option value="">Choose Player Account</option>
                                    <?php echo load_player(); ?>
                                </select>
                            </div>
                            <hr>
                            <div class="form-group col-md-12 pt-1">
                                <form method="POST" action="settledreport_action.php">
                                    <input type="hidden" name="hid">
                                    <input type="hidden" name="ser">
                                    <input type="hidden" name="dtfrom">
                                    <input type="hidden" name="dtto">
                                    <input type="hidden" name="hidplayeraccount">
                                    <input type="hidden" name="hidgametype" value="sport">
                                    <button type="submit" name="action" value="excel"
                                        class="btn btn-social btn-block btn-dropbox"><span
                                            class="la la-file-excel-o font-medium-3"></span> Excel</button>
                                    <button type="submit" name="action" value="pdf"
                                        class="btn btn-social btn-block btn-dropbox"><span
                                            class="la la-file-pdf-o font-medium-3"></span> PDF</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-9">
                    <div class="card">
                        <div class="card-content collapse show">
                            <div class="card-body">
                                <table width="100%">
                                    <tr>
                                        <td width="25%">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-4 col-form-label">Entry</label>
                                                <div class="col-sm-8">
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
                                                <label for="inputEmail3" class="col-sm-3 col-form-label">Search</label>
                                                <div class="col-sm-9">
                                                    <input type="search" class="form-control" id="searching"
                                                        placeholder="Searching . . . . ">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <div id="show_table" class="table-responsive-sm">

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
var settledreport_url = "<?php echo roothtml.'reports/settledreport_action.php'; ?>";

$(document).ready(function() {
    function load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        var dtfrom = $("[name='dtfrom']").val();
        var dtto = $("[name='dtto']").val();
        var playeraccount = $("[name='hidplayeraccount']").val();
        var hidgametype = $("[name='hidgametype']").val();
        $.ajax({
            type: "post",
            url: settledreport_url,
            data: {
                action: 'show',
                page_no: page,
                entryvalue: entryvalue,
                search: search,
                dtfrom: dtfrom,
                dtto: dtto,
                playeraccount: playeraccount,
                hidgametype: hidgametype,
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

    $(document).on("change", "#playeraccount", function() {
        var entryvalue = $(this).val();
        $("[name='hidplayeraccount']").val(entryvalue);
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

    $('input[name="searchRadiobox"]').change(function() {
        var selectedValue = $('input[name="searchRadiobox"]:checked').val();
        $("[name='hidgametype']").val($(this).val());
        load_page();
    });




});
</script>