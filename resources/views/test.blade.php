@extends('layout')

@section('title')
{{ $test->name }}
@stop

@section('content')
<h1>{{ $test->name }}</h1>
@stop