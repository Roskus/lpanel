@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3">{{ __('Profile') }}</h1>
        </div>
    </div>
    <form method="post" action="/profile/save">
    @csrf
    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
        <label for="name" class="col-md-4 control-label">{{ __('Name') }}</label>

        <div class="col-md-6">
            <input id="name" type="text" class="form-control" name="name" value="{{ $user->name }}" required autofocus>

            @if ($errors->has('name'))
                <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="col-md-4 control-label">E-Mail</label>

        <div class="col-md-6">
            <input id="email" type="email" class="form-control" name="email" value="{{ $user->email }}" maxlength="254" required>

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password" class="col-md-4 control-label">{{ __('Password') }}</label>

        <div class="col-md-6">
            <input type="password" name="password" id="password" value="" class="form-control" autocomplete="off">

            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group">
        <label for="password-confirm" class="col-md-4 control-label">{{ __('Confirm Password') }}</label>

        <div class="col-md-6">
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="off">
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4  text-right">
            <button type="submit" class="btn btn-primary">{{ __('Save' ) }} <i class="fas fa-save"></i></button>
        </div>
    </div>
    </form>
</div><!--./container-->
@endsection
