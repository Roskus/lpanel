@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3"><i class="fas fa-fw fa-database"></i> {{ __('Databases') }}</h1>
        </div>
    </div>
    @if ($success_message)
    <div class="alert alert-success">
        {{ $success_message }}
    </div>
    @endif
    <div class="row">
        <div class="col">
            <a href="/database/create" class="btn btn-primary mt-2 mb-2">{{ __('Create') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Databases') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if (!empty($databases))
                            @foreach ($databases as $database)
                            <tr>
                                <td>{{ $database }}</td>
                                <td>
                                    <form action="/database/delete/{{ $database }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this database?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2" class="text-center">{{ __('No databases found.') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- ./row -->
</div><!-- ./container -->
@endsection
