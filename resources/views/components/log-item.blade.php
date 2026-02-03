@php
    $hasSolution = $log->solution !== null;
    $statusColor = match($log->status) {
        'open' => 'var(--danger-color)',
        'in_progress' => 'var(--warning-color)',
        'closed' => 'var(--success-color)',
        default => 'var(--text-muted)',
    };
    $statusText = match($log->status) {
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'closed' => 'Closed',
        default => $log->status,
    };
    $full = $log->content ?? '';
    $short = mb_strlen($full) > 150 ? mb_substr($full, 0, 150) . '...' : $full;
@endphp

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
        <div>
            <span class="card-title">{{ $log->name }}</span>
            <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">
                by {{ $log->user->username ?? 'Unknown' }}
            </span>
        </div>
        <span style="font-size: 11px; padding: 4px 10px; background: {{ $statusColor }}20; color: {{ $statusColor }}; border-radius: 12px; font-weight: 500;">
            {{ $statusText }}
        </span>
    </div>

    <p class="content-preview" data-full="{{ $full }}">
        {!! nl2br(e($short)) !!}
    </p>

    <div style="display: flex; align-items: center; gap: 12px; margin-top: 16px; flex-wrap: wrap;">
        @if ($hasSolution)
            <a href="{{ route('solutions.show', $log->solution) }}" class="btn btn-small">View Solution</a>
            <span style="font-size: 12px; color: var(--text-muted);">
                Solved by {{ $log->solution->user->username ?? 'Unknown' }}
            </span>
        @else
            <a href="{{ route('solutions.create', ['log_id' => $log->id]) }}" class="btn btn-small">Create Solution</a>
        @endif

        @if (Auth::id() === $log->user_id)
            <div style="margin-left: auto; display: flex; gap: 8px;">
                <!-- Edit Button -->
                <button class="btn btn-secondary btn-icon"
                    onclick='openEditModal({{ $log->id }}, @json($log->content))' title="Chỉnh sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="color: var(--warning-color);">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>

                <!-- Delete Button (Only if no solution) -->
                @if (!$hasSolution)
                    <form method="POST" action="{{ route('logs.destroy', $log) }}"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa vấn đề này? Hành động này không thể hoàn tác.');"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary btn-icon" title="Xóa">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="color: var(--danger-color);">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
