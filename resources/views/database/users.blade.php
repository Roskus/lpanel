@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3"><i class="fas fa-fw fa-users"></i> {{ __('Database Users') }}</h1>
        </div>
    </div>
    @if ($success_message)
    <div class="alert alert-success">
        {{ $success_message }}
    </div>
    @endif
    <div class="row">
        <div class="col">
            <a href="/database/create-user" class="btn btn-primary mt-2 mb-2">{{ __('Create User') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Users') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Username') }}</th>
                                <th>{{ __('Host') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user['User'] }}</td>
                                <td>{{ $user['Host'] }}</td>
                                <td>
                                    <a href="/database/edit-user/{{ $user['User'] }}/{{ $user['Host'] }}" class="btn btn-sm btn-warning">{{ __('Edit') }}</a>
                                    <form action="/database/delete-user/{{ $user['User'] }}/{{ $user['Host'] }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
