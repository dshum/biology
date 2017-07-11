@extends('layout')

@section('title')
ЕГЭ по биологии
@stop

@section('content')
@if (Auth::check())
    @foreach ($subjects as $subject)
    <h2>{{ $subject->name }}</h2>
        @foreach ($topics as $topic)
            @if ($topic->subject_id == $subject->id)
                <h3>{{ $topic->name }}</h3>
                @foreach ($subtopics as $subtopic)
                    @if ($subtopic->topic_id == $topic->id)
                        <h3>{{ $subtopic->name }}</h3>
                        @foreach ($tests as $test)
                            @if ($test->subtopic_id == $subtopic->id)
                                <a href="{{ route('test', ['id' => $test->id]) }}">{{ $test->name }}</a><br>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                @foreach ($tests as $test)
                    @if ($test->topic_id == $topic->id)
                        <a href="{{ route('test', ['id' => $test->id]) }}">{{ $test->name }}</a><br>
                    @endif
                @endforeach
            @endif
        @endforeach
    @endforeach
@else
<form action="{{ route('login') }}" method="post">
    {{ csrf_field() }}
    <div class="row">
        <label>E-mail:</label><br>
        <input type="text" name="email" value="">
    </div>
    <div class="row">
        <label>Пароль:</label><br>
        <input type="password" name="password" value="">
    </div>
    <div class="row">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember">Запомнить меня</label>
    </div>
    <div class="row submit">
        <input type="submit" value="Войти" class="btn">
    </div>
</form>
@endif
@stop