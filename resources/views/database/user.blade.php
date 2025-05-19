@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3"><i class="fas fa-fw fa-user"></i> {{ __('Database User') }}</h1>
        </div>
    </div>
    <form method="post" action="/database/save-user">
        @csrf
        <input type="hidden" name="id" value="{{ $user->id ?? '' }}">
        <div class="row form-group">
            <div class="col">
                <label for="username">{{ __('Username') }}</label>
                <input type="text" name="username" id="username" value="{{ $user->username ?? '' }}" required maxlength="32" class="form-control">
            </div>
            <div class="col">
                <label for="host">{{ __('Host') }}</label>
                <input type="text" name="host" id="host" value="{{ $user->host ?? 'localhost' }}" required maxlength="64" class="form-control">
            </div>
        </div>
        <div class="row form-group">
            <div class="col">
                <label for="password">{{ __('Password') }}</label>
                <input type="password" name="password" id="password" class="form-control" {{ empty($user) ? 'required' : '' }}>
            </div>
            <div class="col">
                <label for="confirm_password">{{ __('Confirm Password') }}</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" {{ empty($user) ? 'required' : '' }}>
            </div>
        </div>
        <div class="row form-group">
            <div class="col">
                <label for="privileges">{{ __('Privileges') }}</label>
                <select name="privileges" id="privileges" class="form-control">
                    <option value="ALL">ALL</option>
                    <option value="SELECT">SELECT</option>
                    <option value="INSERT">INSERT</option>
                    <option value="UPDATE">UPDATE</option>
                    <option value="DELETE">DELETE</option>
                </select>
            </div>
            <div class="col">
                <label for="database">{{ __('Database') }}</label>
                <input type="text" name="database" id="database" value="{{ $user->database ?? '' }}" class="form-control" placeholder="Opcional: base de datos específica">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="/database/users" class="btn btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection
