@extends('layouts.app')

@section('title', 'Import de relevés bancaires')

@section('content')
    <div class="form-container">
        <h1>Import de relevé bancaire</h1>

        @if(session('success'))
            @include('components.alerts.success', ['message' => session('success')])
        @endif

        @if(session('error'))
            @include('components.alerts.error', ['message' => session('error')])
        @endif

        @if(!isset($incomes))
            @include('imports.form')
        @else
            @include('imports.preview', ['incomes' => $incomes, 'incomeTypes' => $incomeTypes])
        @endif
    </div>
@endsection
