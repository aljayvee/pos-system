@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-history text-secondary"></i> System Activity Logs</h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        <td class="fw-bold">{{ $log->user->name ?? 'System/Deleted' }}</td>
                        <td><span class="badge bg-info text-dark">{{ $log->action }}</span></td>
                        <td class="text-muted small">{{ $log->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No activity recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection