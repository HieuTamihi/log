@extends('layouts.app')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    <x-navbar />

    <main class="ss-main">
        <div class="ss-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('dashboard') }}" class="breadcrumb-item">Business Machine</a>
                <span class="breadcrumb-separator">→</span>
                <a href="{{ route('machines.show', $component->subsystem->machine->slug) }}" class="breadcrumb-item">{{ $component->subsystem->machine->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <a href="{{ route('subsystems.show', ['machineSlug' => $component->subsystem->machine->slug, 'subsystemSlug' => $component->subsystem->slug]) }}" class="breadcrumb-item">{{ $component->subsystem->name }}</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">{{ $component->name }}</span>
            </div>

            <div class="note-container">
                <!-- Note Header -->
                <div class="note-header">
                    <div class="note-title-section">
                        <div class="note-icon">{{ $component->icon ?? '📄' }}</div>
                        <div>
                            <h1 class="note-title">{{ $component->name }}</h1>
                            <div class="note-meta">
                                <span class="status-badge status-{{ $component->health_status }}">{{ ucfirst($component->health_status) }}</span>
                                <span class="last-updated">Last updated {{ $component->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="note-actions">
                        <a href="{{ route('upgrades.create', $component->id) }}" class="btn-primary">
                            <i class="fas fa-edit"></i> Edit Note
                        </a>
                    </div>
                </div>

                <!-- Note Content -->
                <div class="note-body">
                    @if($component->content)
                        <div class="markdown-content">
                            {!! Str::markdown($component->content) !!}
                        </div>
                    @else
                        <div class="empty-note">
                            <div class="empty-icon">📝</div>
                            <h3>This note is empty</h3>
                            <p>Click "Edit Note" to add content.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- History Section -->
            <div class="history-section">
                <h3 class="history-title">Version History</h3>
                <div class="history-list">
                    @forelse($component->upgrades->where('status', 'shipped')->sortByDesc('shipped_at') as $upgrade)
                        <div class="history-item">
                            <div class="history-info">
                                <span class="history-name">{{ $upgrade->name }}</span>
                                <span class="history-date">Shipped {{ $upgrade->shipped_at->diffForHumans() }} by {{ $upgrade->user->name ?? 'Unknown' }}</span>
                            </div>
                            <button class="btn-sm btn-secondary" onclick="alert('Restore feature coming soon!')">Restore</button>
                        </div>
                    @empty
                        <p class="text-gray-500">No history available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</div>

@push('styles')
<style>
    .ss-wrapper { min-height: 100vh; }
    .bg-gradient { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.05) 0%, transparent 50%); z-index: -1; }
    .ss-container { max-width: 1000px; margin: 0 auto; padding: 0 32px; }
    .ss-main { padding: 40px 0 80px; }
    
    .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; font-size: 14px; color: #64748b; }
    .breadcrumb-item { color: inherit; text-decoration: none; }
    .breadcrumb-item:hover { color: #6366f1; }
    .breadcrumb-item.active { color: #1a202c; font-weight: 500; }

    .note-container { background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 32px; }
    
    .note-header { padding: 32px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; background: #f8fafc; }
    .note-title-section { display: flex; gap: 20px; align-items: center; }
    .note-icon { width: 64px; height: 64px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 32px; }
    .note-title { font-size: 32px; font-weight: 700; color: #1a202c; line-height: 1.2; margin-bottom: 8px; }
    .note-meta { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #64748b; }
    
    .status-badge { padding: 4px 10px; border-radius: 20px; font-weight: 600; text-transform: capitalize; font-size: 12px; }
    .status-smooth, .status-green { background: #d1fae5; color: #065f46; }
    .status-on_fire, .status-red { background: #fee2e2; color: #991b1b; }
    .status-needs_love, .status-yellow { background: #fef3c7; color: #92400e; }

    .note-body { padding: 40px; min-height: 300px; font-size: 16px; line-height: 1.8; color: #334155; }
    
    .markdown-content h1 { font-size: 2em; font-weight: 700; margin-bottom: 0.5em; color: #1a202c; }
    .markdown-content h2 { font-size: 1.5em; font-weight: 600; margin-top: 1.5em; margin-bottom: 0.5em; color: #1a202c; }
    .markdown-content p { margin-bottom: 1em; }
    .markdown-content ul, .markdown-content ol { margin-bottom: 1em; padding-left: 1.5em; }
    
    .empty-note { text-align: center; padding: 40px; color: #94a3b8; }
    .empty-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }

    .history-section { border-top: 1px solid #e2e8f0; padding-top: 32px; }
    .history-title { font-size: 18px; font-weight: 600; color: #1a202c; margin-bottom: 16px; }
    .history-list { display: flex; flex-direction: column; gap: 12px; }
    .history-item { display: flex; justify-content: space-between; align-items: center; padding: 16px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; }
    .history-name { font-weight: 600; color: #1a202c; display: block; }
    .history-date { font-size: 13px; color: #64748b; }

    .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; border-radius: 10px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2); transition: all 0.2s; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3); }
    .btn-secondary { padding: 8px 16px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; color: #64748b; font-weight: 500; cursor: pointer; }
    .btn-secondary:hover { background: #f8fafc; color: #1a202c; }
</style>
@endpush
@endsection
