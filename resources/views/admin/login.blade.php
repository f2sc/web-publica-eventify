@extends('layouts.admin')

@section('title', 'Acceso CMS')

@section('content')
<div class="login-wrapper">
    <div class="login-box">
        <h1>Eventify CMS</h1>
        <p>Inicia sesión con tu cuenta de administrador.</p>

        @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control"
                       required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem">
                Entrar
            </button>
        </form>
    </div>
</div>
@endsection
