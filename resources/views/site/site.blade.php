@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3">Website</h1>
        </div>
    </div>
    <div class="row">
        <form method="post" action="/site/save" class="form">
        @csrf
        <input type="hidden" name="id" value="{{ $website->id }}">
        <div class="form-group row">
            <div class="col">
                <label>URL</label>
                <input type="text" name="url" value="{{ $website->url }}" placeholder="site.com" required="required" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <label>{{ __('Type') }}</label>
                <select name="type" required="requied" class="form-control">
                    <option value=""></option>
                    <option value="nginx" @if ($website->type == 'nginx') selected="selected" @endif>Nginx</option>
                    <option value="apache" @if ($website->type == 'apache') selected="selected" @endif>Apache</option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="text">{{ __('Save') }}</span>
                    <span class="icon text-white-50">
                        <i class="far fa-save"></i>
                    </span>
                </button>
            </div>
        </div>
        </form>
    </div><!--./row-->
</div>
@endsection
