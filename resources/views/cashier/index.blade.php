@extends('cashier.layout')

@section('content')
    <pos-interface 
        :initial-products="{{ json_encode($products) }}"
        :initial-categories="{{ json_encode($categories) }}"
        :initial-customers="{{ json_encode($customers) }}"
        :tax-config="{{ json_encode($taxSettings) }}" 
        store-name="{{ $store->name ?? 'SariPOS' }}"
        cashier-name="{{ $user->name }}"
        csrf-token="{{ csrf_token() }}"
    ></pos-interface>
@endsection