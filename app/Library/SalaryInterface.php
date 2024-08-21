<?php

namespace App\Library;

interface SalaryInterface
{
    public function employeeEarnings();

    public function employeeDeductions();

    public function employeeTaxableAmounts();

    public function employeeLoans();

    public function employeeBonus();

    public function employeeAttendances();

}
