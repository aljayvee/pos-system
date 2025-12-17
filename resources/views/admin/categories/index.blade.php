@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Manage Categories</h5>
                </div>
                <div class="card-body">
                    {{-- Add Form --}}
                    <form action="{{ route('categories.store') }}" method="POST" class="d-flex gap-2 mb-4">
                        @csrf
                        <input type="text" name="name" class="form-control" placeholder="New Category (e.g. Snacks)" required>
                        <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i></button>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3">{{ session('success') }}</div>
                    @endif

                    {{-- DESKTOP TABLE --}}
                    <div class="d-none d-md-block">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $cat)
                                <tr>
                                    <td>{{ $cat->id }}</td>
                                    <td>{{ $cat->name }}</td>
                                    <td>
                                        <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm w-100">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">No categories found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE LIST VIEW --}}
                    <div class="d-md-none">
                        <div class="list-group list-group-flush">
                            @forelse($categories as $cat)
                            <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                <div>
                                    <span class="text-muted small me-2">#{{ $cat->id }}</span>
                                    <span class="fw-bold text-dark">{{ $cat->name }}</span>
                                </div>
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-light text-danger btn-sm rounded-circle border shadow-sm" style="width: 32px; height: 32px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted">No categories yet.</div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="col-md-6 mt-3 mt-md-0">
            <div class="alert alert-info border-0 shadow-sm">
                <h5><i class="fa fa-info-circle"></i> Instructions</h5>
                <p class="mb-0 small">Create categories here first. You will need to select these when adding new products.</p>
            </div>
        </div>
    </div>
</div>
@endsection