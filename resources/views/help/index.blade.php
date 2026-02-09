@extends('layouts.app')

@section('title', 'Hướng dẫn sử dụng - System Sight')

@section('content')
<div class="ss-wrapper">
    <div class="bg-gradient"></div>
    
    <x-navbar />

    <main class="ss-main">
        <div class="ss-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('machines.index') }}" class="breadcrumb-item">Trang chủ</a>
                <span class="breadcrumb-separator">→</span>
                <span class="breadcrumb-item active">Hướng dẫn</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">📚 Hướng dẫn sử dụng System Sight</h1>
                <p class="page-subtitle">Hiểu rõ cách hoạt động của hệ thống để cải tiến liên tục</p>
            </div>

            <!-- Overview Section -->
            <div class="guide-section">
                <h2>🎯 System Sight là gì?</h2>
                <p>System Sight giúp bạn <strong>nhìn thấy</strong>, <strong>xây dựng</strong> và <strong>cải thiện</strong> các quy trình kinh doanh của mình một cách có hệ thống.</p>
                
                <div class="concept-grid">
                    <div class="concept-card">
                        <div class="concept-icon">🏭</div>
                        <h3>Machine (Máy)</h3>
                        <p>Đại diện cho một <strong>lĩnh vực kinh doanh lớn</strong> của bạn.</p>
                        <p class="example">Ví dụ: Marketing, Bán hàng, Vận hành, Tài chính...</p>
                    </div>
                    
                    <div class="concept-card">
                        <div class="concept-icon">⚙️</div>
                        <h3>Subsystem (Hệ thống con)</h3>
                        <p><strong>Các phần nhỏ hơn</strong> trong một Machine.</p>
                        <p class="example">Ví dụ: Trong Marketing có: Content, Ads, SEO, Email...</p>
                    </div>
                    
                    <div class="concept-card">
                        <div class="concept-icon">🧩</div>
                        <h3>Component (Thành phần)</h3>
                        <p><strong>Các hoạt động cụ thể</strong> trong Subsystem.</p>
                        <p class="example">Ví dụ: Trong Content có: Viết blog, Làm video, Thiết kế banner...</p>
                    </div>
                    
                    <div class="concept-card">
                        <div class="concept-icon">🚀</div>
                        <h3>Upgrade (Cải tiến)</h3>
                        <p><strong>Một thay đổi hoặc quy trình mới</strong> để cải thiện Component.</p>
                        <p class="example">Ví dụ: "Quy trình viết blog 5 bước" để cải thiện việc viết blog</p>
                    </div>
                </div>
            </div>

            <!-- Status Section -->
            <div class="guide-section">
                <h2>🚦 Trạng thái của thành phần</h2>
                <p>Mỗi Component có một trong 3 trạng thái:</p>
                
                <div class="status-list">
                    <div class="status-item status-fire">
                        <span class="status-emoji">🔥</span>
                        <div>
                            <strong>On Fire (Đang cháy)</strong>
                            <p>Có vấn đề nghiêm trọng, cần xử lý ngay!</p>
                        </div>
                    </div>
                    
                    <div class="status-item status-love">
                        <span class="status-emoji">💛</span>
                        <div>
                            <strong>Needs Love (Cần quan tâm)</strong>
                            <p>Đang hoạt động nhưng chưa tốt, cần cải thiện</p>
                        </div>
                    </div>
                    
                    <div class="status-item status-smooth">
                        <span class="status-emoji">✅</span>
                        <div>
                            <strong>Smooth (Trơn tru)</strong>
                            <p>Đang hoạt động tốt, không cần can thiệp</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Section -->
            <div class="guide-section">
                <h2>📋 Quy trình làm việc</h2>
                
                <div class="workflow-steps">
                    <div class="workflow-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Tạo Machine</h4>
                            <p>Xác định các lĩnh vực kinh doanh chính của bạn</p>
                        </div>
                    </div>
                    
                    <div class="workflow-arrow">→</div>
                    
                    <div class="workflow-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Thêm Subsystem</h4>
                            <p>Chia nhỏ Machine thành các hệ thống con</p>
                        </div>
                    </div>
                    
                    <div class="workflow-arrow">→</div>
                    
                    <div class="workflow-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Tạo Component</h4>
                            <p>Liệt kê các hoạt động trong mỗi Subsystem</p>
                        </div>
                    </div>
                    
                    <div class="workflow-arrow">→</div>
                    
                    <div class="workflow-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Ship Upgrade</h4>
                            <p>Tạo cải tiến cho các Component cần thiết</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upgrade Detail -->
            <div class="guide-section">
                <h2>🚀 Cách tạo một Upgrade</h2>
                <p>Mỗi Upgrade bao gồm:</p>
                
                <div class="upgrade-fields">
                    <div class="field-item">
                        <strong>📝 Tên cải tiến</strong>
                        <p>Đặt tên ngắn gọn, dễ hiểu</p>
                    </div>
                    <div class="field-item">
                        <strong>🎯 Mục đích</strong>
                        <p>Tại sao cần cải tiến này? Giải quyết vấn đề gì?</p>
                    </div>
                    <div class="field-item">
                        <strong>⚡ Kích hoạt</strong>
                        <p>Khi nào sẽ áp dụng cải tiến này?</p>
                    </div>
                    <div class="field-item">
                        <strong>📋 Các bước</strong>
                        <p>Liệt kê từng bước để thực hiện</p>
                    </div>
                    <div class="field-item">
                        <strong>✅ Tiêu chí hoàn thành</strong>
                        <p>Làm sao biết cải tiến đã thành công?</p>
                    </div>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="guide-section tips-section">
                <h2>💡 Mẹo sử dụng hiệu quả</h2>
                <ul class="tips-list">
                    <li>🔥 Ưu tiên xử lý các Component "On Fire" trước</li>
                    <li>📅 Cố gắng ship ít nhất 1 upgrade mỗi tuần để duy trì streak</li>
                    <li>📝 Viết các bước cụ thể, rõ ràng để dễ thực hiện lại</li>
                    <li>✅ Đặt tiêu chí hoàn thành đo lường được</li>
                    <li>🔍 Thường xuyên review các upgrade đã ship để đánh giá hiệu quả</li>
                </ul>
            </div>

            <!-- Back Button -->
            <div class="guide-actions">
                <a href="{{ route('machines.index') }}" class="btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Quay lại Trang chủ</span>
                </a>
            </div>
        </div>
    </main>
</div>

@push('styles')
<style>
    .ss-wrapper {
        min-height: 100vh;
    }

    .bg-gradient {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.05) 0%, transparent 50%);
        z-index: -1;
    }

    .ss-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 32px;
    }

    .ss-main {
        padding: 40px 0 80px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        font-size: 14px;
    }

    .breadcrumb-item {
        color: #64748b;
        text-decoration: none;
    }

    .breadcrumb-item:hover {
        color: #6366f1;
    }

    .breadcrumb-item.active {
        color: #1a202c;
        font-weight: 500;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
    }

    .page-header {
        margin-bottom: 40px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 16px;
    }

    .guide-section {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }

    .guide-section h2 {
        font-size: 22px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 16px;
    }

    .guide-section > p {
        color: #475569;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .concept-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .concept-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
    }

    .concept-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .concept-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .concept-card p {
        font-size: 14px;
        color: #475569;
        line-height: 1.5;
        margin: 0;
    }

    .concept-card .example {
        margin-top: 8px;
        font-size: 13px;
        color: #6366f1;
        font-style: italic;
    }

    .status-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 12px;
    }

    .status-item.status-fire {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .status-item.status-love {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    .status-item.status-smooth {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }

    .status-emoji {
        font-size: 28px;
    }

    .status-item strong {
        display: block;
        font-size: 15px;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .status-item p {
        font-size: 14px;
        color: #475569;
        margin: 0;
    }

    .workflow-steps {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .workflow-step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #f8fafc;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        flex: 1;
        min-width: 150px;
    }

    .step-number {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }

    .step-content h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 4px;
    }

    .step-content p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }

    .workflow-arrow {
        color: #cbd5e1;
        font-size: 20px;
        font-weight: bold;
    }

    .upgrade-fields {
        display: grid;
        gap: 12px;
    }

    .field-item {
        background: #f8fafc;
        padding: 16px 20px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .field-item strong {
        display: block;
        font-size: 14px;
        color: #1a202c;
        margin-bottom: 6px;
    }

    .field-item p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }

    .tips-section {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #fbbf24;
    }

    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .tips-list li {
        padding: 10px 0;
        font-size: 15px;
        color: #78350f;
        border-bottom: 1px solid rgba(251, 191, 36, 0.3);
    }

    .tips-list li:last-child {
        border-bottom: none;
    }

    .guide-actions {
        text-align: center;
        margin-top: 32px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        transition: all 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    @media (max-width: 768px) {
        .ss-container {
            padding: 0 16px;
        }

        .guide-section {
            padding: 24px;
        }

        .concept-grid {
            grid-template-columns: 1fr;
        }

        .workflow-steps {
            flex-direction: column;
        }

        .workflow-arrow {
            transform: rotate(90deg);
        }

        .workflow-step {
            width: 100%;
        }
    }
</style>
@endpush
@endsection
