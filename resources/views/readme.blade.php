@extends('layouts.app')

@section('content')

<style>
    .readme {
        font-size: 20px;
    }
</style>

@php
    use App\Models\TaxRule;
    $taxRulesPerMonth = array();
    $taxRatesPerMonth = array();
    $temp = 0;

    foreach ($tax->rules->groupBy("gender")[TaxRule::GENDER_MALE] as $taxRule)
    {
        if($taxRule->slab != TaxRule::SLAB_REMAINING) {
            $temp += $taxRule->slab / 12;
            array_push($taxRulesPerMonth, $temp);
            array_push($taxRatesPerMonth, $taxRule->rate);
        } elseif($taxRule->slab == TaxRule::SLAB_REMAINING) {
            array_push($taxRulesPerMonth, PHP_INT_MAX);
            array_push($taxRatesPerMonth, $taxRule->rate);
        }
    }

    // var_dump($taxRulesPerMonth);
    // echo "<br/>";
    // var_dump($taxRatesPerMonth);
    // exit();

    // $taxRulesPerMonth = array_reverse($taxRulesPerMonth);
    // $taxRatesPerMonth = array_reverse($taxRatesPerMonth);

    // dd($taxRulesPerMonth);
@endphp

<div class="readme">
    <h2>Tax info</h2>
    <hr/>
    <p>
        <b>Active Tax:</b> {{ $tax->name }}
    </p>
    <p>
        <b>Eligible Amount of Rebate:</b> {{ $tax->eligible_rebate }}%
    </p>
    <p>
        <b>Tax Rebate (Eligible Amount):</b> {{ $tax->tax_rebate }}%
    </p>
    <p>
        <b>Minimum Tax Amount:</b> {{ $tax->min_tax_amount }}
    </p>
    <p>
        <b>Tax Status:</b> {{ $tax->status == \App\Models\Tax::STATUS_ACTIVE ? "Active" : "Inactive" }}
    </p>

    <hr/>

    <h2>Tax Rules</h2>
    <hr/>

    <h3>Male</h3>
    @php
        $taxRulesPerMonth = array();
        $taxRatesPerMonth = array();
        $temp = 0;
        $previous = -1;

        foreach ($tax->rules->groupBy("gender")[TaxRule::GENDER_MALE] as $taxRule)
        {
            // $previous +=  +1;
            if($taxRule->slab != TaxRule::SLAB_REMAINING) {
                $temp += $taxRule->slab / 12;
                array_push($taxRulesPerMonth, $temp);
                array_push($taxRatesPerMonth, $taxRule->rate);
            } elseif($taxRule->slab == TaxRule::SLAB_REMAINING) {
                array_push($taxRulesPerMonth, 111);
                array_push($taxRatesPerMonth, $taxRule->rate);
            }
        }
    @endphp

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Range</th>
                <th>Amount</th>
                <th>Taka</th>
            </tr>
        </thead>
        <tbody>
        @foreach($taxRulesPerMonth as $key => $rule)
            <tr>
                <td>
                    {{ $key != 0 ? round($taxRulesPerMonth[$key-1] + 1, 2) : 0 }} -
                    {{ $taxRulesPerMonth[$key] != \App\Models\TaxRule::SLAB_REMAINING ? round($taxRulesPerMonth[$key], 2) : "Remaining" }}
                </td>
                <td>{{ $taxRatesPerMonth[$key] == 0 ? "Nil" : $taxRatesPerMonth[$key] }}%</td>
                @if($taxRulesPerMonth[$key] == \App\Models\TaxRule::SLAB_REMAINING)
                    <td>Not defined</td>
                @else
                    <td>{{ round(($taxRulesPerMonth[$key] - $taxRulesPerMonth[($key - 1) < 0 ? 0 : $key-1]) * ($taxRatesPerMonth[$key] / 100), 2) }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr/>

    <h3>Female</h3>
    @php
        $taxRulesPerMonth = array();
        $taxRatesPerMonth = array();
        $temp = 0;
        $previous = -1;

        foreach ($tax->rules->groupBy("gender")[TaxRule::GENDER_FEMALE] as $taxRule)
        {
            // $previous +=  +1;
            if($taxRule->slab != TaxRule::SLAB_REMAINING) {
                $temp += $taxRule->slab / 12;
                array_push($taxRulesPerMonth, $temp);
                array_push($taxRatesPerMonth, $taxRule->rate);
            } elseif($taxRule->slab == TaxRule::SLAB_REMAINING) {
                array_push($taxRulesPerMonth, 111);
                array_push($taxRatesPerMonth, $taxRule->rate);
            }
        }
    @endphp

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Range</th>
            <th>Amount</th>
            <th>Taka</th>
        </tr>
        </thead>
        <tbody>
        @foreach($taxRulesPerMonth as $key => $rule)
            <tr>
                <td>
                    {{ $key != 0 ? round($taxRulesPerMonth[$key-1] + 1, 2) : 0 }} -
                    {{ $taxRulesPerMonth[$key] != \App\Models\TaxRule::SLAB_REMAINING ? round($taxRulesPerMonth[$key], 2) : "Remaining" }}
                </td>
                <td>{{ $taxRatesPerMonth[$key] == 0 ? "Nil" : $taxRatesPerMonth[$key] }}%</td>
                @if($taxRulesPerMonth[$key] == \App\Models\TaxRule::SLAB_REMAINING)
                    <td>Not defined</td>
                @else
                    <td>{{ round(($taxRulesPerMonth[$key] - $taxRulesPerMonth[($key - 1) < 0 ? 0 : $key-1]) * ($taxRatesPerMonth[$key] / 100), 2) }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
