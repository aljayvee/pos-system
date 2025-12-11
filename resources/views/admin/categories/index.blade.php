@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Manage Categories</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.store') }}" method="POST" class="d-flex gap-2 mb-4">
                        @csrf
                        <input type="text" name="name" class="form-control" placeholder="New Category Name (e.g. Snacks)" required>
                        <button type="submit" class="btn btn-success">Add</button>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success py-2">{{ session('success') }}</div>
                    @endif

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
                                    <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm w-100">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No categories found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="alert alert-info">
                <h5><i class="fa fa-info-circle"></i> Instructions</h5>
                <p>Create categories here first (e.g., <strong>Canned Goods</strong>, <strong>Beverages</strong>, <strong>Toiletries</strong>). You will need to select these when adding new products.</p>
            </div>
        </div>
    </div>
</div>
@endsection