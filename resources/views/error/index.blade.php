@extends('layouts.app')

@section('title', isset($title) ? $title : 'Erreur de l\'application')

@section('content')
<div>
    <div>
        @isset($title)
            {{ $title }}
        @else
            Une erreur a été détectée !
        @endisset
    </div>
    <div>
        @isset($message)
            {{ $message }}
        @else
            Aucune précision sur l'erreur rencontrée.
        @endisset
    </div>
</div>
@endsection
