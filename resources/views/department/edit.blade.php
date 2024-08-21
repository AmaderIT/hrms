@extends('layouts.app')

@section('top-css')
    <style type="text/css">
        .warehouse_id_div {
            margin-top: -8px;
        }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
        }
        .select2-selection__clear {
            margin-right: 0 !important;
        }
    </style>
@endsection

@section('content')
    @include('department.common-view.common_edit')
@endsection

@section('footer-js')
    <script>
        var length = 0;

        function onChangeCheckBox() {
            var chk_arr =  document.querySelectorAll("input[name='days[]']:checked");
            length = chk_arr.length;
        }

        function submitCheck(event) {
            if(length <= 0) {
                event.preventDefault();
                notify().error("You have to check at least one weekly holiday.")
            }
        }


        $(document).ready( function () {
            $('#is_warehouse').on('click', function () {
                if(Number($(this).val()) == 0){
                    $(this).val(1);
                    $(this).prop("checked", true);
                    $('#warehouse_id').removeClass('disabled');
                    $('.warehouse_id_div').removeClass('d-none');
                }else{
                    $(this).val(0);
                    $(this).prop("checked", false);
                    $('#warehouse_id').addClass('disabled');
                    $('.warehouse_id_div').addClass('d-none');
                }
            });
        });

        $(document).ready( function () {
            const $relaxCheckBox = $('input[name=is_relax_day_setting]');
            const $relaxWrap = $('.enable_depand_fileds_wrap');

            if($relaxCheckBox.is(':checked')) $relaxWrap.slideDown("slow");
            else {
                $relaxWrap.slideUp("hide");
                $('select[name=relax_day_type] option[value=""]').prop('selected', true);
                $('select[name=max_count_per_month] option[value=""]').prop('selected', true);
                $('#weekly_days').val('').trigger("change");
            }

            $relaxCheckBox.change(function() {
                if($(this).is(':checked')) $relaxWrap.slideDown("slow");
                else {
                    $relaxWrap.slideUp("hide");
                    $('select[name=relax_day_type] option[value=""]').prop('selected', true);
                    $('select[name=max_count_per_month] option[value=""]').prop('selected', true);
                    $('#weekly_days').val('').trigger("change");
                }
            });
        });

        $("#weekly_days").select2({
            placeholder: "Select day(s) of the week ...",
            width:'100%',
            allowClear: true
        });
    </script>

@endsection
