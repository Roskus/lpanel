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
        <input type="hidden" name="id" value="">
        <div class="form-group row">
            <div class="col">
                <input type="text" name="url" value="{{ $website->url }}" placeholder="site.com" required="required" class="form-contol">
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <select name="type" required="requied" class="form-control">
                    <option value="">Type</option>
                    <option value="nginx">Nginx</option>
                    <option value="apache">Apache</option>
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
