function showToast(message, type = 'info', duration = 4000) {
    // Tạo container nếu chưa có
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    // Icon tương ứng
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️'
    };

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || '🔔'}</div>
        <div class="toast-message">${message}</div>
        <div class="toast-close">&times;</div>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);

    // Chạy thanh progress
    const progress = toast.querySelector('.toast-progress');
    progress.style.animation = `progressLoad ${duration}ms linear forwards`;

    // Tự động xóa sau duration
    const timer = setTimeout(() => {
        dismissToast(toast);
    }, duration);

    // Nút đóng thủ công
    toast.querySelector('.toast-close').onclick = () => {
        clearTimeout(timer);
        dismissToast(toast);
    };
}

function dismissToast(toast) {
    toast.style.animation = 'toastOut 0.3s ease-in forwards';
    toast.addEventListener('animationend', () => {
        toast.remove();
    });
}