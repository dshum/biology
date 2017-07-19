@extends('layout')

@section('title')
Профиль
@stop

@section('content')
<h1>{{ $user->email }}</h1>
@if (isset($errors) && $errors->any())
    <div class="errors">
    @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    </div>
@endif
<form action="{{ route('profile') }}" method="post" autocomplete="false">
    {{ csrf_field() }}
    <div class="row">
        <label>Имя:</label><br>
        <input type="text" name="first_name" value="{{ $first_name }}">
    </div>
    <div class="row">
        <label>Фамилия:</label><br>
        <input type="text" name="last_name" value="{{ $last_name }}">
    </div>
    <div class="row">
        <label>Новый пароль:</label><br>
        <input type="password" name="password" value="">
    </div>
    <div class="row">
        <label>Подтверждение:</label><br>
        <input type="password" name="confirmation" value="">
    </div>
    <div class="row submit">
        <input type="submit" value="Сохранить" class="btn">
    </div>
</form>
@stop