@props(['entryPoint' => null, 'title' => '', 'clean' => false])
@extends('layouts.index', ['entryPoint' => $entryPoint, 'clean' => $clean])
@section('head')
    <title>{{$title}}</title>
@endsection
@section('content')
    {{$slot}}
@endsection