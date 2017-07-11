@extends('layout')

@section('title')
Тесты ЕГЭ по биологии
@stop

@section('content')
<h1>Результаты</h1>
@foreach ($userTests as $userTest)
<a href="{{ route('test', $userTest->test_id) }}">{{ $userTest->name }}</a><br>
{{ $userTest->created_at->format('d.m.Y H:i:s') }}<br>
@if ($userTest->complete)
Завершен<br>
@else
В процессе<br>
@endif
<br>
@endforeach
@stop