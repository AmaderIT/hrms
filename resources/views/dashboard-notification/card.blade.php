<div class="col-xl-{{$card_width ?? ''}} dashboard-card">
    <!--begin::Stats Widget 1-->
    <div class="card card-custom card-stretch gutter-b">
        <!--begin::Header-->
        <div class="card-header border-0 pt-6">
            <h3 class="card-title">
                <span class="card-label text-dark-75" style="font-size: 15px"> {{$card_title ??  ''}}</span>
            </h3>
        </div>
        <div
            class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
            <div class="progress-vertical w-350px ml-25">
                <div class="display2 py-0 pl-22 text-primary">
                    <a class="count_data-{{$card_key ??  ''}}-{{$room ??  ''}}" href="#"></a>
                </div>
            </div>
        </div>
    </div>
    <!--end::Stats Widget 1-->
</div>


<script>
    setTimeout(function () {
        $.ajax({
            url: '{{route("dashboard-notification.get-data")}}',
            type: 'GET',
            data: {
                card_title: '{{$card_title ?? ''}}',
                card_key: '{{$card_key ?? ''}}',
                permission_key: '{{$permission_key?? ''}}',
                room: '{{$room?? ''}}'
            },
            success: function (res) {
                let cardTag = '.count_data-' + '{{$card_key?? ""}}-{{$room?? ""}}';
                $(cardTag).attr('href', res.data.url)
                $(cardTag).html(res.data.count)
            },
            error: function (err) {
                console.log(err)
            }
        })
    }, 2000)

</script>

