<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repeated Problem Tracker</title>
    <style>
        :root {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #e0e0e0;
            --accent-color: #4daafc;
            --border-color: #333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            max-width: 800px;
            width: 100%;
        }

        header {
            text-align: left;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        h1 { color: white; margin-bottom: 5px; }
        p.subtitle { color: #888; font-size: 0.9em; }

        .input-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--accent-color);
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            background: #2a2a2a;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: white;
            box-sizing: border-box;
        }

        button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: opacity 0.2s;
        }

        button:hover { opacity: 0.8; }

        .report-section {
            width: 100%;
        }

        .log-item {
            background: var(--card-bg);
            padding: 15px;
            border-left: 4px solid var(--accent-color);
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .log-date { font-size: 0.8em; color: #888; }
        .log-content { margin-top: 5px; font-size: 0.95em; }
        .log-content span { color: #aaa; font-style: italic; }

        .analysis-box {
            background: #1a2633;
            border: 1px solid #2c4763;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .tag {
            display: inline-block;
            background: #333;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin: 2px;
            border: 1px solid #444;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Spot the Patterns</h1>
        <p class="subtitle">Mục tiêu: Rèn luyện khả năng nhận diện các vấn đề lặp lại.</p>
    </header>

    <div class="input-section">
        <h3>Ghi chép hôm nay</h3>
        <div class="form-group">
            <label>1. Việc gì bạn làm thấy lặp đi lặp lại?</label>
            <input type="text" id="repetitive" placeholder="Ví dụ: Cài đặt lại app cho dự án mới...">
        </div>
        <div class="form-group">
            <label>2. Việc gì bạn phải giải thích lại lần nữa?</label>
            <input type="text" id="explained" placeholder="Ví dụ: Quy tắc đặt tên file cho đồng nghiệp...">
        </div>
        <div class="form-group">
            <label>3. Điều gì khiến bạn khó chịu vì nó đã xảy ra trước đó?</label>
            <input type="text" id="annoyed" placeholder="Ví dụ: Sửa lỗi format văn bản trong bài nhóm...">
        </div>
        <button onclick="saveLog()">Lưu ghi chép</button>
    </div>

    <div class="report-section">
        <h3>Phân tích quy luật (Patterns)</h3>
        <div id="analysis" class="analysis-box">
            Chưa có đủ dữ liệu để phân tích. Hãy bắt đầu ghi chép.
        </div>

        <h3>Lịch sử quan sát</h3>
        <div id="logs-container"></div>
    </div>
</div>

<script>
    // Khởi tạo dữ liệu từ LocalStorage
    let logs = JSON.parse(localStorage.getItem('problemLogs')) || [];

    function saveLog() {
        const repetitive = document.getElementById('repetitive').value;
        const explained = document.getElementById('explained').value;
        const annoyed = document.getElementById('annoyed').value;

        if (!repetitive && !explained && !annoyed) {
            alert("Vui lòng nhập ít nhất một mục!");
            return;
        }

        const newEntry = {
            id: Date.now(),
            date: new Date().toLocaleDateString('vi-VN'),
            repetitive,
            explained,
            annoyed,
            timestamp: new Date()
        };

        logs.unshift(newEntry);
        localStorage.setItem('problemLogs', JSON.stringify(logs));
        
        // Reset form
        document.getElementById('repetitive').value = '';
        document.getElementById('explained').value = '';
        document.getElementById('annoyed').value = '';

        renderLogs();
        analyzePatterns();
    }

    function renderLogs() {
        const container = document.getElementById('logs-container');
        container.innerHTML = logs.map(log => `
            <div class="log-item">
                <div class="log-date">${log.date}</div>
                <div class="log-content">🔄 <span>Lặp lại:</span> ${log.repetitive || 'Không'}</div>
                <div class="log-content">🗣️ <span>Giải thích:</span> ${log.explained || 'Không'}</div>
                <div class="log-content">💢 <span>Khó chịu:</span> ${log.annoyed || 'Không'}</div>
            </div>
        `).join('');
    }

    function analyzePatterns() {
        if (logs.length === 0) return;

        // Thuật toán tách từ đơn giản để tìm từ khóa xuất hiện nhiều (Pattern Spotting)
        const allText = logs.map(l => `${l.repetitive} ${l.explained} ${l.annoyed}`).join(' ').toLowerCase();
        const words = allText.match(/\b(\w{4,})\b/g) || []; // Lấy các từ > 3 ký tự
        
        const freq = {};
        words.forEach(w => {
            if(!['nhiều', 'trong', 'những', 'không', 'người'].includes(w)) {
                freq[w] = (freq[w] || 0) + 1;
            }
        });

        const sortedWords = Object.entries(freq)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 10);

        let html = `<h4>Từ khóa xuất hiện nhiều nhất (Dấu hiệu lặp lại):</h4>`;
        if (sortedWords.length > 0) {
            html += sortedWords.map(w => `<span class="tag">${w[0]} (${w[1]} lần)</span>`).join('');
            html += `<p style="font-size: 0.85em; margin-top:10px; color: #4daafc;">
                * Gợi ý: Hãy tập trung vào các từ khóa trên để viết danh sách 10 vấn đề cuối tháng.</p>`;
        } else {
            html += "Hãy ghi chép thêm vài ngày để hệ thống tìm quy luật.";
        }

        document.getElementById('analysis').innerHTML = html;
    }

    // Chạy lần đầu
    renderLogs();
    analyzePatterns();
</script>

</body>
</html>