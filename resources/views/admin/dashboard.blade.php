@extends('admin.layout')

@section('content')
<h1>Admin Dashboard</h1>
<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4>Today's Sales</h4>
                <h2>${{ $todays_sales }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body">
                <h4>Low Stock Items</h4>
                <h2>{{ $low_stock_count }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ url('/admin/products') }}" class="btn btn-outline-dark">Manage Products</a>
    <a href="{{ url('/admin/reports') }}" class="btn btn-outline-dark">View Reports</a>
</div>
@endsection