@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3">Website</h1>
        </div>
    </div>

    @if ($errors->any())
    <div class="row">
        <div class="col">
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <form method="post" action="/site/save">
        @csrf
        <input type="hidden" name="id" value="{{ $website->id }}">
        <div class="form-group row">
            <div class="col">
                <label>URL</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">https://</div>
                    </div>
                    <input type="text" name="url" value="{{ $website->url }}" placeholder="site.com" required="required" class="form-control">
                </div>
                <div class="col">
                    <label class="">Alias (One per line)</label>
                    <div class="">
                        <textarea name="alias" class="form-control">{{ !empty($website->alias) ? implode("\n", json_decode($website->alias)) : '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="form-check">
                    <input type="checkbox" name="protocols[http]" id="http" value="Y" @if($website->http) checked="checked" @endif class="form-check-input">
                    <label class="form-check-label" for="defaultCheck1">
                        HTTP <small class="text-muted">Works over HTTP protocol (Port: 80)</small>
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="protocols[https]" id="https" value="Y" @if($website->https) checked="checked" @endif class="form-check-input">
                    <label class="form-check-label" for="defaultCheck2">
                        HTTPS <small class="text-muted">Works over secure protocol (Port: 443)</small>
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="protocols[http2https]" id="http2https" value="Y" @if($website->http2https) checked="checked" @endif class="form-check-input">
                    <label class="form-check-label" for="defaultCheck2">
                        HTTP -> HTTPS <small class="text-muted">Automatic redirect HTTP traffic to HTTPS</small>
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="protocols[lets_encrypt]" id="letsencrypt" value="Y" @if($website->lets_encrypt) checked="checked" @endif class="form-check-input">
                    <label class="form-check-label" for="defaultCheck2">
                        <a href="https://letsencrypt.org/" target="_blank">Let's Encript</a>
                    </label>
                </div>
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
