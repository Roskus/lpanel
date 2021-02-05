@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3"><i class="fas fa-user"></i> {{ __('User') }}</h1>
        </div>
    </div>

    <form method="post" action="/user/save">
        @csrf
        <input type="hidden" name="id" value="{{ $user->id }}">
        <div class="row form-group">
            <label>{{ __('Name') }}</label>
            <input type="text" name="name" id="name" value="{{ $user->name }}" required maxlength="50" class="form-control">
        </div>
        <div class="row form-group">
            <label>E-mail</label>
            <input type="email" name="email" id="email" value="{{ $user->email }}" required maxlength="254" class="form-control">
        </div>
        <div class="row form-group">
            <button type="submit" class="btn btn-primary btn-icon-split">
                <span class="text">{{ __('Save') }}</span>
                <span class="icon text-white-50">
                    <i class="far fa-save"></i>
                </span>
            </button>
        </div>
    </form>
</div>
@endsection
