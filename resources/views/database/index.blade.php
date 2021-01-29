@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800 pb-3">{{ __('Databases') }}</h1>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a href="/database/add" class="btn btn-primary mt-2 mb-2">{{ __('New') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Database') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>Status</th>
                                <th>Created at</th>
                                <th>Updated at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if (!@empty($databases))
                            @foreach ($databases as $database)
                            <tr>
                                <td>
                                    <a href="/database/edit/{{ $database->id }}">{{ $database->name }}</a>
                                </td>
                                <td>{{ $database->status }}</td>
                                <td>{{ $database->created_at->format('d/m/Y') }}</td>
                                <td>{{ $database->updated_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="#" class="btn btn-danger btn-circle">
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
