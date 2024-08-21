@extends('layouts.app')
@section('content')
    @can('View Dashboard Policy Card')
        @include('policy.policy-dashboard')
    @endif
@endsection


