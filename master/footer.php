<!-- BEGIN: Footer-->
<footer class="footer footer-static footer-light navbar-border navbar-shadow" style="display:none;">
    <p class="clearfix blue-grey lighten-2 text-sm-center mb-0 px-2"><span
            class="float-md-left d-block d-md-inline-block">Copyright &copy; 2025 <a class="text-bold-800 grey darken-2"
                href="https://1.envato.market/modern_admin" target="_blank">PIXINVENT</a></span><span
            class="float-md-right d-none d-lg-block">Hand-crafted & Made with<i class="ft-heart pink"></i><span
                id="scroll-top"></span></span></p>
</footer>
<!-- END: Footer-->


<!-- BEGIN: Vendor JS-->
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/vendors.min.js'?>"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/charts/chartist.min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/charts/chartist-plugin-tooltip.min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/charts/raphael-min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/charts/morris.min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/timeline/horizontal-timeline.js'?>"></script>
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
<script src="<?php echo roothtml.'lib/app-assets/js/core/app-menu.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/js/core/app.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/ui/jquery.sticky.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/icheck/icheck.min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/js/scripts/forms/checkbox-radio.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/js/scripts/forms/switch.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/toggle/bootstrap-switch.min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/toggle/switchery.min.js'?>"></script>
<script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/toggle/bootstrap-checkbox.min.js'?>"></script>
<script src="<?=roothtml.'lib/app-assets/js/scripts/forms/input-groups.js'?>"></script>
<script src="<?=roothtml.'lib/app-assets/js/scripts/pages/app-todo.js' ?>"></script>
<!-- END: Theme JS-->

<!-- BEGIN: Page JS-->
<script src="<?php echo roothtml.'lib/app-assets/js/scripts/pages/dashboard-ecommerce.js'?>"></script>
<!-- END: Page JS-->
<!-- Select2 -->
<script src="<?=roothtml.'lib/app-assets/vendors/js/forms/select/select2.full.min.js'?>"></script>
<script src="<?=roothtml.'lib/app-assets/js/scripts/forms/select/form-select2.js'?>"></script>
<script>
$(document).ready(function() {
    $(document).ajaxStart(function() {
        $(".loader").show();
    });

    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });

    $(document).on("click", "#btnlogout", function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Answer?',
            text: "Are you sure Exit!",
            icon: 'info',
            showCancelButton: true,
            showCloseButton: false, // ညာဘက်‌ထောင့်မှာ x ပေါ်ချင်ရင်
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            allowEscapeKey: false,
            focusConfirm: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?php echo roothtml.'index_action.php' ?>",
                    data: {
                        action: 'logout'
                    },
                    success: function(data) {
                        if (data == 1) {
                            location.href =
                                "<?php echo roothtml.'index.php' ?>";
                        }
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Swal.fire(
                //     'Information',
                //     'Your data is safe.',
                //     'info'
                // );
            }
        });
        
    });

    function load_topup_data() {
        $.ajax({
            url: "<?php echo roothtml.'index_action.php' ?>",
            type: 'POST',
            data: {
                action: 'load_data_topup'
            },
            success: function(data) {
                $('#load_data_topup').html(data);
            },
        });
    }

    function load_withdraw_data() {
        $.ajax({
            url: "<?php echo roothtml.'index_action.php' ?>",
            type: 'POST',
            data: {
                action: 'load_data_withdraw'
            },
            success: function(data) {
                $('#load_data_withdraw').html(data);
            },
        });
    }

    let isChecking = false;

    function checkNewRequests() {
        if (isChecking) return;

        isChecking = true;
        $.ajax({
            url: "<?php echo roothtml.'index_action.php' ?>",
            type: 'POST',
            data: {
                action: 'check_topup'
            },
            success: function(data) {
                $('.show_topup').text(data);
                if (data > 0) {
                    $('.show_topup').show();
                    $('.show_topupnoti').show();
                } else {
                    $('.show_topup').hide();
                    $('.show_topupnoti').hide();
                }
                load_topup_data();
            },
            complete: function() {
                isChecking = false;
            }
        });
    }
    checkNewRequests();
    setInterval(checkNewRequests, 5000);

    let isCheckingWithdraw = false;

    function checkWithdrawRequests() {
        if (isCheckingWithdraw) return;

        isCheckingWithdraw = true;
        $.ajax({
            url: "<?php echo roothtml.'index_action.php' ?>",
            type: 'POST',
            data: {
                action: 'check_withdraw'
            },
            success: function(data) {
                $('.show_withdraw').text(data);
                if (data > 0) {
                    $('.show_withdraw').show();
                    $('.show_withdrawnoti').show();
                } else {
                    $('.show_withdraw').hide();
                    $('.show_withdrawnoti').hide();
                }
                load_withdraw_data()
            },
            complete: function() {
                isCheckingWithdraw = false;
            }
        });
    }
    checkWithdrawRequests();
    setInterval(checkWithdrawRequests, 5000);

});
</script>

</body>
<!-- END: Body-->

</html>