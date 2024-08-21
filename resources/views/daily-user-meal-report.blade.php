<html>
    <head>
        <title>BYSL meal Management</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="shortcut icon" href="{{ asset('assets/media/logos/bysl_favicon.ico') }}" />
    </head>

    <body>
        <div class="container">
            <header class="d-flex justify-content-between align-items-center">
                <div>
                    <img style="height: 110px; width: auto;" src="{{ asset('assets/media/logos/bysl_favicon.ico') }}" />
                </div>

                <h3>
                    Date: {{ \Carbon\Carbon::now()->isoFormat('Do MMM, YYYY') }}
                </h3>
            </header>

            <div class="h2 mb-5 pb-3" role="alert">
                Total Meal Consumers Today: <span class="h1">{{ $totalMealConsumerToday }} Persons</span>
            </div>

            <div>
                <p class="h4 mb-2">Cancel meal request by:</p>
            </div>
            <div class="row">
                @forelse($activeMealConsumersNotTakingMeal as $user)
                    @php $image = $user->employee->getPhotoAttribute($user->employee->photo); @endphp
                    <div class="col-2 mb-2">
                        <div class="card h-100">
                            <img class="card-img-top img-fit m-auto" src="{{ $image  }}" alt="Card image cap" style="height: auto; width: 120px">
                            <div class="card-body">
                                <h5 class="card-title">ID: {{ $user->employee->fingerprint_no  }}</h5>
                                <p class="card-text">Name: {{ $user->employee->name  }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <h1 class="m-auto py-5">No one cancelled their meal today</h1>
                @endforelse
            </div>
        </div>
    </body>
</html>



