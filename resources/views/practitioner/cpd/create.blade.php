@extends('layouts.app', ['title' => $title])

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-6" 
         x-data="profileManager()" 
         x-init="init()">
<div class="space-y-6">
<div class="space-y-6">
    @include('practitioner.cpd.partials.create.header')

    <!-- Form Section -->
    <form action="{{ route('practitioner.cpd.store') }}" method="POST" class="space-y-6">
        @csrf
        
        @include('practitioner.cpd.partials.create.activity-info')

        @include('practitioner.cpd.partials.create.points-evidence')

        @include('practitioner.cpd.partials.create.description')

        @include('practitioner.cpd.partials.create.actions')
    </form>
</div>
</div>
@endsection
