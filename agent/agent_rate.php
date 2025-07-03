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
                <h3 class="content-header-title mb-0 d-inline-block">Agent Report</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Agent Report
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
                        <form method="POST" action="agent_rate_action.php">
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
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Add New Agent</label>
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
                        <label for="usr">Currency</label>
                        <select required class=" form-control select2" name="currency">
                            <option value="">Choose Currency Unit</option>
                            <?php for($i=0;$i<count($arr_currency);$i++){ ?>
                            <option value="<?=$arr_currency[$i]?>"><?=$arr_currency[$i]?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usr">Minimum Bet</label>
                        <input type="number" required class="form-control" name="min"
                            placeholder="Enter Minimum Amount">
                    </div>
                    <div class="form-group">
                        <label for="usr">Maximum Bet</label>
                        <input type="number" required class="form-control" name="max"
                            placeholder="Enter Maximum Amount">
                    </div>
                    <div class="form-group">
                        <label for="usr">Max Per Match</label>
                        <input type="number" required class="form-control" name="maxpermatch"
                            placeholder="Enter Maximum Amount Per Match">
                    </div>
                    <div class="form-group">
                        <label for="usr">Casino Table Limit</label>
                        <select required class=" form-control select2" name="casinotablelimit">
                            <option value="">Choose Table Limit</option>
                            <option value="1">Low</option>
                            <option value="2">Medium</option>
                            <option value="3">High</option>
                            <option value="4">VIP(All)</option>
                        </select>
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

<!-- edit Modal -->
<div class="modal fade text-left" id="btneditmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel25"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel25">Edit User Control</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmedit" method="POST">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="eaid" />
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usr">User Name</label>
                        <input type="text" required class="form-control" name="eusername" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label for="usr">Password</label>
                        <input type="password" required class="form-control" name="epassword"
                            placeholder="Enter password">
                    </div>
                    <div class="form-group">
                        <label for="usr">User Type</label>
                        <select required class=" form-control select2" name="eusertype" id="eusertype">
                            <option value="">Choose User Type</option>
                            <?php for($i=0;$i<count($arr_usertype);$i++){ ?>
                            <option value="<?=$arr_usertype[$i]?>"><?=$arr_usertype[$i]?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-primary"><i class="la la-edit"></i>Edit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
    include(root.'master/footer.php');
?>

<script>
$(document).ready(function() {
    var rate_url = "<?php echo roothtml.'agent/agent_rate_action.php' ?>";

    function load_page(page) {
        var entryvalue = $("[name='hid']").val();
        var search = $("[name='ser']").val();
        $.ajax({
            type: "post",
            url: rate_url,
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

    $(document).on('click', '#btngo_detail', function() {
        var agentid = $(this).data('agentid');
        $.ajax({
            type: "post",
            url: rate_url,
            data: {
                action: 'go_detail',
                agentid: agentid
            },
            success: function(data) {
                if(data == 1){
                    window.location.href = "<?php echo roothtml.'agent/agent_rate_detail.php' ?>";
                }
            }
        });
    });




});
</script>