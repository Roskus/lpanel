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
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Databases') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if (!empty($databases))
                            @foreach ($databases as $database)
                            <tr>
                                <td>{{ $database }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="text-center">{{ __('No databases found.') }}</td>
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
