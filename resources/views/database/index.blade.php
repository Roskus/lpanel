@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3"><i class="fas fa-fw fa-database"></i> {{ __('Databases') }}</h1>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a href="/database/add" class="btn btn-primary mt-2 mb-2">{{ __('Add') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Database') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created at') }}</th>
                                <th>{{ __('Updated at') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if (!@empty($databases))
                            @foreach ($databases as $database)
                            <tr>
                                <td>
                                    <a href="/database/edit/{{ $database->id }}">{{ $database->name }}</a>
                                </td>
                                <td>{{ $database->type }}</td>
                                <td>{{ $database->status }}</td>
                                <td>{{ $database->created_at->format('d/m/Y') }}</td>
                                <td>{{ $database->updated_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="@if($database->type != 'Postgres') {{ env('MYSQL_MANAGER') }} @else {{ env('POSTGRES_MANAGER') }} @endif" target="_blank" title="{{ __('Manager') }}" class="btn btn-info btn-circle">
                                        <i class="fas fa-cogs"></i>
                                    </a>
                                    <a href="" title="{{ __('Backup') }}" target="_blank" class="btn btn-warning btn-circle">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="#" title="{{ __('Delete') }}" class="btn btn-danger btn-circle">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!--./row-->

</div><!--./container-->
@endsection
