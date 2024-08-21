@include('department.common-view.common_create')
<script>
    let length = 0;
    function onChangeCheckBox() {
        let chk_arr = document.querySelectorAll("input[name='days[]']:checked");
        length = chk_arr.length;
    }
    function submitCheck(event) {
        if (length <= 0) {
            event.preventDefault();
            notify().error("You have to check at least one weekly holiday.")
        }
    }
    $("#datepicker").datepicker({
        format: "yyyy",
        startView: "years",
        minViewMode: "years"
    });
</script>
<script>
    $(document).ready(function () {
        $('#is_warehouse').on('click', function () {
            if (Number($(this).val()) == 0) {
                $(this).val(1);
                $(this).prop("checked", true);
                $('#warehouse_id').removeClass('disabled');
                $('.warehouse_id_div').removeClass('d-none');
            } else {
                $(this).val(0);
                $('#warehouse_id').val('');
                $(this).prop("checked", false);
                $('#warehouse_id').addClass('disabled');
                $('.warehouse_id_div').addClass('d-none');
            }
        });
    });
</script>
