@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3"><i class="fas fa-fw fa-database"></i> {{ __('Database') }}</h1>
        </div>
    </div>
    <form method="post" action="/database/save">
        @csrf
        <input type="hidden" name="id" value="{{ $database->id }}">
        <div class="row form-group">
            <div class="col">
                <label>{{ __('Name') }}</label>
                <input type="text" name="name" id="name" value="{{ $database->name }}" required maxlength="50" class="form-control">
            </div>
            <div class="col">
                <label class="">{{ __('Type') }}</label>
                <select name="type" required class="form-control">
                    <option value=""></option>
                    <option value="mysql">MySQL/MariaDB</option>
                    <option value="postgres">PostgreSQL</option>
                </select>
            </div>
        </div>
        <div class="row form-group">
            <div class="col">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="text">Guardar</span>
                    <span class="icon text-white-50">
                        <i class="far fa-save"></i>
                    </span>
                </button>
            </div>
        </div>
    </form>
</div><!--./container-->
@endsection
