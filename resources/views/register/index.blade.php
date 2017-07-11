@extends('layout')

@section('title')
Регистрация
@stop

@section('content')
@if (isset($errors) && $errors->any())
    <div class="errors">
    @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    </div>
@endif
<form action="{{ route('register') }}" method="post" autocomplete="false">
    {{ csrf_field() }}
    <div class="row">
        <label>E-mail:</label><br>
        <input type="text" name="email" value="{{ $email }}">
    </div>
    <div class="row">
        <label>Пароль:</label><br>
        <input type="password" name="password" value="{{ $password }}">
    </div>
    <div class="row">
        <label>Имя:</label><br>
        <input type="text" name="first_name" value="{{ $first_name }}">
    </div>
    <div class="row">
        <label>Фамилия:</label><br>
        <input type="text" name="last_name" value="{{ $last_name }}">
    </div>
    <div class="row submit">
        <input type="submit" value="Зарегистрироваться" class="btn">
    </div>
</form>
@stop