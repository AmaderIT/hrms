<div class="col-lg-5 col-md-5 col-md-12 requested-leave-summary-card">
    <h5 class="text-center" style="text-decoration: underline;">Employee Information</h5>
    <table style="width: 100%;">
        <tr>
            <td width="30%" class="requested-leave-summary-card-font-weight">Employee ID
            </td>
            <td width="70%">
                : {{ !empty($getEmployeeInfos->fingerprint_no)?$getEmployeeInfos->fingerprint_no:"" }}</td>
        </tr>
        <tr>
            <td width="30%" class="requested-leave-summary-card-font-weight">Name</td>
            <td width="70%">
                : {{ !empty($getEmployeeInfos->name)?$getEmployeeInfos->name:"" }}</td>
        </tr>
        <tr>
            <td width="30%" class="requested-leave-summary-card-font-weight">Designation</td>
            <td width="70%">
                : {{ optional($getEmployeeInfos->currentPromotion->designation)->title }}</td>
        </tr>
        <tr>
            <td width="30%" class="requested-leave-summary-card-font-weight">Division</td>
            <td width="70%">
                : {{ !empty($getEmployeeInfos->currentPromotion->officeDivision->name)?$getEmployeeInfos->currentPromotion->officeDivision->name:"" }}</td>
        </tr>
        <tr>
            <td width="30%" class="requested-leave-summary-card-font-weight">Department</td>
            <td width="70%">
                : {{ !empty($getEmployeeInfos->currentPromotion->department->name)?$getEmployeeInfos->currentPromotion->department->name:"" }}</td>
        </tr>
        <tr>
            <td width="30%" class="requested-leave-summary-card-font-weight">Joining Date
            </td>
            <td width="70%">
                : {{ \Carbon\Carbon::createFromDate($getEmployeeInfos->employeeStatus()->where("action_reason_id", 2)->orderByDESC('action_date')->first()->action_date->toDateTimeString())->format("M d, Y") }}</td>
        </tr>
    </table>
    @include('requested-application.employee-leave-graph')
</div>
