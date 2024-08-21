<table class="customTbale table table-bordered">
    <thead>
    <tr class="month-class">
        <td colspan="7">{{$current_month_name.', '.$current_year}}</td>
    </tr>
    </thead>
    <thead>
    <tr class="day-class">
        <td>Sat</td>
        <td>Sun</td>
        <td>Mon</td>
        <td>Tue</td>
        <td>Wed</td>
        <td>Thu</td>
        <td>Fri</td>
    </tr>
    </thead>
    <tbody>
    @php
        $count=0;
        $current_date_second = strtotime($current_date);
    @endphp
    @for($i=1;$i<=$days;$i++)
        @php
            $count++;
            $new_date = $current_year_month.'-'.str_pad($i,2,'0',STR_PAD_LEFT);
            $new_date_second = strtotime($new_date);
        @endphp
        @if($count==1)
            <tr>
        @endif
        @if($i==1 && $first_day_number>$count)
            @for($j=1;$j<$first_day_number;$j++)
                <td></td>
                @php
                    $count++;
                @endphp
            @endfor
        @endif
        @if(isset($public_holidays[$i]) || isset($relax_days[$i]))
            <td @if($current_date_second<=$new_date_second) class="pointer-td" @endif id="date-td-id_{{$i}}" data-day-value="{{$i}}">
                <div class="day text-center @if($count==7) red-color @endif">{{$i}}</div>
                <div class="instraction">
                    @isset($public_holidays[$i])
                        <div class="public p-0" data-holiday-id="{{$public_holidays[$i]['holiday_id']}}">{{$public_holidays[$i]['holiday_name']}}</div>
                    @endisset
                    @isset($relax_days[$i])
                        <div class="relax p-0">Relax Day</div>
                    @endisset
                </div>
            </td>
        @else
            <td @if($current_date_second<=$new_date_second) class="pointer-td" @endif id="date-td-id_{{$i}}" data-day-value="{{$i}}">
                <div class="day text-center @if($count==7) red-color @endif">{{$i}}</div>
            </td>
        @endif
        @if($i==$days && $count<7)
            @for($j=1;$count<7;$j++)
                <td></td>
                @php
                    $count++;
                @endphp
            @endfor
        @endif
        @if($count==7)
            </tr>
            @php
                $count=0;
            @endphp
        @endif
    @endfor

    </tbody>
</table>

<ul>
    @foreach($monthly_holiday as $holiday_type)
        @foreach($holiday_type as $type=>$holiday_specific)
            @if($type=='public')
                <li><span class="public">{{$holiday_specific['name']}}</span> : <b>{{$holiday_specific['date']}}</b></li>
            @else
                <li><span class="relax">{{$holiday_specific['name']}}</span> : <b>{{$holiday_specific['date']}}</b></li>
                @if(!empty($holiday_specific['note'])) <span style="font-size: 12px"><b>Note</b> : {{$holiday_specific['note']}}</span> @endif
            @endif
        @endforeach
    @endforeach
</ul>
