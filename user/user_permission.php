<?php
    include('../config.php');
    include(root.'master/header.php');

    $aid = (isset($_SESSION["go_sport_permission_aid"])?$_SESSION["go_sport_permission_aid"]:0);
    $sql = "select * from tbluser where AID={$aid}";
    $result = mysqli_query($con,$sql) or die("SQL a Query");
    $row = mysqli_fetch_array($result);

    $P1 = $row["P1"];
    $P2 = $row["P2"];

    $M1 = $row["M1"];
    $M1P1 = $row["M1P1"];
    $M1P2 = $row["M1P2"];
    $M1P3 = $row["M1P3"];

    $M2 = $row["M2"];
    $M2P1 = $row["M2P1"];
    $M2P2 = $row["M2P2"];
    $M2P3 = $row["M2P3"];

    $M3 = $row["M3"];
    $M3P1 = $row["M3P1"];
    $M3P2 = $row["M3P2"];
    $M3P3 = $row["M3P3"];

    $M4 = $row["M4"];
    $M4P1 = $row["M4P1"];
    $M4P2 = $row["M4P2"];
    $M4P3 = $row["M4P3"];
    $M4P4 = $row["M4P4"];

    $M5 = $row["M5"];
    $M5P1 = $row["M5P1"];
    $M5P2 = $row["M5P2"];
    $M5P3 = $row["M5P3"];
    
?>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                <h3 class="content-header-title mb-0 d-inline-block">Manage Permission</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?=roothtml.'home/home.php'?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Manage Permission
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-5">
                <div id="recent-transactions" class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <form id="frmsave" method="post">
                                    <input type="hidden" name="action" value="save_permission">
                                    <input type="hidden" name="aid" value="<?=$aid?>">
                                    <div class="row">
                                        <h3 class="my-2 col-12 mr-3">
                                            Order Dashboard
                                        </h3>
                                        <div class="col-4 reception">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    <?=($P1==1)?'checked':'' ?> name="P1">
                                                <label class="ml-50" for="accountSwitch1">Dashboard</label>
                                            </div>
                                        </div>
                                        <!-- ////////////////////////////////////////////// -->
                                        <?php if($M1==1){ ?><h6 class="col-12"><hr></h6> <?php } ?>
                                        <!-- //////////////////////////////////////////// -->
                                        <h3 class="my-2 col-12 mr-3" id="btnmanageaccount">
                                            Manage Account
                                            <input type="checkbox" class="switchery" data-size="xs"
                                                data-switchery="true" <?=($M1==1)?'checked':'' ?> name="M1">
                                        </h3>
                                        <div class="col-4 manageaccount"
                                            style="<?=($M1 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M1P1==1)?'checked':'' ?> name="M1P1">
                                                <label class="ml-50" for="accountSwitch1">User Control</label>
                                            </div>
                                        </div>
                                        <div class="col-4 manageaccount"
                                            style="<?=($M1 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M1P2==1)?'checked':'' ?> name="M1P2">
                                                <label class="ml-50" for="accountSwitch1">Manage Agent</label>
                                            </div>
                                        </div>
                                        <div class="col-4 manageaccount"
                                            style="<?=($M1 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M1P3==1)?'checked':'' ?> name="M1P3">
                                                <label class="ml-50" for="accountSwitch1">Manage Player</label>
                                            </div>
                                        </div>
                                        <!-- ////////////////////////////////////////////// -->
                                         <?php if($M2==1){ ?><h6 class="col-12"><hr></h6> <?php } ?>
                                        <!-- //////////////////////////////////////////// -->
                                        <h3 class="my-2 col-12 mr-3" id="btnsportbet">
                                            Sport Bet
                                            <input type="checkbox" class="switchery" data-size="xs"
                                                data-switchery="true" <?=($M2==1)?'checked':'' ?> name="M2">
                                        </h3>
                                        <div class="col-4 sportbet"
                                            style="<?=($M2 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M2P1==1)?'checked':'' ?> name="M2P1">
                                                <label class="ml-50" for="accountSwitch1">Running</label>
                                            </div>
                                        </div>
                                        <div class="col-4 sportbet"
                                            style="<?=($M2 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M2P2==1)?'checked':'' ?> name="M2P2">
                                                <label class="ml-50" for="accountSwitch1">Settled</label>
                                            </div>
                                        </div>
                                        <div class="col-4 sportbet"
                                            style="<?=($M2 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M2P3==1)?'checked':'' ?> name="M2P3">
                                                <label class="ml-50" for="accountSwitch1">Canceled</label>
                                            </div>
                                        </div>
                                        <!-- ////////////////////////////// -->
                                         <?php if($M3==1){ ?><h6 class="col-12"><hr></h6> <?php } ?>
                                        <!-- //////////////////////////////////////////// -->
                                        <h3 class="my-2 col-12 mr-3" id="btncasinobet">
                                            Casino Bet
                                            <input type="checkbox" class="switchery" data-size="xs"
                                                data-switchery="true" <?=($M3==1)?'checked':'' ?> name="M3">
                                        </h3>
                                        <div class="col-4 casinobet"
                                            style="<?=($M3 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M3P1==1)?'checked':'' ?> name="M3P1">
                                                <label class="ml-50" for="accountSwitch1">Running</label>
                                            </div>
                                        </div>
                                        <div class="col-4 casinobet"
                                            style="<?=($M3 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M3P2==1)?'checked':'' ?> name="M3P2">
                                                <label class="ml-50" for="accountSwitch1">Settled</label>
                                            </div>
                                        </div>
                                        <div class="col-4 casinobet"
                                            style="<?=($M3 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M3P3==1)?'checked':'' ?> name="M3P3">
                                                <label class="ml-50" for="accountSwitch1">Canceled</label>
                                            </div>
                                        </div>
                                        <!-- ////////////////////////////// -->
                                         <?php if($M4==1){ ?><h6 class="col-12"><hr></h6> <?php } ?>
                                        <!-- //////////////////////////////////////////// -->
                                        <h3 class="my-2 col-12 mr-3" id="btnplayer">
                                            Players
                                            <input type="checkbox" class="switchery" data-size="xs"
                                                data-switchery="true" <?=($M4==1)?'checked':'' ?> name="M4">
                                        </h3>
                                        <div class="col-4 player"
                                            style="<?=($M4 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M4P1==1)?'checked':'' ?> name="M4P1">
                                                <label class="ml-50" for="accountSwitch1">Player Balance</label>
                                            </div>
                                        </div>
                                        <div class="col-4 player"
                                            style="<?=($M4 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M4P2==1)?'checked':'' ?> name="M4P2">
                                                <label class="ml-50" for="accountSwitch1">Top-up</label>
                                            </div>
                                        </div>
                                        <div class="col-4 player"
                                            style="<?=($M4 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M4P3==1)?'checked':'' ?> name="M4P3">
                                                <label class="ml-50" for="accountSwitch1">Withdraw</label>
                                            </div>
                                        </div>
                                        <div class="col-4 player"
                                            style="<?=($M4 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M4P4==1)?'checked':'' ?> name="M4P4">
                                                <label class="ml-50" for="accountSwitch1">Wallet History</label>
                                            </div>
                                        </div>
                                        <!-- ////////////////////////////// -->
                                         <?php if($M5==1){ ?><h6 class="col-12"><hr></h6> <?php } ?>
                                        <!-- //////////////////////////////////////////// -->
                                        <h3 class="my-2 col-12 mr-3" id="btnreport">
                                            Reports
                                            <input type="checkbox" class="switchery" data-size="xs"
                                                data-switchery="true" <?=($M5==1)?'checked':'' ?> name="M5">
                                        </h3>
                                        <div class="col-4 report"
                                            style="<?=($M5 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M5P1==1)?'checked':'' ?> name="M5P1">
                                                <label class="ml-50" for="accountSwitch1">Running Report</label>
                                            </div>
                                        </div>
                                        <div class="col-4 report"
                                            style="<?=($M5 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M5P2==1)?'checked':'' ?> name="M5P2">
                                                <label class="ml-50" for="accountSwitch1">Settled Report</label>
                                            </div>
                                        </div>
                                        <div class="col-4 report"
                                            style="<?=($M5 == 1)?'display:block;':'display:none;'?>">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($M5P3==1)?'checked':'' ?> name="M5P3">
                                                <label class="ml-50" for="accountSwitch1">Canceled Report</label>
                                            </div>
                                        </div>
                                        <!-- ////////////////////////////////// -->
                                         <?php if($P2==1){ ?><h6 class="col-12"><hr></h6> <?php } ?>
                                        <!-- //////////////////////////////////////////// -->
                                        <h3 class="my-2 col-12 mr-3 ">
                                            Log History
                                        </h3>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <input type="checkbox" class="switchery" data-size="xs"
                                                    data-switchery="true" <?=($P2==1)?'checked':'' ?> name="P2">
                                                <label class="ml-50" for="accountSwitch1">Log History</label>
                                            </div>
                                        </div>
                                        <!-- ///////////////////////// -->
                                        <div
                                            class="col-12 d-flex flex-sm-row flex-column justify-content-center pt-1 border-top">
                                            <button type="submit" class="btn btn-primary mr-sm-1 mb-1 mb-sm-0">Save
                                                changes</button>
                                            <a href="<?=roothtml.'user/user.php'?>"
                                                class="btn btn-danger">Back</a>
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
</div>
<!-- END: Content-->


<?php   include(root.'master/footer.php');   ?>

<script>
var userpermission_url = "<?php echo roothtml.'user/user_permission_action.php'; ?>";

$(document).ready(function() {
    $("#frmsave").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: "post",
            url: userpermission_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                // swal(data);
                location.href = "<?php echo roothtml.'user/user.php' ?>";
            }
        });
    });

    document.querySelector("#btnmanageaccount input[type='checkbox']").addEventListener("change", function(e) {
        e.preventDefault();
        var isChecked = this.checked;
        document.querySelectorAll(".manageaccount").forEach(function(div) {
            div.style.display = isChecked ? "block" : "none";
        });
    });

    document.querySelector("#btnsportbet input[type='checkbox']").addEventListener("change", function(e) {
        e.preventDefault();
        var isChecked = this.checked;
        document.querySelectorAll(".sportbet").forEach(function(div) {
            div.style.display = isChecked ? "block" : "none";
        });
    });

    document.querySelector("#btncasinobet input[type='checkbox']").addEventListener("change", function(e) {
        e.preventDefault();
        var isChecked = this.checked;
        document.querySelectorAll(".casinobet").forEach(function(div) {
            div.style.display = isChecked ? "block" : "none";
        });
    });

    document.querySelector("#btnplayer input[type='checkbox']").addEventListener("change", function(e) {
        e.preventDefault();
        var isChecked = this.checked;
        document.querySelectorAll(".player").forEach(function(div) {
            div.style.display = isChecked ? "block" : "none";
        });
    });

    document.querySelector("#btnreport input[type='checkbox']").addEventListener("change", function(e) {
        e.preventDefault();
        var isChecked = this.checked;
        document.querySelectorAll(".report").forEach(function(div) {
            div.style.display = isChecked ? "block" : "none";
        });
    });


});
</script>