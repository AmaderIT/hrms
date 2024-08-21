<style>
    #assignee_table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    #assignee_table td, th {
        text-align: left;
        padding: 8px;
    }

    #assignee_table .department_info {
        background-color: #dddddd;
    }
</style>
@php
    $a = 0;
    $number = count($employees);
@endphp
@if($number>0)
    <table id="assignee_table" class="table table-bordered">
        <thead>
        <tr>
            <th>Employee</th>
            <th>Email</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($employees as $employee)
            <tr class="">
                <td>{{$employee->fingerprint_no.'-'.$employee->name}}</td>
                <td>{{$employee->email}}</td>
                <td style="{{($employee->approval_status==\App\Models\AssignRelaxDay::APPROVAL_PENDING) ? 'color:rgb(255, 204, 0);' : 'color:rgb(40, 167, 69);'}}">{{ ($employee->approval_status==\App\Models\AssignRelaxDay::APPROVAL_PENDING) ? 'Pending' : 'Approved' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <h5 class="">No Employee found!</h5>
@endif
<script>
    $(document).ready( function () {
        $('#relax_date_label').html('{!! $relax_date_label !!}');
    });
</script>
