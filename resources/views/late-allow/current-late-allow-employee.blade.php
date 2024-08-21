<center><h5>Late Allowed Employee List</h5></center>

<table class="table table-responsive-lg dataTable no-footer" role="grid"
       aria-describedby="employeeUnpaidLeave_info">
    <thead class="custom-thead">
    <tr role="row">
        <th>#</th>
        <th>Name</th>
        <th>Designation</th>
        <th>No Of Allowed Late</th>
        <th>Allowed Date</th>
        <th>Allowed By</th>
        <th>Action</th>
    </tr>
    </thead>

    <tbody id="tbl-data">
    </tbody>
</table>
<div style="display: none;" id="data-not-found">
    <center><span>Data Not Found!</span></center>
</div>


<script>

    function loadHistory(key, value) {
        $('#allow').val("")
        $('#data-not-found').hide()
        $.ajax({
            url: '{{route("late-allow.history")}}',
            type: 'POST',
            data: {
                [key]: value
            },
            success: function (res) {
                var data = '';

                $('#tbl-data').empty();

                if (res.result.length > 0) {

                    $.each(res.result, function (x, y) {
                        data += '<tr>';
                        data += '<td>' + (x + 1) + '</td>';
                        data += '<td>' + y.employee.name + '</td>';
                        data += '<td>' + y.employee.current_promotion.designation.title + '</td>';
                        data += '<td>' + y.allow + '</td>';
                        data += '<td>' + y.allowed_date + '</td>';
                        data += '<td>' + y.allowed_by.name + '</td>';
                        data += '<td>' + y.actions + '</td>';
                        data += '</tr>';

                        if (key == 'user_id' && y.is_active == 1) {
                            $('#allow').val(y.allow);
                        }
                    });

                } else {
                    $('#data-not-found').show()
                }


                $('#tbl-data').html(data)
            },
            error: function (err) {
                console.log(err)
            }
        })
    }

    function showDetails(t) {

        $('#tbl-data-transaction').html("")

        var user_id = $(t).data('user-id');

        $.ajax({
            url: '{{route('late-allow.get-details')}}',
            type: 'POST',
            data: {
                user_id: user_id
            },
            success: function (res) {
                var data = '';
                var active_status = ["In Active", "Active"];
                var style = "";

                $.each(res.result, function (x, y) {
                    style = 'style="color: #d15151;"';
                    if (y.is_active == 1) {
                        style = 'style="color: #48bd87;"'
                    }
                    data += '<tr>';
                    data += '<td>' + (x + 1) + '</td>';
                    data += '<td ' + style + '>' + active_status[y.is_active] + '</td>';
                    data += '<td>' + y.allow + '</td>';
                    data += '<td>' + y.allowed_date + '</td>';
                    data += '<td>' + y.allowed_by.name + '</td>';

                    if (y.replaced_date != null) {
                        data += '<td>' + y.removed_by.name + '</td>';
                        data += '<td>' + y.replaced_date + '</td>';
                    } else {
                        data += '<td></td>';
                        data += '<td></td>';
                    }
                    data += '</tr>';

                    $('#name-t').html(y.employee.name)
                    $('#designation-t').html(y.employee.current_promotion.designation.title)

                });

                $('#tbl-data-transaction').html(data)
            },
            error: function (res) {
                console.log(res)
            }
        })
    }
    var edit_flag = 0;

    function edit(t) {
        edit_flag = 1
        var user_id = $(t).data('user-id')
        var allow_value = $(t).data('allow-value')
        $('#user_id').val(user_id).change()
        $('#allow').val(allow_value)
        edit_flag = 0
    }


</script>

