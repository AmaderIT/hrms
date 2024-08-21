<center><h5>Employee Departmental Movement History</h5></center>

<table class="table table-responsive-lg dataTable no-footer" role="grid"
       aria-describedby="employeeUnpaidLeave_info">
    <thead class="custom-thead">
    <tr role="row">
        <th>#</th>
        <th>Division</th>
        <th>Department</th>
        <th>Date</th>
        <th>Movement Type</th>
        <th>Employment Type</th>
        <th>Designation</th>
        <th>PayGrade</th>
        <th>WorkSlot</th>
    </tr>
    </thead>

    <tbody id="tbl-data">
    </tbody>
</table>


<script>
    var emp_id = 0;

    function loadTransferHistory() {
        $.ajax({
            url: '{{route("transfer.history")}}',
            type: 'POST',
            data: {
                emp_id: emp_id
            },
            success: function (res) {
                var data = '';
                $('#progress-num').empty();

                if (res.result.length > 0) {
                    $.each(res.result, function (x, y) {
                        data += '<tr>';
                        data += '<td>' + (x + 1) + '</td>';
                        data += '<td>' + y.office_division.name + '</td>';
                        data += '<td>' + y.department.name + '</td>';
                        data += '<td>' + y.transfer_date + '</td>';
                        data += '<td>' + y.type + '</td>';
                        data += '<td>' + y.employment_type + '</td>';
                        data += '<td>' + y.designation.title + '</td>';
                        data += '<td>' + y.pay_grade.name + '</td>';
                        data += '<td>' + y.work_slot.title + '</td>';
                        data += '</tr>';
                    });
                } else {
                    data += '<tr>';
                    data += '<td></td>';
                    data += '<td></td>';
                    data += '<td class="no-data-found" colspan="4" >Previous transfer history was not found!</td>';
                    data += '</tr>';
                }


                $('#tbl-data').html(data)
            },
            error: function (err) {
                console.log(err)
            }
        })
    }


</script>

