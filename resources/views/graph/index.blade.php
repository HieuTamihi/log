<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Knowledge Graph</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            margin: 0; 
            overflow: hidden; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
            background: #f8f8f8;
        }
        
        /* Sidebar */
        #sidebar { 
            width: 280px; 
            height: 100vh; 
            background: #ffffff; 
            border-right: 1px solid #e5e5e5;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 0.3s ease, opacity 0.3s ease;
            white-space: nowrap; /* Prevent text wrapping during collapse */
        }

        #sidebar.collapsed {
            width: 0;
            border: none;
        }
        
        .sidebar-header {
            padding: 8px;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            gap: 4px;
            background: #fafafa;
        }
        
        .icon-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6e6e6e;
            transition: all 0.15s;
            padding: 0;
        }
        
        .icon-btn:hover { 
            background: #f0f0f0;
            color: #4a4a4a;
        }
        
        .icon-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }
        
        #folder-tree {
            flex: 1;
            overflow-y: auto;
            padding: 8px 4px;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        #folder-tree-content {
            flex: 0 0 auto;
        }
        
        #folder-tree-drop-zone {
            flex: 1;
            min-height: 100px;
            position: relative;
            transition: background 0.2s;
        }
        
        #folder-tree-drop-zone.drag-over {
            background: linear-gradient(to bottom, transparent 0%, #f0f0ff 100%);
        }
        
        #folder-tree-drop-zone.drag-over::after {
            content: '📂 Drop here to move to root';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 12px 24px;
            background: #eef2ff;
            border: 2px dashed #6366f1;
            border-radius: 8px;
            color: #6366f1;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        #folder-tree::-webkit-scrollbar {
            width: 8px;
        }
        
        #folder-tree::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #folder-tree::-webkit-scrollbar-thumb {
            background: #d0d0d0;
            border-radius: 4px;
        }
        
        .tree-item {
            display: flex;
            align-items: center;
            padding: 3px 6px;
            cursor: pointer;
            border-radius: 3px;
            user-select: none;
            font-size: 13px;
            color: #6e6e6e;
            line-height: 1.4;
            border: 1px solid transparent;
            position: relative;
        }
        
        .tree-item:hover { 
            background: #f0f0f0;
            color: #2e2e2e;
        }
        
        .tree-item.selected { 
            background: #e0e0ff;
            color: #2e2e2e;
        }
        
        .tree-item.editing { 
            background: #f5f5f5; 
            border: 1px solid #6366f1;
            box-shadow: 0 0 0 1px #6366f1;
        }
        
        .tree-item-icon {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 9px;
            color: #8e8e8e;
            flex-shrink: 0;
            margin-right: 4px;
        }
        
        .tree-item-emoji {
            display: none;
        }
        
        .tree-item-name {
            flex: 1;
            padding: 2px 4px;
            border: none;
            background: transparent;
            outline: none;
            font-size: 13px;
            color: inherit;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border-radius: 2px;
        }
        
        .tree-item-name:focus {
            background: white;
            outline: none;
        }
        
        .tree-item-name[contenteditable="true"] {
            background: white;
        }
        
        .tree-children {
            padding-left: 0;
            margin-left: 10px;
            border-left: 1px solid #e0e0e0;
            display: none;
            position: relative;
        }
        
        .tree-children.expanded { display: block; }

        .tree-item {
            padding: 4px 6px;
            /* ... existing ... */
        }
        
        .sidebar-footer {
            padding: 8px 12px;
            border-top: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafafa;
            font-size: 12px;
            color: #6e6e6e;
        }
        
        .vault-name {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        
        /* Canvas */
        #canvas-container { 
            flex: 1; 
            height: 100vh; 
            position: relative; 
            overflow: hidden; 
            background: #f8f8f8;
        }
        
        #canvas { 
            width: 100%; 
            height: 100%; 
            cursor: grab;
        }
        
        #canvas.dragging { cursor: grabbing; }

        /* Connections Layer */
        #connections-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: visible;
        }

        .connection-line {
            stroke: #cbd5e1;
            stroke-width: 2px;
            fill: none;
            pointer-events: visibleStroke;
            cursor: pointer;
            transition: stroke 0.2s;
        }

        .connection-line:hover {
            stroke: #94a3b8;
            stroke-width: 3px;
        }

        .connection-line.temp {
            stroke: #6366f1;
            stroke-dasharray: 4;
        }

        .connection-handle {
            width: 12px;
            height: 12px;
            background: #fff;
            border: 2px solid #3b82f6;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            cursor: crosshair;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 10;
        }

        .card:hover .connection-handle {
            opacity: 1;
        }

        .connection-handle.right {
            right: -6px;
        }

        .connection-handle.left {
            left: -6px;
            background: #f1f5f9;
            border-color: #cbd5e1;
            cursor: default; /* Input only */
        }
        
        /* Right Toolbar */
        .right-toolbar {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .toolbar-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6e6e6e;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.15s;
        }
        
        .toolbar-btn:hover {
            background: #f0f0f0;
            color: #2e2e2e;
        }
        
        .toolbar-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }
        
        /* Cards */
        .card { 
            position: absolute; 
            background: white; 
            border: 1px solid #e5e5e5;
            border-radius: 6px; 
            padding: 16px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.08); 
            min-width: 240px;
            max-width: 400px;
            cursor: move;
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        
        .card:hover { 
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            border-color: #b5b5b5;
        }

        .card.selected {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2), 0 4px 12px rgba(0,0,0,0.12);
            z-index: 1000;
        }
        
        .card-header { 
            font-weight: 600; 
            font-size: 15px; 
            margin-bottom: 8px;
            color: #2e2e2e;
        }
        
        .card-subheader { 
            font-size: 13px; 
            color: #6e6e6e; 
            margin-bottom: 12px;
            line-height: 1.5;
        }
        
        .card-links { 
            display: flex; 
            flex-direction: column; 
            gap: 4px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
        }
        
        .card-link { 
            padding: 4px 8px; 
            background: #f8f8f8; 
            border-radius: 3px; 
            cursor: pointer; 
            font-size: 12px;
            color: #6366f1;
            transition: all 0.15s;
        }
        
        .card-link:hover { 
            background: #e8e8ff;
        }
        
        .card-link:hover { 
            background: #e8e8ff;
        }
        
        .connection-handle {
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            background: #fff;
            border: 2px solid #6366f1;
            border-radius: 50%;
            cursor: crosshair;
            z-index: 10;
            transition: all 0.15s;
            opacity: 0;
        }

        .card:hover .connection-handle {
            opacity: 1;
        }

        .connection-handle:hover {
            background: #6366f1;
            transform: translateY(-50%) scale(1.2);
        }

        .card-actions {
            position: absolute;
            top: -45px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 4px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            gap: 4px;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.2s;
            border: 1px solid #e5e5e5;
            white-space: nowrap;
        }
        
        .card.selected .card-actions {
            opacity: 1;
            pointer-events: auto;
            top: -50px;
        }
        
        .card-btn {
            padding: 4px 10px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            background: #f8f8f8;
            color: #6e6e6e;
            transition: all 0.15s;
        }
        
        .card-btn:hover { 
            background: #e5e5e5; 
            color: #2e2e2e;
        }
        
        .card-btn.delete { color: #d32f2f; }
        .card-btn.delete:hover { background: #ffebee; }
        
        /* Modal */
        #modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background: rgba(0,0,0,0.4); 
            z-index: 1000;
        }
        
        #modal.active { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        #modal-content { 
            background: white; 
            padding: 0;
            border-radius: 8px; 
            max-width: 800px; 
            width: 90%; 
            max-height: 85vh; 
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
        }
        
        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #2e2e2e;
            font-weight: 600;
        }
        
        .modal-header-actions {
            display: flex;
            gap: 8px;
        }
        
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }
        
        .note-content-view {
            font-size: 14px;
            line-height: 1.5;
            color: #2e2e2e;
            word-wrap: break-word;
        }
        
        .note-content-view:empty::before {
            content: 'Empty note';
            color: #8e8e8e;
            font-style: italic;
        }
        
        #modal-content textarea {
            width: 100%;
            min-height: 400px;
            padding: 16px;
            border: 1px solid #e5e5e5;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            line-height: 1.8;
            outline: none;
            border-radius: 0 0 6px 6px;
        }
        
        #note-content {
            width: 100%;
            height: 100%;
            padding: 16px;
            border: 1px solid #e5e5e5;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            resize: none;
            line-height: 1.5;
            outline: none;
            border-radius: 0 0 6px 6px;
            box-sizing: border-box;
        }

        /* Split Editor Layout */
        .split-editor {
            display: flex;
            gap: 0;
            min-height: 400px;
            border: 1px solid #e5e5e5;
            border-radius: 0 0 6px 6px;
            background: white;
            align-items: stretch;
        }

        .editor-side {
            flex: 1;
            min-width: 0;
            border-right: 1px solid #e5e5e5;
        }

        #note-content {
            width: 100%;
            height: auto;
            min-height: 400px;
            padding: 16px;
            border: none;
            resize: none;
            font-family: inherit;
            font-size: 14px;
            line-height: 1.6;
            outline: none;
            display: block;
            overflow: hidden;
        }

        .preview-side {
            flex: 1;
            min-width: 0;
            padding: 24px;
            background: white;
        }
        
        .modal-actions {
            margin-top: 16px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 8px 16px;
            font-size: 13px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.15s;
        }
        
        .btn-primary { 
            background: #6366f1; 
            color: white; 
        }
        
        .btn-primary:hover { background: #4f46e5; }
        
        .btn-secondary { 
            background: #f0f0f0; 
            color: #2e2e2e; 
        }
        
        .btn-secondary:hover { background: #e5e5e5; }
        
        .hidden-links { display: none; }
        
        /* Context Menu */
        #context-menu {
            position: fixed;
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 4px 0;
            min-width: 200px;
            z-index: 2000;
            display: none;
        }
        
        #context-menu.active {
            display: block;
        }
        
        .context-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            font-size: 13px;
            color: #2e2e2e;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .context-menu-item:hover {
            background: #f0f0f0;
        }
        
        .resource-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        #image-size-modal button[type="button"]:hover {
            border-color: #0066cc !important;
            background: #f0f7ff !important;
        }
        
        .context-menu-item.danger {
            color: #d32f2f;
        }
        
        .context-menu-item.danger:hover {
            background: #ffebee;
        }
        
        .context-menu-item svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
            flex-shrink: 0;
        }
        
        .context-menu-separator {
            height: 1px;
            background: #e5e5e5;
            margin: 4px 0;
        }
        
        /* Empty state */
        .empty-state {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #8e8e8e;
        }
        
        .empty-state-icons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 32px;
            opacity: 0.3;
        }
        
        .empty-state-text {
            font-size: 13px;
            line-height: 1.6;
        }

        /* History List */
        .history-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.15s;
        }
        .history-item:hover { background: #f9f9f9; }
        .history-meta { font-size: 11px; color: #8e8e8e; margin-bottom: 4px; display: flex; justify-content: space-between; }
        .history-note { font-size: 13px; color: #2e2e2e; }

        /* Multi-tab CSS */
        #note-tabs {
            display: flex;
            background: #f0f0f0;
            padding: 4px 8px 0 8px;
            border-bottom: 1px solid #e5e5e5;
            overflow-x: auto;
            gap: 2px;
            scrollbar-width: none; /* Firefox */
        }
        #note-tabs::-webkit-scrollbar { display: none; } /* Chrome/Safari */

        .note-tab {
            padding: 8px 16px;
            background: #e5e5e5;
            border: 1px solid #dcdcdc;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            font-size: 13px;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            max-width: 200px;
            position: relative;
            transition: all 0.2s;
            user-select: none;
        }

        .note-tab:hover {
            background: #ebebeb;
            color: #333;
        }

        .note-tab.active {
            background: white;
            color: #2e2e2e;
            border-color: #e5e5e5;
            font-weight: 500;
            z-index: 1;
        }

        .note-tab .tab-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .note-tab .tab-close {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #999;
            transition: all 0.2s;
        }

        .note-tab .tab-close:hover {
            background: #ff4d4f;
            color: white;
        }

        .note-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 1px;
            background: white;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    <script src="https://unpkg.com/turndown/dist/turndown.js"></script>
    <script>
        // Use a custom renderer for marked to handle external links
        const renderer = new marked.Renderer();
        const baseLinkRenderer = renderer.link.bind(renderer);
        
        renderer.link = function(href, title, text) {
            // Handle both new API (v4+) which uses a token object, and old API
            let targetHref, targetTitle, targetText;
            
            if (typeof href === 'object' && href !== null) {
                targetHref = href.href;
                targetTitle = href.title;
                targetText = href.text;
            } else {
                targetHref = href;
                targetTitle = title;
                targetText = text;
            }

            const safeHref = (typeof targetHref === 'string') ? targetHref : '';
            const isExternal = safeHref.startsWith('http://') || safeHref.startsWith('https://');
            
            if (isExternal) {
                return `<a href="${safeHref}" onclick="window.open(this.href, '_blank'); return false;" rel="noopener">${targetText || safeHref}</a>`;
            }
            
            return `<a href="${safeHref}">${targetText || safeHref}</a>`;
        };

        marked.setOptions({
            renderer: renderer,
            gfm: true,
            breaks: true,
            headerIds: false,
            mangle: false
        });
    </script>
    <style>
        /* Spin animation for loading */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Markdown Styles */
        .note-content-view h1,
        .note-content-view h2,
        .note-content-view h3,
        .note-content-view h4,
        .note-content-view h5,
        .note-content-view h6 {
            margin-top: 16px;
            margin-bottom: 8px;
            font-weight: 600;
            line-height: 1.25;
        }
        
        .note-content-view h1 { font-size: 2em; border-bottom: 1px solid #e5e5e5; padding-bottom: 8px; }
        .note-content-view h2 { font-size: 1.5em; border-bottom: 1px solid #e5e5e5; padding-bottom: 8px; }
        .note-content-view h3 { font-size: 1.25em; }
        .note-content-view h4 { font-size: 1em; }
        .note-content-view h5 { font-size: 0.875em; }
        .note-content-view h6 { font-size: 0.85em; color: #6e6e6e; }
        
        .note-content-view p {
            margin-bottom: 8px;
        }
        
        .note-content-view ul,
        .note-content-view ol {
            margin-bottom: 8px;
            padding-left: 1.5em;
        }
        
        .note-content-view li {
            margin-bottom: 2px;
        }

        .note-content-view > *:last-child {
            margin-bottom: 0;
        }
        
        .note-content-view code {
            background: #f6f8fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .note-content-view pre {
            background: #f6f8fa;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            margin-bottom: 16px;
        }
        
        .note-content-view pre code {
            background: none;
            padding: 0;
        }
        
        .note-content-view blockquote {
            border-left: 4px solid #e5e5e5;
            padding-left: 16px;
            margin-left: 0;
            margin-bottom: 16px;
            color: #6e6e6e;
        }
        
        .note-content-view a {
            color: #6366f1;
            text-decoration: none;
        }
        
        .note-content-view a:hover {
            text-decoration: underline;
        }
        
        .note-content-view table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 16px;
        }
        
        .note-content-view table th,
        .note-content-view table td {
            border: 1px solid #e5e5e5;
            padding: 8px 12px;
            text-align: left;
        }
        
        .note-content-view table th {
            background: #f6f8fa;
            font-weight: 600;
        }
        
        .note-content-view hr {
            border: none;
            border-top: 1px solid #e5e5e5;
            margin: 24px 0;
        }
        
        .note-content-view img {
            max-width: 100%;
            height: auto;
        }
        
        /* Markdown Toolbar */
        .md-btn {
            padding: 6px 10px;
            border: 1px solid #e5e5e5;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #2e2e2e;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .md-btn:hover {
            background: #f0f0f0;
            border-color: #d0d0d0;
        }
        
        .md-btn:active {
            background: #e5e5e5;
        }
    </style>
</head>
<body>
    <div style="display: flex;">
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="sidebar-header" style="display: flex; align-items: center; padding: 12px 16px; gap: 4px;">
                <!-- New Note -->
                <button class="icon-btn" onclick="createNote()" title="New note">
                    <svg viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>
                </button>
                <!-- New Folder -->
                <button class="icon-btn" onclick="createFolder()" title="New folder">
                    <svg viewBox="0 0 24 24"><path d="M10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6H12L10,4Z"/></svg>
                </button>
                <!-- New Canvas -->
                <button class="icon-btn" onclick="createCanvas()" title="New canvas">
                    <svg viewBox="0 0 24 24"><path d="M10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6H12L10,4M19,19H5V8H19V19M17,12V14H13V18H11V14H7V12H11V8H13V12H17Z"/></svg>
                </button>
                <!-- Resource Library -->
                <button class="icon-btn" onclick="openResourceLibrary()" title="Resource Library" style="color: #0066cc;">
                    <svg viewBox="0 0 24 24"><path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M19,19H5V5H19V19Z"/></svg>
                </button>
                <!-- Collapse/Expand All -->
                <button class="icon-btn" onclick="toggleExpandAll()" title="Collapse/Expand all">
                    <svg viewBox="0 0 24 24"><path d="M7,5H21V7H7V5M7,13V11H21V13H7M4,4.5A1.5,1.5 0 0,1 5.5,6A1.5,1.5 0 0,1 4,7.5A1.5,1.5 0 0,1 2.5,6A1.5,1.5 0 0,1 4,4.5M4,10.5A1.5,1.5 0 0,1 5.5,12A1.5,1.5 0 0,1 4,13.5A1.5,1.5 0 0,1 2.5,12A1.5,1.5 0 0,1 4,10.5M7,19V17H21V19H7M4,16.5A1.5,1.5 0 0,1 5.5,18A1.5,1.5 0 0,1 4,19.5A1.5,1.5 0 0,1 2.5,18A1.5,1.5 0 0,1 4,16.5Z"/></svg>
                </button>
                
                <div style="flex: 1;"></div>

                <!-- Toggle Sidebar (Close) -->
                <button class="icon-btn" onclick="toggleSidebar()" title="Close sidebar">
                    <svg viewBox="0 0 24 24"><path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/></svg>
                </button>
            </div>
            
            <div id="folder-tree">
                <div id="folder-tree-content"></div>
                <div id="folder-tree-drop-zone"></div>
            </div>
            <!-- Footer Removed -->
        </div>

        <!-- Canvas Container -->
        <div id="canvas-container" style="display: flex; flex-direction: column;">
            
            <!-- Top Navbar -->
            <div class="main-navbar" style="height: 50px; background: white; border-bottom: 1px solid #e5e5e5; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <!-- External Sidebar Toggle (Moved here) -->
                    <button id="sidebar-toggle-external" class="icon-btn" onclick="toggleSidebar()" title="Show sidebar" style="display: none;">
                        <svg viewBox="0 0 24 24"><path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/></svg>
                    </button>
                    
                    <!-- Logo -->
                    <div style="font-weight: 600; font-size: 16px; color: #2e2e2e; display: flex; align-items: center; gap: 8px;">
                        System Sight
                        <span id="canvas-indicator" style="font-size: 13px; font-weight: 400; color: #6e6e6e; display: none;">
                            / <span id="canvas-name"></span>
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- Audit Logs (Formerly Reports) -->
                    <button class="icon-btn" title="Audit Logs" onclick="window.location.href='/audit-logs'">
                        <svg viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M15,18V16H6V18H15M18,14V12H6V14H18Z"/></svg>
                    </button>
                    
                    <!-- Notifications -->
                    <div style="position: relative;">
                        <button class="icon-btn" onclick="toggleNotifications(event)" title="Notifications">
                            <svg viewBox="0 0 24 24"><path d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21"/></svg>
                            <span class="notification-badge" style="position: absolute; top: 2px; right: 2px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 1px solid white;"></span>
                        </button>
                        <!-- Dropdown -->
                        <div id="notification-dropdown" style="display: none; position: absolute; top: 100%; right: 0; width: 280px; background: white; border: 1px solid #e5e5e5; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 1000; margin-top: 8px;">
                            <div style="padding: 12px; border-bottom: 1px solid #f0f0f0; font-weight: 600; font-size: 13px;">Notifications</div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <div style="padding: 12px; font-size: 13px; color: #6e6e6e; text-align: center;">No new notifications</div>
                            </div>
                        </div>
                    </div>

                    <!-- User / Logout -->
                    <div style="position: relative;">
                        <button class="icon-btn" onclick="toggleUserMenu(event)" title="User menu">
                            <svg viewBox="0 0 24 24"><path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/></svg>
                        </button>
                         <div id="user-dropdown" style="display: none; position: absolute; top: 100%; right: 0; width: 160px; background: white; border: 1px solid #e5e5e5; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 1000; margin-top: 8px;">
                             <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; color: #d32f2f; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"/></svg>
                                    Log Out
                                </button>
                             </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Screen -->
            <div id="welcome-screen" style="position: relative; flex: 1; overflow: hidden; background: #f8f8f8; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center; color: #6e6e6e;">
                    <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                    <h2 style="font-size: 24px; font-weight: 600; color: #2e2e2e; margin-bottom: 8px;">Welcome to System Sight</h2>
                    <p style="font-size: 14px; line-height: 1.6;">
                        Create notes and folders from the sidebar<br>
                        Or click on a canvas to start visualizing
                    </p>
                </div>
            </div>

            <!-- Note Editor Panel -->
            <div id="note-panel" style="position: relative; flex: 1; overflow: hidden; background: #f8f8f8; display: none; flex-direction: column;">
                <div id="note-tabs"></div>
                <div id="active-note-container" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
                    <!-- Note content will be loaded here -->
                </div>
            </div>

            <!-- Canvas Wrapper -->
            <div id="canvas-wrapper" style="position: relative; flex: 1; overflow: hidden; background: #f8f8f8; display: none;">
                <div class="right-toolbar">
                    <button onclick="zoomIn()" class="toolbar-btn" title="Zoom in">
                        <svg viewBox="0 0 24 24"><path d="M15.5,14L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5M12,10H10V12H9V10H7V9H9V7H10V9H12V10Z"/></svg>
                    </button>
                    <button onclick="zoomOut()" class="toolbar-btn" title="Zoom out">
                        <svg viewBox="0 0 24 24"><path d="M15.5,14L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5M7,9H12V10H7V9Z"/></svg>
                    </button>
                    <button onclick="resetZoom()" class="toolbar-btn" title="Reset zoom">
                        <svg viewBox="0 0 24 24"><path d="M12,5.5A6.5,6.5 0 0,1 18.5,12C18.5,13.25 18.15,14.42 17.54,15.41L16,14.04C16.32,13.43 16.5,12.73 16.5,12A4.5,4.5 0 0,0 12,7.5C10.76,7.5 9.65,8 8.84,8.84L7.3,7.3C8.46,6.24 10.14,5.5 12,5.5M5.5,12C5.5,10.75 5.85,9.58 6.46,8.59L8,9.96C7.68,10.57 7.5,11.27 7.5,12A4.5,4.5 0 0,0 12,16.5C13.24,16.5 14.35,16 15.16,15.16L16.7,16.7C15.54,17.76 13.86,18.5 12,18.5A6.5,6.5 0 0,1 5.5,12M2.27,1.44L20.56,19.73L19.15,21.14L15.54,17.54C14.42,18.32 13.06,18.82 11.59,18.95V21.5H9.59V18.95C6.5,18.57 4,16.03 4,12.91C4,11.44 4.5,10.08 5.28,8.96L1.44,5.12L2.27,1.44M12.41,5.05C15.5,5.43 18,7.97 18,11.09C18,12.56 17.5,13.92 16.72,15.04L12.41,10.73V5.05Z"/></svg>
                    </button>
                    <button onclick="autoArrangeCards()" class="toolbar-btn" title="Auto Arrange">
                        <svg viewBox="0 0 24 24"><path d="M10,4V8H14V4H10M16,4V8H20V4H16M16,10V14H20V10H16M16,16V20H20V16H16M14,20V16H10V20H14M8,20V16H4V20H8M8,14V10H4V14H8M8,8V4H4V8H8M10,14H14V10H10V14M4,2H20A2,2 0 0,1 22,4V20A2,2 0 0,1 20,22H4C2.92,22 2,21.1 2,20V4A2,2 0 0,1 4,2Z"/></svg>
                    </button>
                    <button onclick="toggleFullscreen()" class="toolbar-btn" title="Fullscreen">
                        <svg viewBox="0 0 24 24"><path d="M5,5H10V7H7V10H5V5M14,5H19V10H17V7H14V5M17,14H19V19H14V17H17V14M10,17V19H5V14H7V17H10Z"/></svg>
                    </button>
                </div>
                <div id="canvas">
                    <svg id="connections-layer"></svg>
                    <div class="empty-state" id="empty-state">
                        <div class="empty-state-icons">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M13,9H18.5L13,3.5V9M6,2H14L20,8V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V4C4,2.89 4.89,2 6,2M15,18V16H6V18H15M18,14V12H6V14H18Z"/></svg>
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M19,20H4C2.89,20 2,19.1 2,18V6C2,4.89 2.89,4 4,4H10L12,6H19A2,2 0 0,1 21,8H21L4,8V18L6.14,10H23.21L20.93,18.5C20.7,19.37 19.92,20 19,20Z"/></svg>
                        </div>
                        <div class="empty-state-text">
                            Drag from left or double-click<br>
                            Cmd + Drag to zoom
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal">
        <div id="modal-content"></div>
    </div>

    <!-- Context Menu -->
    <div id="context-menu">
        <div class="context-menu-item" onclick="contextMenuAction('newNote')">
            <svg viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>
            <span>New note</span>
        </div>
        <div class="context-menu-item" onclick="contextMenuAction('newFolder')">
            <svg viewBox="0 0 24 24"><path d="M10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6H12L10,4Z"/></svg>
            <span>New folder</span>
        </div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" onclick="contextMenuAction('status')">
            <svg viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/></svg>
            <span>Change Status</span>
        </div>
        <div class="context-menu-item" onclick="contextMenuAction('move')">
            <svg viewBox="0 0 24 24"><path d="M14,18V15H10V11H14V8L19,13M20,6H12L10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6Z"/></svg>
            <span id="context-move-text">Move to...</span>
        </div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" onclick="contextMenuAction('rename')">
            <svg viewBox="0 0 24 24"><path d="M14.06,9L15,9.94L5.92,19H5V18.08L14.06,9M17.66,3C17.41,3 17.15,3.1 16.96,3.29L15.13,5.12L18.88,8.87L20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18.17,3.09 17.92,3 17.66,3M14.06,6.19L3,17.25V21H6.75L17.81,9.94L14.06,6.19Z"/></svg>
            <span>Rename...</span>
        </div>
        <div class="context-menu-item danger" onclick="contextMenuAction('delete')">
            <svg viewBox="0 0 24 24"><path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/></svg>
            <span>Delete</span>
        </div>
    </div>
    
    <!-- Resource Context Menu -->
    <div id="resource-context-menu" style="display: none; position: fixed; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 200px; z-index: 3000; padding: 4px 0;">
        <div class="context-menu-item" onclick="resourceContextAction('insert')">
            <svg viewBox="0 0 24 24"><path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/></svg>
            <span id="resource-insert-text">Insert</span>
        </div>
        <div class="context-menu-item" onclick="resourceContextAction('copy')">
            <svg viewBox="0 0 24 24"><path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z"/></svg>
            <span>Copy Link</span>
        </div>
        <div class="context-menu-item" onclick="resourceContextAction('download')">
            <svg viewBox="0 0 24 24"><path d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z"/></svg>
            <span>Download</span>
        </div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" onclick="resourceContextAction('details')">
            <svg viewBox="0 0 24 24"><path d="M13,9H11V7H13M13,17H11V11H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/></svg>
            <span>View Details</span>
        </div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item danger" onclick="resourceContextAction('delete')">
            <svg viewBox="0 0 24 24"><path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/></svg>
            <span>Delete</span>
        </div>
    </div>
    
    <!-- Image Size Selection Modal -->
    <div id="image-size-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 4000; align-items: center; justify-content: center;">
        <div onclick="event.stopPropagation()" style="background: white; padding: 24px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #333;">Insert Image</h3>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; color: #666; margin-bottom: 8px;">Select Size:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <button type="button" onclick="insertImageWithSize('original'); event.stopPropagation();" style="padding: 12px; border: 2px solid #e5e5e5; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                        <div style="font-weight: 500; margin-bottom: 4px;">Original</div>
                        <div style="font-size: 11px; color: #999;">Full size</div>
                    </button>
                    <button type="button" onclick="insertImageWithSize('large'); event.stopPropagation();" style="padding: 12px; border: 2px solid #e5e5e5; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                        <div style="font-weight: 500; margin-bottom: 4px;">Large</div>
                        <div style="font-size: 11px; color: #999;">800px</div>
                    </button>
                    <button type="button" onclick="insertImageWithSize('medium'); event.stopPropagation();" style="padding: 12px; border: 2px solid #e5e5e5; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                        <div style="font-weight: 500; margin-bottom: 4px;">Medium</div>
                        <div style="font-size: 11px; color: #999;">500px</div>
                    </button>
                    <button type="button" onclick="insertImageWithSize('small'); event.stopPropagation();" style="padding: 12px; border: 2px solid #e5e5e5; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                        <div style="font-weight: 500; margin-bottom: 4px;">Small</div>
                        <div style="font-size: 11px; color: #999;">300px</div>
                    </button>
                    <button type="button" onclick="insertImageWithSize('thumbnail'); event.stopPropagation();" style="padding: 12px; border: 2px solid #e5e5e5; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                        <div style="font-weight: 500; margin-bottom: 4px;">Thumbnail</div>
                        <div style="font-size: 11px; color: #999;">150px</div>
                    </button>
                    <button type="button" onclick="insertImageWithSize('custom'); event.stopPropagation();" style="padding: 12px; border: 2px solid #e5e5e5; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                        <div style="font-weight: 500; margin-bottom: 4px;">Custom</div>
                        <div style="font-size: 11px; color: #999;">Enter size</div>
                    </button>
                </div>
            </div>
            
            <button type="button" onclick="closeImageSizeModal(); event.stopPropagation();" style="width: 100%; padding: 10px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: pointer; font-size: 14px; font-family: inherit;">Cancel</button>
        </div>
    </div>
    
    <!-- Status Change Modal -->
    <div id="status-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 24px; border-radius: 8px; max-width: 300px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #333;">Change Status</h3>
            <div style="display: flex; gap: 12px; justify-content: center; margin-bottom: 20px;">
                <button type="button" onclick="changeStatusFromMenu('none')" class="status-menu-btn" data-status="none" style="width: 48px; height: 48px; border-radius: 8px; border: 2px solid #ddd; background: white; cursor: pointer; transition: all 0.15s; position: relative;" title="None">
                    <svg viewBox="0 0 24 24" style="width: 28px; height: 28px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); fill: #999;"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/></svg>
                </button>
                <button type="button" onclick="changeStatusFromMenu('draft')" class="status-menu-btn" data-status="draft" style="width: 48px; height: 48px; border-radius: 8px; border: 2px solid #ddd; background: #ef4444; cursor: pointer; transition: all 0.15s;" title="Red - Issues/Draft"></button>
                <button type="button" onclick="changeStatusFromMenu('improving')" class="status-menu-btn" data-status="improving" style="width: 48px; height: 48px; border-radius: 8px; border: 2px solid #ddd; background: #f59e0b; cursor: pointer; transition: all 0.15s;" title="Yellow - In Progress"></button>
                <button type="button" onclick="changeStatusFromMenu('standardized')" class="status-menu-btn" data-status="standardized" style="width: 48px; height: 48px; border-radius: 8px; border: 2px solid #ddd; background: #10b981; cursor: pointer; transition: all 0.15s;" title="Green - Complete"></button>
            </div>
            <button onclick="closeStatusModal()" style="width: 100%; padding: 8px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: pointer; font-size: 14px;">Cancel</button>
        </div>
    </div>
    
    <!-- Move To Modal -->
    <div id="move-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 0; border-radius: 8px; max-width: 400px; width: 90%; max-height: 70vh; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e5e5e5; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Move to folder</h3>
                <button onclick="closeMoveModal()" style="border: none; background: none; cursor: pointer; font-size: 20px; color: #6e6e6e;">&times;</button>
            </div>
            <div style="flex: 1; overflow-y: auto; padding: 16px 20px;">
                <div style="padding: 12px; background: #f0f0f0; border-radius: 4px; cursor: pointer; margin-bottom: 8px; font-size: 14px;" onclick="moveToFolder(null)">
                    📂 Root (No folder)
                </div>
                <div id="move-folder-list"></div>
            </div>
        </div>
    </div>

    <!-- Resource Library Modal -->
    <div id="resource-library-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 8px; width: 90%; max-width: 1200px; height: 85vh; box-shadow: 0 8px 32px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="padding: 16px 20px; border-bottom: 1px solid #e5e5e5; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">📚 Resource Library</h3>
                <button onclick="closeResourceLibrary()" style="border: none; background: none; cursor: pointer; font-size: 24px; color: #6e6e6e;">&times;</button>
            </div>
            
            <!-- Toolbar -->
            <div style="padding: 12px 20px; border-bottom: 1px solid #e5e5e5; display: flex; gap: 12px; align-items: center; background: #fafafa;">
                <button onclick="document.getElementById('resource-file-upload').click()" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M9,16V10H5L12,3L19,10H15V16H9M5,20V18H19V20H5Z"/></svg>
                    Upload
                </button>
                <input type="file" id="resource-file-upload" style="display: none;" onchange="uploadResource(event)" multiple>
                
                <input type="text" id="resource-search" placeholder="Search resources..." style="flex: 1; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px;" oninput="filterResources()">
                
                <select id="resource-type-filter" onchange="filterResources()" style="padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px;">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="document">Documents</option>
                    <option value="video">Videos</option>
                    <option value="audio">Audio</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <!-- Content -->
            <div id="resource-library-content" style="flex: 1; overflow-y: auto; padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; align-content: start;">
                <!-- Resources will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Note Link Selector Modal -->
    <div id="note-link-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 3000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 8px; width: 90%; max-width: 500px; max-height: 80vh; box-shadow: 0 8px 32px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e5e5e5; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Link to another note</h3>
                <button onclick="closeNoteLinkModal()" style="border: none; background: none; cursor: pointer; font-size: 24px; color: #6e6e6e;">&times;</button>
            </div>
            <div style="padding: 12px 20px; border-bottom: 1px solid #e5e5e5; background: #fafafa;">
                <input type="text" id="note-link-search" placeholder="Search notes..." style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px;" oninput="filterNoteLinks()">
            </div>
            <div id="note-link-list" style="flex: 1; overflow-y: auto; padding: 10px;">
                <!-- Note list will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Hidden input for Word Import -->
    <input type="file" id="word-import-input" accept=".docx" style="display: none;" onchange="handleWordImport(event)">

    <script>
        // Version: 2026-02-13 03:30 - Added Move To feature
        console.log('Script loaded - Version 3.30');
        let cards = @json($cards);
        let zoom = 1;
        let panX = 0, panY = 0;
        let isDragging = false;
        let isConnecting = false;
        let connectionSource = null;
        let connectionTempEnd = { x: 0, y: 0 };
        let dragStartX, dragStartY;
        let draggedCard = null;
        let folders = [];
        let notes = [];
        let users = [];
        let expandedFolders = new Set();
        let editingElement = null;
        let contextMenuTarget = null;
        let selectedCardId = null;

        // Multi-tab state
        let openedNotes = []; // Array of note objects { id, data, mode: 'view'|'edit'|'history', scrollPos: 0 }
        let activeNoteId = null;

        const canvas = document.getElementById('canvas');
        const modal = document.getElementById('modal');
        const modalContent = document.getElementById('modal-content');
        const contextMenu = document.getElementById('context-menu');

        let rootNotes = [];
        let canvases = [];
        let currentCanvasId = null; // Track current canvas

        // Load folders and notes
        async function loadFolders() {
            const [foldersRes, rootNotesRes, canvasesRes] = await Promise.all([
                fetch('/api/folders/tree'),
                fetch('/api/notes?root=true'),
                fetch('/api/canvases')
            ]);
            folders = await foldersRes.json();
            rootNotes = await rootNotesRes.json();
            canvases = await canvasesRes.json();
            renderFolderTree();
        }

        async function loadUsers() {
            const res = await fetch('/api/users');
            users = await res.json();
        }

        function renderFolderTree() {
            const tree = document.getElementById('folder-tree-content');
            const foldersHtml = renderFolders(folders);
            
            // Render root notes
            const statusIcons = {
                'draft': '🔴',
                'improving': '🟡',
                'standardized': '🟢'
            };

            const rootNotesHtml = rootNotes.map(note => {
                const noteStatus = note.status && note.status !== 'none' && statusIcons[note.status] ? `<span style="font-size: 10px; margin-left: auto;">${statusIcons[note.status]}</span>` : '';
                return `
                    <div class="tree-item note-tree-item" draggable="true" data-type="note" data-note-id="${note.id}">
                        <span class="tree-item-icon" style="visibility: hidden;"></span>
                        <span class="tree-item-name" contenteditable="false" onblur="saveItemName(${note.id}, 'note', this)" onkeydown="handleKeyDown(event, this)">${note.name}</span>
                        ${noteStatus}
                    </div>
                `;
            }).join('');

            // Render canvases
            const canvasesHtml = canvases.map(canvas => {
                return `
                    <div class="tree-item canvas-tree-item" draggable="true" data-type="canvas" data-canvas-id="${canvas.id}">
                        <span class="tree-item-icon">🎨</span>
                        <span class="tree-item-name" contenteditable="false" onblur="saveItemName(${canvas.id}, 'canvas', this)" onkeydown="handleKeyDown(event, this)">${canvas.name}</span>
                    </div>
                `;
            }).join('');

            tree.innerHTML = foldersHtml + rootNotesHtml + canvasesHtml;
            attachNoteClickHandlers();
            attachCanvasClickHandlers();
        }

        function renderFolders(items, level = 0) {
            return items.map(item => {
                const hasChildren = (item.children && item.children.length > 0) || (item.notes && item.notes.length > 0);
                const isExpanded = expandedFolders.has(item.id);
                // Always show arrow for folders (even if empty)
                const arrow = `<svg viewBox="0 0 24 24" style="width: 14px; height: 14px; transform: rotate(${isExpanded ? '90deg' : '0deg'}); transition: transform 0.1s;">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                     </svg>`;
                
                // Status mapping
                const statusIcons = {
                    'draft': '🔴',
                    'improving': '🟡',
                    'standardized': '🟢'
                };
                
                // Calculate folder status based on notes inside (priority: draft > improving > standardized > none)
                let folderStatus = 'none';
                if (item.notes && item.notes.length > 0) {
                    const statusPriority = { 'draft': 3, 'improving': 2, 'standardized': 1, 'none': 0 };
                    let highestPriority = 0;
                    
                    item.notes.forEach(note => {
                        const priority = statusPriority[note.status] || 0;
                        if (priority > highestPriority) {
                            highestPriority = priority;
                            folderStatus = note.status;
                        }
                    });
                }
                
                const statusIcon = folderStatus && folderStatus !== 'none' && statusIcons[folderStatus] ? `<span style="font-size: 10px; margin-left: 4px;">${statusIcons[folderStatus]}</span>` : '';

                return `
                    <div>
                        <div class="tree-item" data-type="folder" data-id="${item.id}" 
                             draggable="true"
                             onclick="toggleFolder(${item.id}, event)"
                             ondragover="allowDrop(event)" 
                             ondragleave="resetDragStyle(event)"
                             ondrop="dropNoteToFolder(event, ${item.id})">
                            <span class="tree-item-icon">${arrow}</span>
                            <span class="tree-item-name" contenteditable="false" onblur="saveItemName(${item.id}, 'folder', this)" onkeydown="handleKeyDown(event, this)">${item.name}</span>
                            ${statusIcon}
                        </div>
                        <div class="tree-children ${isExpanded ? 'expanded' : ''}" id="folder-${item.id}">
                            ${item.notes ? item.notes.map(note => {
                                const noteStatus = note.status && note.status !== 'none' && statusIcons[note.status] ? `<span style="font-size: 10px; margin-left: auto;">${statusIcons[note.status]}</span>` : '';
                                return `
                                <div class="tree-item note-tree-item" draggable="true" data-type="note" data-note-id="${note.id}">
                                    <span class="tree-item-icon" style="visibility: hidden;"></span>
                                    <span class="tree-item-name" contenteditable="false" onblur="saveItemName(${note.id}, 'note', this)" onkeydown="handleKeyDown(event, this)">${note.name}</span>
                                    ${noteStatus}
                                </div>
                            `}).join('') : ''}
                            ${item.children ? renderFolders(item.children, level + 1) : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Handle note click - attach after render
        function attachNoteClickHandlers() {
            document.querySelectorAll('.note-tree-item').forEach(item => {
                // Click handler
                item.addEventListener('click', (e) => {
                    // Don't open if editing, right-clicking, or if it's part of drag
                    if (e.target.contentEditable === 'true' || e.button === 2 || item.dataset.isDragging === 'true') {
                        return;
                    }
                    const noteId = item.dataset.noteId;
                    if (noteId) {
                        openNote(noteId);
                    }
                });
                
                // Prevent click when dragging
                item.addEventListener('dragstart', (e) => {
                    item.dataset.isDragging = 'true';
                });
                
                item.addEventListener('dragend', (e) => {
                    setTimeout(() => {
                        delete item.dataset.isDragging;
                    }, 100);
                });
            });
        }

        // Handle canvas click - attach after render
        function attachCanvasClickHandlers() {
            document.querySelectorAll('.canvas-tree-item').forEach(item => {
                // Click handler
                item.addEventListener('click', (e) => {
                    // Don't open if editing, right-clicking, or if it's part of drag
                    if (e.target.contentEditable === 'true' || e.button === 2 || item.dataset.isDragging === 'true') {
                        return;
                    }
                    const canvasId = item.dataset.canvasId;
                    if (canvasId) {
                        openCanvas(canvasId);
                    }
                });
                
                // Prevent click when dragging
                item.addEventListener('dragstart', (e) => {
                    item.dataset.isDragging = 'true';
                });
                
                item.addEventListener('dragend', (e) => {
                    setTimeout(() => {
                        delete item.dataset.isDragging;
                    }, 100);
                });
            });
        }

        async function openCanvas(canvasId) {
            // Load canvas data
            const res = await fetch(`/api/canvases/${canvasId}`);
            const canvasData = await res.json();
            
            // Update current canvas
            currentCanvasId = canvasId;
            
            // Show canvas wrapper, hide others
            document.getElementById('canvas-wrapper').style.display = 'block';
            document.getElementById('welcome-screen').style.display = 'none';
            document.getElementById('note-panel').style.display = 'none';
            
            // Update current cards to show only cards from this canvas
            cards = canvasData.cards || [];
            renderCards();
            
            // Update page title and indicator
            document.title = `${canvasData.name} - System Sight`;
            document.getElementById('canvas-indicator').style.display = 'flex';
            document.getElementById('canvas-name').textContent = canvasData.name;
        }

        function toggleFolder(folderId, event) {
            // If editing, don't toggle
            const target = event.target;
            if (target.isContentEditable || target.closest('[contenteditable="true"]')) return;
            
            if (expandedFolders.has(folderId)) {
                expandedFolders.delete(folderId);
            } else {
                expandedFolders.add(folderId);
            }
            renderFolderTree();
        }

        function handleKeyDown(event, element) {
            if (event.key === 'Enter') {
                event.preventDefault();
                element.blur();
            } else if (event.key === 'Escape') {
                element.textContent = element.dataset.originalText;
                element.contentEditable = 'false';
                element.blur();
            }
        }

        async function saveItemName(id, type, element) {
            if (element.contentEditable === 'false') return;
            
            const newName = element.textContent.trim();
            if (!newName) {
                element.textContent = element.dataset.originalText;
                element.contentEditable = 'false';
                return;
            }

            let endpoint;
            if (type === 'folder') {
                endpoint = `/api/folders/${id}`;
            } else if (type === 'note') {
                endpoint = `/api/notes/${id}`;
            } else if (type === 'canvas') {
                endpoint = `/api/canvases/${id}`;
            }
            
            const res = await fetch(endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name: newName })
            });

            element.contentEditable = 'false';
            
            // Update folder cards if it's a folder or note
            if (type === 'folder') {
                await updateFolderCards(id);
            } else if (type === 'note') {
                // Get note to find its folder
                const noteData = await res.json();
                if (noteData.folder_id) {
                    await updateFolderCards(noteData.folder_id);
                }
            } else if (type === 'canvas') {
                // Update canvas name in indicator if this is the current canvas
                if (currentCanvasId == id) {
                    document.getElementById('canvas-name').textContent = newName;
                    document.title = `${newName} - System Sight`;
                }
            }
            
            await loadFolders();
            
            // Reload current canvas if we're viewing one
            if (currentCanvasId && type === 'canvas' && currentCanvasId == id) {
                await openCanvas(currentCanvasId);
            }
        }

        // Enable inline editing on double click
        document.addEventListener('dblclick', (e) => {
            if (e.target.classList.contains('tree-item-name')) {
                e.target.dataset.originalText = e.target.textContent;
                e.target.contentEditable = 'true';
                e.target.closest('.tree-item').classList.add('editing');
                e.target.focus();
                
                // Select text
                const range = document.createRange();
                range.selectNodeContents(e.target);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        });

        // Remove editing class when done
        document.addEventListener('focusout', (e) => {
            if (e.target.classList.contains('tree-item-name')) {
                e.target.closest('.tree-item')?.classList.remove('editing');
            }
        });

        // Context Menu
        document.addEventListener('contextmenu', (e) => {
            const treeItem = e.target.closest('.tree-item');
            if (treeItem) {
                e.preventDefault();
                contextMenuTarget = treeItem;
                
                // Update move text based on item type
                const type = treeItem.dataset.type;
                console.log('Context menu on type:', type, 'element:', treeItem);
                
                const moveText = document.getElementById('context-move-text');
                if (type === 'folder') {
                    moveText.textContent = 'Move folder to...';
                } else if (type === 'note') {
                    moveText.textContent = 'Move note to...';
                } else {
                    moveText.textContent = 'Move to...';
                }
                
                // Show/hide status option based on type (only for notes)
                const statusMenuItem = document.querySelector('.context-menu-item[onclick*="status"]');
                if (statusMenuItem) {
                    statusMenuItem.style.display = type === 'note' ? 'flex' : 'none';
                }
                
                // Position context menu
                contextMenu.style.left = `${e.clientX}px`;
                contextMenu.style.top = `${e.clientY}px`;
                contextMenu.classList.add('active');
                
                // Adjust if menu goes off screen
                setTimeout(() => {
                    const rect = contextMenu.getBoundingClientRect();
                    if (rect.right > window.innerWidth) {
                        contextMenu.style.left = `${window.innerWidth - rect.width - 10}px`;
                    }
                    if (rect.bottom > window.innerHeight) {
                        contextMenu.style.top = `${window.innerHeight - rect.height - 10}px`;
                    }
                }, 0);
            }
        });

        // Close context menu on click outside
        document.addEventListener('click', (e) => {
            if (!contextMenu.contains(e.target)) {
                contextMenu.classList.remove('active');
            }
        });

        async function contextMenuAction(action) {
            contextMenu.classList.remove('active');
            
            if (!contextMenuTarget) return;
            
            const type = contextMenuTarget.dataset.type;
            const id = type === 'folder' ? contextMenuTarget.dataset.id : contextMenuTarget.dataset.noteId;
            
            switch(action) {
                case 'newNote':
                    if (type === 'folder') {
                        await createNote(id);
                    } else {
                        await createNote();
                    }
                    break;
                    
                case 'newFolder':
                    await createFolder();
                    break;
                
                case 'status':
                    openStatusModal(type, id);
                    break;
                    
                case 'move':
                    openMoveModal(type, id);
                    break;
                    
                case 'rename':
                    const nameEl = contextMenuTarget.querySelector('.tree-item-name');
                    nameEl.dataset.originalText = nameEl.textContent;
                    nameEl.contentEditable = 'true';
                    contextMenuTarget.classList.add('editing');
                    nameEl.focus();
                    
                    const range = document.createRange();
                    range.selectNodeContents(nameEl);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                    break;
                    
                case 'delete':
                    if (type === 'folder') {
                        if (confirm('Delete this folder and all its contents?')) {
                            await fetch(`/api/folders/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            // Update folder cards on canvas
                            await updateFolderCards(id);
                            loadFolders();
                        }
                    } else {
                        if (confirm('Delete this note?')) {
                            // Get note info to find its folder
                            const noteRes = await fetch(`/api/notes/${id}`);
                            const noteData = await noteRes.json();
                            const noteFolderId = noteData.folder_id;
                            
                            await fetch(`/api/notes/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            
                            // Update folder cards if note was in a folder
                            if (noteFolderId) {
                                await updateFolderCards(noteFolderId);
                            }
                            
                            loadFolders();
                        }
                    }
                    break;
            }
            
            contextMenuTarget = null;
        }

        // Status Modal Functions
        let statusChangeType = null;
        let statusChangeId = null;
        
        function openStatusModal(type, id) {
            statusChangeType = type;
            statusChangeId = id;
            document.getElementById('status-modal').style.display = 'flex';
        }
        
        function closeStatusModal() {
            document.getElementById('status-modal').style.display = 'none';
            statusChangeType = null;
            statusChangeId = null;
        }
        
        async function changeStatusFromMenu(status) {
            if (!statusChangeType || !statusChangeId) return;
            
            try {
                if (statusChangeType === 'note') {
                    await fetch(`/api/notes/${statusChangeId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ status: status })
                    });
                } else if (statusChangeType === 'folder') {
                    await fetch(`/api/folders/${statusChangeId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ status: status })
                    });
                }
                
                // Reload folders to update sidebar
                await loadFolders();
                
                // Update cards on canvas
                renderCards();
                
                closeStatusModal();
            } catch (e) {
                console.error('Error changing status:', e);
                alert('Failed to change status');
            }
        }

        // Drag note or folder from sidebar to canvas
        let draggedNoteId = null;
        let draggedFolderId = null;
        let draggedFolderData = null;
        
        document.addEventListener('dragstart', (e) => {
            const treeItem = e.target.closest('.tree-item');
            if (!treeItem || treeItem.getAttribute('draggable') !== 'true') return;
            
            // Check if it's a note
            if (treeItem.dataset.noteId) {
                draggedNoteId = treeItem.dataset.noteId;
                draggedFolderId = null;
                draggedFolderData = null;
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', draggedNoteId);
                treeItem.style.opacity = '0.5';
                console.log('Drag note started:', draggedNoteId);
            }
            // Check if it's a folder
            else if (treeItem.dataset.type === 'folder' && treeItem.dataset.id) {
                draggedFolderId = treeItem.dataset.id;
                draggedNoteId = null;
                // Find folder data from folders array
                draggedFolderData = findFolderById(folders, parseInt(draggedFolderId));
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', draggedFolderId);
                treeItem.style.opacity = '0.5';
                console.log('Drag folder started:', draggedFolderId, draggedFolderData);
            }
        });

        document.addEventListener('dragend', (e) => {
            const treeItem = e.target.closest('.tree-item');
            if (treeItem) {
                treeItem.style.opacity = '1';
                console.log('Drag ended');
            }
        });

        // Helper function to find folder by id recursively
        function findFolderById(folderList, id) {
            for (const folder of folderList) {
                if (folder.id === id) return folder;
                if (folder.children) {
                    const found = findFolderById(folder.children, id);
                    if (found) return found;
                }
            }
            return null;
        }

        canvas.addEventListener('dragover', (e) => {
            if (draggedNoteId || draggedFolderId) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            }
        });
        
        canvas.addEventListener('drop', async (e) => {
            e.preventDefault();
            
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX - rect.left - panX) / zoom;
            const y = (e.clientY - rect.top - panY) / zoom;

            try {
                // Handle note drop
                if (draggedNoteId) {
                    console.log('Drop event, noteId:', draggedNoteId);
                    const res = await fetch('/api/cards', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ 
                            note_id: draggedNoteId, 
                            position_x: x, 
                            position_y: y,
                            canvas_id: currentCanvasId
                        })
                    });

                    const card = await res.json();
                    cards.push(card);
                    renderCards();
                    console.log('Card created:', card);
                    
                    // Auto-connect to parent folder if note belongs to a folder and folder card exists on canvas
                    if (card.note && card.note.folder_id) {
                        const parentFolderCard = cards.find(c => c.folder && c.folder.id == card.note.folder_id);
                        if (parentFolderCard) {
                            console.log('Auto-connecting note to parent folder card:', parentFolderCard.id);
                            try {
                                await fetch(`/api/cards/${parentFolderCard.id}/links`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({ linked_note_id: card.note.id })
                                });
                                
                                // Update local state
                                if (!parentFolderCard.linked_notes) {
                                    parentFolderCard.linked_notes = [];
                                }
                                parentFolderCard.linked_notes.push({ id: card.note.id, type: 'note' });
                                renderConnections();
                                console.log('Auto-connection to folder created successfully');
                            } catch (error) {
                                console.error('Error creating auto-connection to folder:', error);
                            }
                        }
                    }
                }
                // Handle folder drop
                else if (draggedFolderId) {
                    console.log('Drop event, folderId:', draggedFolderId);
                    const res = await fetch('/api/cards/folder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ 
                            folder_id: draggedFolderId, 
                            position_x: x, 
                            position_y: y,
                            canvas_id: currentCanvasId
                        })
                    });

                    const folderCard = await res.json();
                    cards.push(folderCard);
                    renderCards();
                    console.log('Folder card created:', folderCard);
                    
                    // Auto-connect to parent folder if exists on canvas
                    if (draggedFolderData && draggedFolderData.parent_id) {
                        const parentFolderCard = cards.find(c => c.folder && c.folder.id == draggedFolderData.parent_id);
                        if (parentFolderCard) {
                            console.log('Auto-connecting to parent folder card:', parentFolderCard.id);
                            try {
                                await fetch(`/api/cards/${parentFolderCard.id}/links`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({ linked_folder_id: folderCard.folder.id })
                                });
                                
                                // Update local state
                                if (!parentFolderCard.linked_notes) {
                                    parentFolderCard.linked_notes = [];
                                }
                                parentFolderCard.linked_notes.push({ id: folderCard.folder.id, type: 'folder' });
                                renderConnections();
                                console.log('Auto-connection created successfully');
                            } catch (error) {
                                console.error('Error creating auto-connection:', error);
                            }
                        }
                    }
                    
                    // Auto-create cards for child folders and connect them
                    if (draggedFolderData && draggedFolderData.children && draggedFolderData.children.length > 0) {
                        console.log('Creating child folder cards...', draggedFolderData.children.length);
                        const offsetX = 250; // Horizontal spacing
                        const offsetY = 50; // Vertical spacing between children
                        
                        for (let i = 0; i < draggedFolderData.children.length; i++) {
                            const childFolder = draggedFolderData.children[i];
                            const childX = x + offsetX;
                            const childY = y + (i * offsetY);
                            
                            try {
                                // Create child folder card
                                const childRes = await fetch('/api/cards/folder', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({ folder_id: childFolder.id, position_x: childX, position_y: childY })
                                });
                                
                                const childCard = await childRes.json();
                                cards.push(childCard);
                                console.log('Child folder card created:', childCard);
                                
                                // Create connection from parent to child
                                await fetch(`/api/cards/${folderCard.id}/links`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({ linked_folder_id: childFolder.id })
                                });
                                
                                // Update local state
                                if (!folderCard.linked_notes) {
                                    folderCard.linked_notes = [];
                                }
                                folderCard.linked_notes.push({ id: childFolder.id, type: 'folder' });
                                console.log('Connection to child folder created');
                                
                            } catch (error) {
                                console.error('Error creating child folder card:', error);
                            }
                        }
                        
                        // Re-render everything
                        renderCards();
                        renderConnections();
                    }
                }
            } catch (error) {
                console.error('Error creating card:', error);
            }
            
            draggedNoteId = null;
            draggedFolderId = null;
            draggedFolderData = null;
        });

        // Sidebar Drag & Drop Logic
        function allowDrop(ev) {
            ev.preventDefault();
            ev.currentTarget.style.background = '#e0e7ff';
        }

        function resetDragStyle(ev) {
            ev.preventDefault();
            ev.currentTarget.style.background = '';
        }

        async function dropNoteToFolder(ev, folderId) {
            ev.preventDefault();
            ev.stopPropagation();
            ev.currentTarget.style.background = '';
            
            // Handle note drop
            if (draggedNoteId) {
                console.log(`Moving note ${draggedNoteId} to folder ${folderId}`);
                
                try {
                    // Get note info BEFORE moving to know the old folder
                    const noteInfoRes = await fetch(`/api/notes/${draggedNoteId}`);
                    const noteInfo = await noteInfoRes.json();
                    const oldFolderId = noteInfo.folder_id;
                    
                    // Move note to new folder
                    const res = await fetch(`/api/notes/${draggedNoteId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ folder_id: folderId })
                    });

                    if (res.ok) {
                        console.log('Note moved successfully');
                        await loadFolders();
                        
                        // Update OLD folder cards (remove note)
                        if (oldFolderId) {
                            await updateFolderCards(oldFolderId);
                        }
                        
                        // Update NEW folder cards (add note)
                        await updateFolderCards(folderId);
                        
                        // Ensure target folder is expanded
                        if (!expandedFolders.has(folderId)) {
                            expandedFolders.add(folderId);
                            renderFolderTree();
                        }
                    } else {
                        console.error('Failed to move note');
                    }
                } catch (e) {
                    console.error('Error moving note:', e);
                }
            }
            // Handle folder drop
            else if (draggedFolderId) {
                // Prevent dropping folder into itself
                if (draggedFolderId == folderId) {
                    console.warn('Cannot drop folder into itself');
                    return;
                }
                
                // Prevent dropping folder into its own child
                if (isChildFolder(folderId, draggedFolderId)) {
                    alert('Cannot move folder into its own subfolder');
                    return;
                }
                
                console.log(`Moving folder ${draggedFolderId} to folder ${folderId}`);
                
                try {
                    // Move folder to new parent
                    const res = await fetch(`/api/folders/${draggedFolderId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ parent_id: folderId })
                    });

                    if (res.ok) {
                        console.log('Folder moved successfully');
                        await loadFolders();
                        
                        // Ensure target folder is expanded
                        if (!expandedFolders.has(folderId)) {
                            expandedFolders.add(folderId);
                            renderFolderTree();
                        }
                    } else {
                        console.error('Failed to move folder');
                    }
                } catch (e) {
                    console.error('Error moving folder:', e);
                }
            }
        }
        
        // Check if targetFolderId is a child of sourceFolderId (prevent circular nesting)
        function isChildFolder(targetFolderId, sourceFolderId) {
            const findInChildren = (folder) => {
                if (folder.id == targetFolderId) return true;
                if (folder.children) {
                    return folder.children.some(child => findInChildren(child));
                }
                return false;
            };
            
            const sourceFolder = findFolderById(folders, parseInt(sourceFolderId));
            if (!sourceFolder) return false;
            
            if (sourceFolder.children) {
                return sourceFolder.children.some(child => findInChildren(child));
            }
            return false;
        }

        // Update folder cards on canvas when folder content changes
        async function updateFolderCards(folderId) {
            // Find all folder cards for this folder
            const folderCards = cards.filter(c => c.folder && c.folder.id == folderId);
            
            if (folderCards.length === 0) return;
            
            // Fetch updated folder data
            try {
                const res = await fetch(`/api/folders/${folderId}`);
                if (!res.ok) return;
                
                const updatedFolder = await res.json();
                
                // Update each folder card
                folderCards.forEach(card => {
                    card.folder = updatedFolder;
                });
                
                // Re-render cards
                renderCards();
            } catch (e) {
                console.error('Error updating folder cards:', e);
            }
        }

        function renderConnections() {
            const svg = document.getElementById('connections-layer');
            while (svg.firstChild) {
                svg.removeChild(svg.firstChild);
            }
            
            // Debug: Check if connections are being rendered
            // console.log('Rendering connections...', cards.length, 'cards');

            cards.forEach(card => {
                if (card.linked_notes && Array.isArray(card.linked_notes)) {
                    card.linked_notes.forEach(linkedItem => {
                        // Determine if linked item is a note or folder by checking pivot data
                        // If pivot has linked_note_id, it's a note; if linked_folder_id, it's a folder
                        const isNote = linkedItem.pivot && linkedItem.pivot.linked_note_id != null;
                        const isFolder = linkedItem.pivot && linkedItem.pivot.linked_folder_id != null;
                        
                        // Find target card by note_id or folder_id
                        const targetCard = cards.find(c => {
                            if (isNote && c.note && c.note.id == linkedItem.id) return true;
                            if (isFolder && c.folder && c.folder.id == linkedItem.id) return true;
                            return false;
                        });
                        
                        if (targetCard) {
                            try {
                                drawLine(svg, card, targetCard);
                            } catch (e) {
                                console.error('Error drawing line:', e, 'card:', card, 'targetCard:', targetCard);
                            }
                        }
                        // Removed warning - it's normal for linked items to not have cards on canvas
                    });
                }
            });

            if (isConnecting && connectionSource) {
                drawTempLine(svg, connectionSource, connectionTempEnd);
            }
        }

        function drawLine(svg, source, target) {
            const sourceEl = document.querySelector(`.card[data-card-id="${source.id}"]`);
            const targetEl = document.querySelector(`.card[data-card-id="${target.id}"]`);
            
            if (!sourceEl || !targetEl) return;

            const containerRect = canvas.getBoundingClientRect();
            const box1 = sourceEl.getBoundingClientRect();
            
            const x1 = box1.right - containerRect.left;
            const y1 = box1.top + box1.height / 2 - containerRect.top;
            
            const box2 = targetEl.getBoundingClientRect();
            const x2 = box2.left - containerRect.left;
            const y2 = box2.top + box2.height / 2 - containerRect.top;

            const path = appendPath(svg, x1, y1, x2, y2);
            
            // Add interaction for deletion
            path.style.cursor = 'pointer';
            path.innerHTML = '<title>Double-click to delete connection</title>';
            
            path.addEventListener('dblclick', async (e) => {
                e.stopPropagation();
                if (confirm('Delete this connection?')) {
                    try {
                        const targetEntityId = target.note ? target.note.id : (target.folder ? target.folder.id : null);
                        
                        await fetch(`/api/cards/${source.id}/links`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ 
                                linked_note_id: target.note ? target.note.id : null,
                                linked_folder_id: target.folder ? target.folder.id : null
                            })
                        });
                        
                        // Update local state
                        if (source.linked_notes) {
                            source.linked_notes = source.linked_notes.filter(n => n.id != targetEntityId);
                        }
                        renderConnections();
                    } catch (error) {
                        console.error('Error deleting connection:', error);
                    }
                }
            });
        }

        function drawTempLine(svg, source, endPoint) {
            const sourceEl = document.querySelector(`.card[data-card-id="${source.id}"]`);
            if (!sourceEl) return;
            
            const containerRect = canvas.getBoundingClientRect();
            const box1 = sourceEl.getBoundingClientRect();
            
            const x1 = box1.right - containerRect.left;
            const y1 = box1.top + box1.height / 2 - containerRect.top;
            
            const x2 = endPoint.x - containerRect.left;
            const y2 = endPoint.y - containerRect.top;
            
            const path = appendPath(svg, x1, y1, x2, y2);
            path.classList.add('temp');
        }

        function appendPath(svg, x1, y1, x2, y2) {
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.classList.add('connection-line');
            
            // Bezier Curve
            const cx1 = x1 + Math.abs(x2 - x1) / 2;
            const cy1 = y1;
            const cx2 = x2 - Math.abs(x2 - x1) / 2;
            const cy2 = y2;
            
            const d = `M ${x1} ${y1} C ${cx1} ${cy1}, ${cx2} ${cy2}, ${x2} ${y2}`;
            path.setAttribute('d', d);
            
            svg.appendChild(path);
            return path;
        }

        // Render cards
        function renderCards() {
            const emptyState = document.getElementById('empty-state');
            if (cards.length === 0) {
                emptyState.style.display = 'block';
            } else {
                emptyState.style.display = 'none';
            }
            
            // Remove existing cards
            const existingCards = canvas.querySelectorAll('.card');
            existingCards.forEach(card => card.remove());
            
            cards.forEach(card => {
                // Ensure positions are numbers
                card.position_x = parseFloat(card.position_x);
                card.position_y = parseFloat(card.position_y);

                const cardEl = document.createElement('div');
                cardEl.className = 'card' + (card.id == selectedCardId ? ' selected' : '');
                cardEl.dataset.cardId = card.id;
                cardEl.style.left = `${card.position_x * zoom + panX}px`;
                cardEl.style.top = `${card.position_y * zoom + panY}px`;
                cardEl.style.transform = `scale(${zoom})`;
                cardEl.style.transformOrigin = 'top left';
                
                // Check if this is a folder card
                if (card.folder) {
                    // Render folder card
                    // Hide details when zoomed out (zoom < 0.8)
                    const showDetails = zoom >= 0.8;
                    
                    // Show description (max 2 lines) instead of note count
                    const folderDescription = card.folder.description || '';
                    const descriptionPreview = folderDescription.length > 100 ? folderDescription.substring(0, 100) + '...' : folderDescription;
                    
                    const notesListHtml = showDetails && card.folder.notes && card.folder.notes.length > 0 ? `
                        <div class="card-links">
                            ${card.folder.notes.map(note => `
                                <div class="card-link" onclick="openNote(${note.id})">� ${note.name}</div>
                            `).join('')}
                        </div>
                    ` : (showDetails && (!card.folder.notes || card.folder.notes.length === 0) ? '' : '');

                    cardEl.innerHTML = `
                        <div class="card-header">📁 ${card.folder.name}</div>
                        ${showDetails ? `<div class="card-subheader" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">${descriptionPreview}</div>` : ''}
                        ${notesListHtml}
                        <div class="card-actions">
                            <button onclick="openFolder(${card.folder.id})" class="card-btn">View</button>
                            <button onclick="deleteCard(${card.id})" class="card-btn delete">Delete</button>
                        </div>
                        <div class="connection-handle left" data-card-id="${card.id}"></div>
                        <div class="connection-handle right" data-card-id="${card.id}"></div>
                    `;

                    // Connection Drag Logic for folder cards
                    const rightHandle = cardEl.querySelector('.connection-handle.right');
                    if (rightHandle) {
                        rightHandle.addEventListener('mousedown', (e) => {
                            e.stopPropagation();
                            startConnectionDrag(card, e);
                        });
                    }
                } else if (card.note) {
                    // Render note card
                    // Hide details when zoomed out (zoom < 0.8)
                    const showDetails = zoom >= 0.8;
                    const linksHtml = showDetails && card.linked_notes && card.linked_notes.length > 0 ? `
                        <div class="card-links">
                            ${card.linked_notes.map(link => `
                                <div class="card-link" onclick="openNote(${link.id})">🔗 ${link.name}</div>
                            `).join('')}
                        </div>
                    ` : '';

                    cardEl.innerHTML = `
                        <div class="card-header">${card.note.name}</div>
                        ${showDetails ? `<div class="card-subheader">${card.note.content ? card.note.content.substring(0, 100) + '...' : 'Empty note'}</div>` : ''}
                        ${linksHtml}
                        <div class="card-actions">
                            <button onclick="openNote(${card.note.id})" class="card-btn">Edit</button>
                            <button onclick="deleteCard(${card.id})" class="card-btn delete">Delete</button>
                        </div>
                        <div class="connection-handle left" data-card-id="${card.id}"></div>
                        <div class="connection-handle right" data-card-id="${card.id}"></div>
                    `;

                    // Connection Drag Logic (Only from Right Handle) - only for note cards
                    const rightHandle = cardEl.querySelector('.connection-handle.right');
                    if (rightHandle) {
                        rightHandle.addEventListener('mousedown', (e) => {
                            e.stopPropagation();
                            startConnectionDrag(card, e);
                        });
                    }
                }

                // Card Drag Logic
                cardEl.addEventListener('mousedown', (e) => {
                    // Prevent card drag if clicking on connection handle
                    if (e.target.classList.contains('connection-handle')) return;

                    if (e.target.tagName !== 'BUTTON' && !e.target.classList.contains('card-link')) {
                        selectedCardId = card.id;
                        renderCards();
                        
                        draggedCard = card;
                        dragStartX = e.clientX;
                        dragStartY = e.clientY;
                    } else {
                        // Still select if clicking a button/link
                        if (selectedCardId !== card.id) {
                            selectedCardId = card.id;
                            renderCards();
                        }
                    }
                });

                canvas.appendChild(cardEl);
            });
            
            renderConnections();



        }

        // Pan canvas
        canvas.addEventListener('mousedown', (e) => {
            if (!draggedCard && e.target === canvas) {
                if (selectedCardId !== null) {
                    selectedCardId = null;
                    renderCards();
                }
                isDragging = true;
                dragStartX = e.clientX - panX;
                dragStartY = e.clientY - panY;
                canvas.classList.add('dragging');
            }
        });

        document.addEventListener('mousemove', async (e) => {
            if (isDragging) {
                panX = e.clientX - dragStartX;
                panY = e.clientY - dragStartY;
                renderCards();
            } else if (draggedCard) {
                const dx = (e.clientX - dragStartX) / zoom;
                const dy = (e.clientY - dragStartY) / zoom;
                draggedCard.position_x += dx;
                draggedCard.position_y += dy;
                dragStartX = e.clientX;
                dragStartY = e.clientY;
                renderCards();
            } else if (isConnecting) {
                connectionTempEnd = { x: e.clientX, y: e.clientY };
                renderConnections();
            }
        });

        document.addEventListener('mouseup', async (e) => {
            if (isConnecting) {
                // Check if dropped on a card
                const targetEl = document.elementFromPoint(e.clientX, e.clientY);
                const targetCardEl = targetEl ? targetEl.closest('.card') : null;
                
                if (targetCardEl && connectionSource) {
                    // Find card object data
                    const targetId = targetCardEl.dataset.cardId;
                    const targetCard = cards.find(c => c.id == targetId);
                    
                    if (targetCard && targetCard.id !== connectionSource.id) {
                        // Get the target ID (note or folder)
                        const targetEntityId = targetCard.note ? targetCard.note.id : (targetCard.folder ? targetCard.folder.id : null);
                        
                        if (!targetEntityId) {
                            console.error('Target card has no note or folder');
                            isConnecting = false;
                            connectionSource = null;
                            renderCards();
                            return;
                        }
                        
                        // Check if already linked
                        const alreadyLinked = connectionSource.linked_notes && connectionSource.linked_notes.some(n => n.id == targetEntityId);
                        
                        if (!alreadyLinked) {
                            // Create Link
                            console.log('Creating link from', connectionSource, 'to', targetCard);
                            const response = await fetch(`/api/cards/${connectionSource.id}/links`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ 
                                    linked_note_id: targetCard.note ? targetCard.note.id : null,
                                    linked_folder_id: targetCard.folder ? targetCard.folder.id : null
                                })
                            });
                            
                            if (response.ok) {
                                const data = await response.json();
                                console.log('Link created, response:', data);
                                // Update local data with response
                                if (data.linked_notes) {
                                    connectionSource.linked_notes = data.linked_notes;
                                } else {
                                    // Fallback: manually update
                                    if (!connectionSource.linked_notes) connectionSource.linked_notes = [];
                                    connectionSource.linked_notes.push(targetCard.note || targetCard.folder);
                                }
                            } else {
                                console.error('Failed to create link:', await response.text());
                            }
                        } else {
                            console.log('Cards already linked');
                        }
                    }
                }
                
                isConnecting = false;
                connectionSource = null;
                renderCards(); // Re-render to show new link
            }

            if (draggedCard) {
                console.log('Dropping card ID:', draggedCard.id);
                if (!draggedCard.id) {
                    console.error('Card ID is missing!');
                    return;
                }
                await fetch(`/api/cards/${draggedCard.id}/position`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ position_x: draggedCard.position_x, position_y: draggedCard.position_y })
                });
                draggedCard = null;
            }
            isDragging = false;
            canvas.classList.remove('dragging');
        });

        function startConnectionDrag(card, event) {
            isConnecting = true;
            connectionSource = card;
            connectionTempEnd = { x: event.clientX, y: event.clientY };
            // renderConnections called by mousemove
        }



        // Mouse Wheel Zoom (Ctrl + Wheel)
        canvas.addEventListener('wheel', (e) => {
            if (e.ctrlKey) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.1 : 0.1;
                const newZoom = Math.min(Math.max(zoom + delta, 0.3), 2);
                
                // Zoom towards mouse pointer logic (optional, keeping simple for now)
                // Adjusting pan to keep mouse point stable would be better but simple zoom is okay.
                zoom = newZoom;
                renderCards();
            }
        }, { passive: false });

        // Auto Arrange
        // Auto Arrange
        async function autoArrangeCards() {
            if (cards.length === 0) return;

            // Reset view to default
            panX = 0;
            panY = 0;
            zoom = 1;

            const gap = 50; 
            const cardWidth = 320; // Approx card width
            const cardHeight = 200; // Approx card height (variable but this is for grid)
            
            const cols = Math.ceil(Math.sqrt(cards.length));
            const rows = Math.ceil(cards.length / cols);

            const gridWidth = cols * cardWidth + (cols - 1) * gap;
            const gridHeight = rows * cardHeight + (rows - 1) * gap;

            // Calculate starting position to center the grid in the viewport
            const startX = (window.innerWidth - gridWidth) / 2;
            const startY = (window.innerHeight - gridHeight) / 2;
            
            // Allow for left sidebar offset if visible (approx 280px)
            // But sidebar is overlay or flex? It's flex.
            // window.innerWidth includes sidebar.
            // The canvas is fully wide? Let's check CSS.
            // #main-content { flex: 1; position: relative; }
            // So window.innerWidth might be too wide?
            // Safer to use canvas.clientWidth
            const centerX = canvas.clientWidth / 2;
            const centerY = canvas.clientHeight / 2;
            
            const gridStartX = centerX - gridWidth / 2;
            const gridStartY = centerY - gridHeight / 2;

            // Sort by ID or Name to have deterministic order
            cards.sort((a, b) => a.id - b.id);

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const col = i % cols;
                const row = Math.floor(i / cols);
                
                const newX = gridStartX + col * (cardWidth + gap);
                const newY = gridStartY + row * (cardHeight + gap);

                card.position_x = newX;
                card.position_y = newY;
                
                // Persist change
                await fetch(`/api/cards/${card.id}/position`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ position_x: newX, position_y: newY })
                });
            }
            renderCards();
        }

        // Zoom
        function zoomIn() {
            zoom = Math.min(zoom + 0.1, 2);
            renderCards();
        }

        function zoomOut() {
            zoom = Math.max(zoom - 0.1, 0.3);
            renderCards();
        }

        function resetZoom() {
            zoom = 1;
            panX = 0;
            panY = 0;
            renderCards();
        }

        function collapseAll() {
            expandedFolders.clear();
            renderFolderTree();
        }

        async function createFolder() {
            // Create folder with default name
            const res = await fetch('/api/folders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name: 'Untitled' })
            });

            const newFolder = await res.json();
            await loadFolders();
            
            // Find and edit the new folder
            setTimeout(() => {
                const folderElements = document.querySelectorAll('.tree-item[data-type="folder"]');
                folderElements.forEach(el => {
                    if (el.dataset.id == newFolder.id) {
                        const nameEl = el.querySelector('.tree-item-name');
                        nameEl.dataset.originalText = nameEl.textContent;
                        nameEl.contentEditable = 'true';
                        el.classList.add('editing');
                        nameEl.focus();
                        
                        // Select all text
                        const range = document.createRange();
                        range.selectNodeContents(nameEl);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                });
            }, 100);
        }

        async function createCanvas() {
            const name = prompt('Canvas name:', 'New Canvas');
            if (!name) return;
            
            const res = await fetch('/api/canvases', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name: name })
            });

            const newCanvas = await res.json();
            await loadFolders();
            
            // Find and edit the new canvas
            setTimeout(() => {
                const canvasElements = document.querySelectorAll('.tree-item[data-type="canvas"]');
                canvasElements.forEach(el => {
                    if (el.dataset.canvasId == newCanvas.id) {
                        const nameEl = el.querySelector('.tree-item-name');
                        nameEl.dataset.originalText = nameEl.textContent;
                        nameEl.contentEditable = 'true';
                        el.classList.add('editing');
                        nameEl.focus();
                        
                        // Select all text
                        const range = document.createRange();
                        range.selectNodeContents(nameEl);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                });
            }, 100);
        }

        async function createNote(folderId = null) {
            console.log('Creating new note...', folderId ? `in folder ${folderId}` : 'in root');
            try {
                // Create note with default name
                const res = await fetch('/api/notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        name: 'Untitled', 
                        content: '',
                        folder_id: folderId 
                    })
                });

                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }

                const newNote = await res.json();
                console.log('Note created:', newNote);
                await loadFolders();
                
                // Update folder cards if note was created in a folder
                if (folderId) {
                    await updateFolderCards(folderId);
                }
                
                // Find and edit the new note
                setTimeout(() => {
                    console.log('Searching for new note element:', newNote.id);
                    const noteElements = document.querySelectorAll('.tree-item[data-type="note"]');
                    let found = false;
                    noteElements.forEach(el => {
                        if (el.dataset.noteId == newNote.id) {
                            console.log('Found new note element, starting edit');
                            found = true;
                            const nameEl = el.querySelector('.tree-item-name');
                            nameEl.dataset.originalText = nameEl.textContent;
                            nameEl.contentEditable = 'true';
                            el.classList.add('editing');
                            nameEl.focus();
                            
                            // Select all text
                            const range = document.createRange();
                            range.selectNodeContents(nameEl);
                            const sel = window.getSelection();
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }
                    });
                    if (!found) console.warn('New note element not found in DOM');
                    
                    // If created in a folder, make sure it's expanded
                    if (folderId) {
                         if (!expandedFolders.has(folderId)) {
                             expandedFolders.add(folderId);
                             renderFolderTree();
                         }
                    }
                }, 100);
            } catch (e) {
                console.error('Error creating note:', e);
                alert('Failed to create note: ' + e.message);
            }
        }

        async function openNote(noteId) {
            // Check if note is already open
            let existingNote = openedNotes.find(n => n.id == noteId);
            
            if (!existingNote) {
                const res = await fetch(`/api/notes/${noteId}`);
                const noteData = await res.json();
                existingNote = {
                    id: noteId,
                    data: noteData,
                    mode: 'view',
                    scrollPos: 0
                };
                openedNotes.push(existingNote);
            }
            
            activeNoteId = noteId;
            
            // Show note panel, hide others
            document.getElementById('welcome-screen').style.display = 'none';
            document.getElementById('canvas-wrapper').style.display = 'none';
            document.getElementById('note-panel').style.display = 'flex';

            renderNoteTabs();
            renderActiveNoteContent();
        }

        function renderNoteTabs() {
            const tabsContainer = document.getElementById('note-tabs');
            tabsContainer.innerHTML = openedNotes.map(note => `
                <div class="note-tab ${note.id == activeNoteId ? 'active' : ''}" onclick="switchNote(${note.id})">
                    <span class="tab-name">${note.data.name}</span>
                    <span class="tab-close" onclick="closeNoteTab(${note.id}, event)">&times;</span>
                </div>
            `).join('');
        }

        function switchNote(noteId) {
            if (activeNoteId == noteId) return;
            
            // Save current scroll position if needed (optional enhancement)
            const activeNote = openedNotes.find(n => n.id == activeNoteId);
            if (activeNote) {
                const container = document.querySelector('#active-note-container > div:last-child');
                if (container) activeNote.scrollPos = container.scrollTop;
            }

            activeNoteId = noteId;
            renderNoteTabs();
            renderActiveNoteContent();
            
            // Restore scroll position
            setTimeout(() => {
                const newActive = openedNotes.find(n => n.id == activeNoteId);
                const container = document.querySelector('#active-note-container > div:last-child');
                if (container && newActive) container.scrollTop = newActive.scrollPos;
            }, 0);
        }

        function closeNoteTab(noteId, event) {
            if (event) event.stopPropagation();
            
            const index = openedNotes.findIndex(n => n.id == noteId);
            if (index === -1) return;
            
            openedNotes.splice(index, 1);
            
            if (openedNotes.length === 0) {
                activeNoteId = null;
                document.getElementById('note-panel').style.display = 'none';
                document.getElementById('welcome-screen').style.display = 'flex';
            } else if (activeNoteId == noteId) {
                // Switch to adjacent tab
                const nextNote = openedNotes[index] || openedNotes[index - 1];
                switchNote(nextNote.id);
            } else {
                renderNoteTabs();
            }
        }

        function renderActiveNoteContent() {
            const noteObj = openedNotes.find(n => n.id == activeNoteId);
            if (!noteObj) return;
            
            const note = noteObj.data;
            const container = document.getElementById('active-note-container');
            const notePanel = document.getElementById('note-panel');
            
            // Sync versions for history feature
            notePanel.dataset.versions = JSON.stringify(note.versions || []);
            notePanel.dataset.noteId = note.id;
            
            const statusColors = {
                'none': 'transparent',
                'draft': '#ef4444',
                'improving': '#f59e0b',
                'standardized': '#10b981'
            };
            const currentColor = statusColors[note.status || 'none'];
            const showStatusIndicator = note.status && note.status !== 'none';
            
            // Render markdown content
            const renderedContent = note.content ? marked.parse(note.content) : '';

            container.innerHTML = `
                <div class="modal-header">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            ${note.name}
                            ${showStatusIndicator ? `<span style="width: 12px; height: 12px; border-radius: 50%; background: ${currentColor};" title="Status indicator"></span>` : ''}
                            <span style="font-size: 11px; padding: 2px 6px; border-radius: 4px; background: #e0e0e0; color: #555;">v${note.current_version || 1}</span>
                        </h3>
                    </div>
                    <div class="modal-header-actions">
                        <button onclick="toggleHistory()" class="btn btn-secondary" title="View History">
                            <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3"/></svg>
                        </button>
                        <button onclick="togglePreview()" class="btn btn-secondary" id="preview-btn" style="display: none;" title="Toggle Preview">
                            <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/></svg>
                        </button>
                        <button onclick="editNote(${note.id})" class="btn btn-primary" id="edit-btn">Edit</button>
                        <button onclick="closeNotePanel()" class="btn btn-secondary">Close All</button>
                    </div>
                </div>
                <div style="flex: 1; overflow-y: auto; padding: 24px 24px 40px 24px; max-width: 1400px; margin: 0 auto; width: 100%;background-color: #fff;">
                    <div class="note-content-view" id="note-view">${renderedContent || '<i style="color: #999;">Empty note</i>'}</div>
                    <div id="edit-container" style="display: none; height: 100%;">
                        <!-- Status Color Picker (Moved to Top) -->
                        <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
                            <label style="font-size: 12px; color: #666; font-weight: 500;">Status:</label>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="selectStatus('none')" class="status-color-btn" data-status="none" style="width: 28px; height: 28px; border-radius: 6px; border: 2px solid ${!note.status || note.status === 'none' ? '#333' : '#ddd'}; background: white; cursor: pointer; transition: all 0.15s; position: relative;" title="None">
                                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); fill: #999;"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/></svg>
                                </button>
                                <button type="button" onclick="selectStatus('draft')" class="status-color-btn" data-status="draft" style="width: 28px; height: 28px; border-radius: 6px; border: 2px solid ${note.status === 'draft' ? '#333' : '#ddd'}; background: #ef4444; cursor: pointer; transition: all 0.15s;" title="Red - Issues/Draft"></button>
                                <button type="button" onclick="selectStatus('improving')" class="status-color-btn" data-status="improving" style="width: 28px; height: 28px; border-radius: 6px; border: 2px solid ${note.status === 'improving' ? '#333' : '#ddd'}; background: #f59e0b; cursor: pointer; transition: all 0.15s;" title="Yellow - In Progress"></button>
                                <button type="button" onclick="selectStatus('standardized')" class="status-color-btn" data-status="standardized" style="width: 28px; height: 28px; border-radius: 6px; border: 2px solid ${note.status === 'standardized' ? '#333' : '#ddd'}; background: #10b981; cursor: pointer; transition: all 0.15s;" title="Green - Complete"></button>
                            </div>
                            <input type="hidden" id="note-status" value="${note.status || 'none'}">
                        </div>

                        <!-- Markdown Toolbar -->
                        <div style="display: flex; gap: 4px; padding: 8px; background: #f6f8fa; border: 1px solid #e5e5e5; border-bottom: none; border-radius: 6px 6px 0 0;">
                            <button type="button" onclick="insertMarkdown('**', '**', 'bold text')" class="md-btn" title="Bold (Ctrl+B)">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M13.5,15.5H10V12.5H13.5A1.5,1.5 0 0,1 15,14A1.5,1.5 0 0,1 13.5,15.5M10,6.5H13A1.5,1.5 0 0,1 14.5,8A1.5,1.5 0 0,1 13,9.5H10M15.6,10.79C16.57,10.11 17.25,9 17.25,8C17.25,5.74 15.5,4 13.25,4H7V18H14.04C16.14,18 17.75,16.3 17.75,14.21C17.75,12.69 16.89,11.39 15.6,10.79Z"/></svg>
                            </button>
                            <button type="button" onclick="insertMarkdown('*', '*', 'italic text')" class="md-btn" title="Italic (Ctrl+I)">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M10,4V7H12.21L8.79,15H6V18H14V15H11.79L15.21,7H18V4H10Z"/></svg>
                            </button>
                            <button type="button" onclick="insertMarkdown('~~', '~~', 'strikethrough')" class="md-btn" title="Strikethrough">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M23,12V14H18.61C19.61,16.14 19.56,22 12.38,22C4.05,22.05 4.37,15.5 4.37,15.5L8.34,15.55C8.37,18.92 11.5,18.92 12.12,18.88C12.76,18.83 15.15,18.84 15.34,16.5C15.42,15.41 14.32,14.58 13.12,14H1V12H23M19.41,7.89L15.43,7.86C15.43,7.86 15.6,5.09 12.15,5.08C8.7,5.06 9,7.28 9,7.56C9.04,7.84 9.34,9.22 12,9.88H5.71C5.71,9.88 2.22,3.15 10.74,2C19.45,0.8 19.43,7.91 19.41,7.89Z"/></svg>
                            </button>
                            <div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
                            <button type="button" onclick="triggerWordImport()" class="md-btn" title="Import Word Document" style="color: #2b579a;">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M15.2,18.74L14.08,12.45L12.03,18.74H10.74L8.7,12.45L7.58,18.74H6.33L8.17,10.26H9.59L11.39,15.71L13.18,10.26H14.6L16.44,18.74H15.2Z"/></svg>
                            </button>
                            <div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
                            <button type="button" onclick="insertHeading(1)" class="md-btn" title="Heading 1">H1</button>
                            <button type="button" onclick="insertHeading(2)" class="md-btn" title="Heading 2">H2</button>
                            <button type="button" onclick="insertHeading(3)" class="md-btn" title="Heading 3">H3</button>
                            <div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
                            <button type="button" onclick="insertInlineCode()" class="md-btn" title="Inline Code">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M8,3A2,2 0 0,0 6,5V9A2,2 0 0,1 4,11H3V13H4A2,2 0 0,1 6,15V19A2,2 0 0,0 8,21H10V19H8V14A2,2 0 0,0 6,12A2,2 0 0,0 8,10V5H10V3M16,3A2,2 0 0,1 18,5V9A2,2 0 0,0 20,11H21V13H20A2,2 0 0,0 18,15V19A2,2 0 0,1 16,21H14V19H16V14A2,2 0 0,1 18,12A2,2 0 0,1 16,10V5H14V3H16Z"/></svg>
                            </button>
                            <button type="button" onclick="insertCodeBlock()" class="md-btn" title="Code Block">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M14.6,16.6L19.2,12L14.6,7.4L16,6L22,12L16,18L14.6,16.6M9.4,16.6L4.8,12L9.4,7.4L8,6L2,12L8,18L9.4,16.6Z"/></svg>
                            </button>
                            <div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
                            <button type="button" onclick="insertList('- ')" class="md-btn" title="Bullet List">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M7,5H21V7H7V5M7,13V11H21V13H7M4,4.5A1.5,1.5 0 0,1 5.5,6A1.5,1.5 0 0,1 4,7.5A1.5,1.5 0 0,1 2.5,6A1.5,1.5 0 0,1 4,4.5M4,10.5A1.5,1.5 0 0,1 5.5,12A1.5,1.5 0 0,1 4,13.5A1.5,1.5 0 0,1 2.5,12A1.5,1.5 0 0,1 4,10.5M7,19V17H21V19H7M4,16.5A1.5,1.5 0 0,1 5.5,18A1.5,1.5 0 0,1 4,19.5A1.5,1.5 0 0,1 2.5,18A1.5,1.5 0 0,1 4,16.5Z"/></svg>
                            </button>
                            <button type="button" onclick="insertList('1. ')" class="md-btn" title="Numbered List">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M7,13V11H21V13H7M7,19V17H21V19H7M7,7V5H21V7H7M3,8V5H2V4H4V8H3M2,17V16H5V20H2V19H4V18.5H3V17.5H4V17H2M4.25,10A0.75,0.75 0 0,1 5,10.75C5,10.95 4.92,11.14 4.79,11.27L3.12,13H5V14H2V13.08L4,11H2V10H4.25Z"/></svg>
                            </button>
                            <button type="button" onclick="insertChecklist()" class="md-btn" title="Checklist">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8L10,17Z"/></svg>
                            </button>
                            <div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
                            <button type="button" onclick="insertLink()" class="md-btn" title="Link">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z"/></svg>
                            </button>
                            <button type="button" onclick="openNoteLinkSelector()" class="md-btn" title="Link to Note" style="color: #6366f1;">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M12.5,14.5L14,15.5L12,17V19H14.5L16.5,17L21.5,22L22.5,21L17.5,16L19,14H21V11.5L22.5,10L17.5,5L16,6.5V9H14L12.5,7.5V11H10.5V13H12.5V14.5M11.5,3H12.5V5H11.5V3M20,3H21V5H20V3M20,19H21V21H20V19M3.5,14.5L5,13.5L7,15V13H9V12.5L10.5,11V7.5L9,6V4H6.5L4.5,6L2,3.5L1,4.5L6,9.5L4.5,11V14.5H3.5M4.5,19V21H3.5V19H4.5M4.5,3H3.5V5H4.5V3Z"/></svg>
                            </button>
                            <button type="button" onclick="insertQuote()" class="md-btn" title="Quote">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M14,17H17L19,13V7H13V13H16M6,17H9L11,13V7H5V13H8L6,17Z"/></svg>
                            </button>
                            <div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
                            <button type="button" onclick="openResourceLibraryForInsert()" class="md-btn" title="Insert from Resource Library" style="color: #0066cc;">
                                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M8.5,13.5L11,16.5L14.5,12L19,18H5L8.5,13.5Z"/></svg>
                            </button>
                        </div>
                        <div class="split-editor">
                            <div class="editor-side">
                                <textarea id="note-content" oninput="updatePreview()">${note.content || ''}</textarea>
                            </div>
                            <div class="preview-side note-content-view" id="preview-view"></div>
                        </div>
                        
                    </div>
                    
                    <div id="history-container" style="display: none; padding-top: 16px; border-top: 1px solid #e5e5e5; margin-top: 16px;">
                        <h4 style="font-size: 14px; margin-bottom: 12px;">Version History</h4>
                        <div id="history-list"></div>
                    </div>
                </div>
            `;
            
            // Store note data
            notePanel.dataset.noteId = activeNoteId;
            notePanel.dataset.versions = JSON.stringify(note.versions || []);
            
            // Show note panel, hide others
            document.getElementById('welcome-screen').style.display = 'none';
            document.getElementById('canvas-wrapper').style.display = 'none';
            notePanel.style.display = 'flex';
        }
        
        function closeNotePanel() {
            if (openedNotes.length > 0 && !confirm('Close all open note tabs? Any unsaved changes may be lost.')) {
                return;
            }
            
            openedNotes = [];
            activeNoteId = null;
            
            const notePanel = document.getElementById('note-panel');
            notePanel.style.display = 'none';
            
            // If a canvas is currently open, show it; otherwise show welcome screen
            if (currentCanvasId) {
                document.getElementById('canvas-wrapper').style.display = 'block';
                document.getElementById('welcome-screen').style.display = 'none';
            } else {
                document.getElementById('welcome-screen').style.display = 'flex';
                document.getElementById('canvas-wrapper').style.display = 'none';
            }
        }

        function editNote(noteId) {
            const viewEl = document.getElementById('note-view');
            const editContainer = document.getElementById('edit-container');
            const editBtn = document.getElementById('edit-btn');
            
            viewEl.style.display = 'none';
            editContainer.style.display = 'block';
            
            const textarea = document.getElementById('note-content');
            textarea.focus();
            
            // Initialize preview and height
            updatePreview();
            autoExpandTextarea(textarea);
            
            editBtn.textContent = 'Save';
            editBtn.onclick = () => saveNote(noteId);
        }
        
        function autoExpandTextarea(el) {
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        }

        function updatePreview() {
            const textarea = document.getElementById('note-content');
            const content = textarea.value;
            const previewView = document.getElementById('preview-view');
            const renderedContent = content ? marked.parse(content) : '<i style="color: #999;">Empty note</i>';
            previewView.innerHTML = renderedContent;
            
            // Also update height
            autoExpandTextarea(textarea);
        }
        
        let isPreviewMode = false;
        function togglePreview() {
            const editContainer = document.getElementById('edit-container');
            const previewView = document.getElementById('preview-view');
            const previewBtn = document.getElementById('preview-btn');
            
            isPreviewMode = !isPreviewMode;
            
            if (isPreviewMode) {
                editContainer.style.display = 'none';
                previewView.style.display = 'block';
                previewBtn.textContent = 'Edit';
            } else {
                editContainer.style.display = 'block';
                previewView.style.display = 'none';
                previewBtn.textContent = 'Preview';
            }
        }
        
        // Markdown helper functions
        function insertMarkdown(before, after, placeholder) {
            const textarea = document.getElementById('note-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const textToInsert = selectedText || placeholder;
            const newText = before + textToInsert + after;
            
            textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
            
            // Set cursor position
            if (selectedText) {
                textarea.selectionStart = start;
                textarea.selectionEnd = start + newText.length;
            } else {
                textarea.selectionStart = start + before.length;
                textarea.selectionEnd = start + before.length + textToInsert.length;
            }
            
            textarea.focus();
            updatePreview();
        }
        
        function insertHeading(level) {
            const textarea = document.getElementById('note-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const prefix = '#'.repeat(level) + ' ';
            const textToInsert = selectedText || 'Heading';
            
            // Check if we're at the start of a line
            const beforeCursor = textarea.value.substring(0, start);
            const lastNewline = beforeCursor.lastIndexOf('\n');
            const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
            const isStartOfLine = start === lineStart;
            
            if (isStartOfLine) {
                textarea.value = textarea.value.substring(0, start) + prefix + textToInsert + textarea.value.substring(end);
                textarea.selectionStart = start + prefix.length;
                textarea.selectionEnd = start + prefix.length + textToInsert.length;
            } else {
                textarea.value = textarea.value.substring(0, start) + '\n' + prefix + textToInsert + textarea.value.substring(end);
                textarea.selectionStart = start + 1 + prefix.length;
                textarea.selectionEnd = start + 1 + prefix.length + textToInsert.length;
            }
            
            textarea.focus();
            updatePreview();
        }
        
        function insertList(prefix) {
            const textarea = document.getElementById('note-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            // Check if we're at the start of a line
            const beforeCursor = textarea.value.substring(0, start);
            const lastNewline = beforeCursor.lastIndexOf('\n');
            const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
            const isStartOfLine = start === lineStart;
            
            const textToInsert = selectedText || 'List item';
            
            if (isStartOfLine) {
                textarea.value = textarea.value.substring(0, start) + prefix + textToInsert + textarea.value.substring(end);
                textarea.selectionStart = start + prefix.length;
                textarea.selectionEnd = start + prefix.length + textToInsert.length;
            } else {
                textarea.value = textarea.value.substring(0, start) + '\n' + prefix + textToInsert + textarea.value.substring(end);
                textarea.selectionStart = start + 1 + prefix.length;
                textarea.selectionEnd = start + 1 + prefix.length + textToInsert.length;
            }
            
            textarea.focus();
            updatePreview();
        }
        
        function insertCodeBlock() {
            const textarea = document.getElementById('note-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const textToInsert = selectedText || 'code here';
            
            const codeBlock = '```\n' + textToInsert + '\n```';
            
            textarea.value = textarea.value.substring(0, start) + codeBlock + textarea.value.substring(end);
            textarea.selectionStart = start + 4;
            textarea.selectionEnd = start + 4 + textToInsert.length;
            textarea.focus();
            updatePreview();
        }
        
        function insertInlineCode() {
            insertMarkdown('`', '`', 'code');
        }
        
        function insertChecklist() {
            insertList('- [ ] ');
        }

        // Word Import logic
        function triggerWordImport() {
            document.getElementById('word-import-input').click();
        }

        async function handleWordImport(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async function(e) {
                const arrayBuffer = e.target.result;
                try {
                    // Convert docx to HTML
                    const result = await mammoth.convertToHtml({arrayBuffer: arrayBuffer});
                    const html = result.value; // The generated HTML
                    
                    // Convert HTML to Markdown
                    const turndownService = new TurndownService({
                        headingStyle: 'atx',
                        codeBlockStyle: 'fenced'
                    });
                    const markdown = turndownService.turndown(html);
                    
                    // Append to editor
                    const textarea = document.getElementById('note-content');
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const currentValue = textarea.value;
                    
                    const newValue = currentValue.substring(0, start) + 
                                     (currentValue && start > 0 ? '\n\n' : '') + 
                                     markdown + 
                                     currentValue.substring(end);
                    
                    textarea.value = newValue;
                    updatePreview();
                    
                    // Reset input
                    event.target.value = '';
                } catch (err) {
                    console.error('Word Import Error:', err);
                    alert('Error importing Word document: ' + err.message);
                }
            };
            reader.readAsArrayBuffer(file);
        }
        
        function insertLink() {
            const textarea = document.getElementById('note-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            // If selected text looks like a URL, use it as href
            const isUrl = selectedText.startsWith('http://') || selectedText.startsWith('https://');
            
            if (isUrl) {
                insertMarkdown('[link text](', ')', selectedText);
            } else {
                insertMarkdown('[', '](https://)', selectedText || 'link text');
            }
        }
        
        function insertQuote() {
            insertMarkdown('> ', '', 'quote');
        }

        let allNotesForLinking = [];

        async function openNoteLinkSelector() {
            const res = await fetch('/api/notes');
            allNotesForLinking = await res.json();
            
            document.getElementById('note-link-modal').style.display = 'flex';
            document.getElementById('note-link-search').value = '';
            document.getElementById('note-link-search').focus();
            renderNoteLinkList(allNotesForLinking);
        }

        function closeNoteLinkModal() {
            document.getElementById('note-link-modal').style.display = 'none';
        }

        function filterNoteLinks() {
            const query = document.getElementById('note-link-search').value.toLowerCase();
            const filtered = allNotesForLinking.filter(note => 
                note.name.toLowerCase().includes(query)
            );
            renderNoteLinkList(filtered);
        }

        function renderNoteLinkList(notesList) {
            const list = document.getElementById('note-link-list');
            list.innerHTML = notesList.map(note => `
                <div style="padding: 10px; border-radius: 4px; cursor: pointer; transition: background 0.1s;" 
                     onmouseover="this.style.background='#f0f0ff'" 
                     onmouseout="this.style.background='white'"
                     onclick="insertNoteLink(${note.id}, '${note.name.replace(/'/g, "\\'")}')">
                    <div style="font-weight: 500; font-size: 13px;">📄 ${note.name}</div>
                    <div style="font-size: 11px; color: #888;">ID: ${note.id}</div>
                </div>
            `).join('');
            
            if (notesList.length === 0) {
                list.innerHTML = '<div style="padding: 20px; text-align: center; color: #888; font-size: 13px;">No notes found</div>';
            }
        }

        function insertNoteLink(noteId, noteName) {
            const textarea = document.getElementById('note-content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            const linkName = selectedText || noteName;
            const link = `[${linkName}](note:${noteId})`;
            
            textarea.value = textarea.value.substring(0, start) + link + textarea.value.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + link.length;
            
            updatePreview();
            closeNoteLinkModal();
        }

        // Global link click handler for internal note links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('.note-content-view a');
            if (link) {
                const href = link.getAttribute('href');
                if (!href) return;

                if (href.startsWith('note:')) {
                    e.preventDefault();
                    const noteId = href.split(':')[1];
                    openNote(noteId);
                }
                // External links (http/https) are handled natively by the browser 
                // thanks to target="_blank" added by the marked renderer
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            const textarea = document.getElementById('note-content');
            if (!textarea || document.activeElement !== textarea) return;
            
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                insertMarkdown('**', '**', 'bold text');
            } else if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
                e.preventDefault();
                insertMarkdown('*', '*', 'italic text');
            }
        });

        async function saveNote(noteId) {
            const content = document.getElementById('note-content').value;
            const status = document.getElementById('note-status').value;

            await fetch(`/api/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    content, 
                    status: status
                })
            });

            // Reload note to get updated version info
            const res = await fetch(`/api/notes/${noteId}`);
            const note = await res.json();

            // Update local state in openedNotes
            const noteObj = openedNotes.find(n => n.id == noteId);
            if (noteObj) {
                noteObj.data = note;
                noteObj.mode = 'view';
            }

            // Sync card data if present
            const card = cards.find(c => c.note && c.note.id === noteId);
            if (card) {
                card.note.content = content;
                card.note.status = status;
                renderCards();
            }

            // Update UI if this note is still active
            if (activeNoteId == noteId) {
                renderActiveNoteContent();
                renderNoteTabs(); // Update tab name if needed
            }
            
            // Reload folders to update sidebar
            await loadFolders();
        }

        function closeModal() {
            modal.classList.remove('active');
        }

        // Open Folder Modal
        async function openFolder(folderId) {
            const res = await fetch(`/api/folders/${folderId}`);
            const folder = await res.json();

            modalContent.innerHTML = `
                <div class="modal-header">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            📁 ${folder.name}
                        </h3>
                        <div style="font-size: 12px; color: #666;">
                            <span>Notes: <b>${folder.notes ? folder.notes.length : 0}</b></span>
                        </div>
                    </div>
                    <div class="modal-header-actions">
                        <button onclick="editFolder(${folder.id})" class="btn btn-primary" id="edit-folder-btn">Edit</button>
                        <button onclick="closeModal()" class="btn btn-secondary">Close</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="note-content-view" id="folder-view">${folder.description || ''}</div>
                    <div id="folder-edit-container" style="display: none; height: 100%;">
                        <div class="split-editor" style="height: 400px; min-height: 400px;">
                            <div class="editor-side">
                                <textarea id="folder-description" placeholder="Enter folder description..." oninput="updateFolderPreview()">${folder.description || ''}</textarea>
                            </div>
                            <div class="preview-side note-content-view" id="folder-preview-view"></div>
                        </div>
                    </div>
                    
                    ${folder.notes && folder.notes.length > 0 ? `
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
                        <h4 style="font-size: 14px; margin-bottom: 12px; color: #555;">Notes in this folder</h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            ${folder.notes.map(note => `
                                <div style="padding: 10px; background: #f8f8f8; border-radius: 4px; cursor: pointer; transition: background 0.15s;" 
                                     onmouseover="this.style.background='#e8e8ff'" 
                                     onmouseout="this.style.background='#f8f8f8'"
                                     onclick="closeModal(); setTimeout(() => openNote(${note.id}), 100)">
                                    <div style="font-weight: 500; font-size: 13px; color: #333;">📄 ${note.name}</div>
                                    <div style="font-size: 11px; color: #666; margin-top: 2px;">${note.content ? note.content.substring(0, 80) + '...' : 'Empty note'}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;

            modal.classList.add('active');
            modal.dataset.folderId = folderId;
        }
        
        function selectStatus(status) {
            // Set value for whichever field exists (note or folder)
            const folderStatus = document.getElementById('folder-status');
            const noteStatus = document.getElementById('note-status');
            
            if (folderStatus) folderStatus.value = status;
            if (noteStatus) noteStatus.value = status;
            
            // Update button borders
            document.querySelectorAll('.status-color-btn').forEach(btn => {
                if (btn.dataset.status === status) {
                    btn.style.borderColor = '#333';
                    btn.style.borderWidth = '3px';
                } else {
                    btn.style.borderColor = '#ddd';
                    btn.style.borderWidth = '2px';
                }
            });
        }

        function editFolder(folderId) {
            const viewEl = document.getElementById('folder-view');
            const editContainer = document.getElementById('folder-edit-container');
            const editBtn = document.getElementById('edit-folder-btn');
            
            viewEl.style.display = 'none';
            editContainer.style.display = 'block';
            document.getElementById('folder-description').focus();
            
            // Initialize preview
            updateFolderPreview();
            
            editBtn.textContent = 'Save';
            editBtn.onclick = () => saveFolder(folderId);
        }

        function updateFolderPreview() {
            const content = document.getElementById('folder-description').value;
            const previewView = document.getElementById('folder-preview-view');
            const renderedContent = content ? marked.parse(content) : '';
            if (previewView) previewView.innerHTML = renderedContent;
        }

        async function saveFolder(folderId) {
            const description = document.getElementById('folder-description').value;

            await fetch(`/api/folders/${folderId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    name: document.querySelector('.modal-header h3').textContent.replace('📁 ', '').trim().split('\n')[0],
                    description: description
                })
            });

            // Reload folder
            const res = await fetch(`/api/folders/${folderId}`);
            const folder = await res.json();

            // Update view
            const viewEl = document.getElementById('folder-view');
            const editContainer = document.getElementById('folder-edit-container');
            const editBtn = document.getElementById('edit-folder-btn');
            
            viewEl.innerHTML = description || '';
            viewEl.style.display = 'block';
            editContainer.style.display = 'none';
            
            // Update notes count
            const headerInfo = document.querySelector('.modal-header > div > div[style*="font-size: 12px"]');
            if (headerInfo) {
                headerInfo.innerHTML = `<span>Notes: <b>${folder.notes ? folder.notes.length : 0}</b></span>`;
            }
            
            editBtn.textContent = 'Edit';
            editBtn.onclick = () => editFolder(folderId);
            
            // Update folder card if exists
            const card = cards.find(c => c.folder && c.folder.id === folderId);
            if (card) {
                card.folder.description = description;
                renderCards();
            }
            
            // Reload folders tree
            await loadFolders();
        }

        
        // Move To Modal Functions
        let moveItemType = null;
        let moveItemId = null;
        
        function openMoveModal(type, id) {
            moveItemType = type;
            moveItemId = id;
            
            const moveModal = document.getElementById('move-modal');
            const moveFolderList = document.getElementById('move-folder-list');
            
            // Render folder list
            moveFolderList.innerHTML = renderMoveFolders(folders, 0, id, type);
            
            moveModal.style.display = 'flex';
        }
        
        function renderMoveFolders(folderList, level, currentId, itemType) {
            return folderList.map(folder => {
                // Don't show current folder if moving a folder (can't move into itself)
                if (itemType === 'folder' && folder.id == currentId) {
                    return '';
                }
                
                const indent = level * 20;
                let html = `
                    <div style="padding: 10px 12px; padding-left: ${12 + indent}px; cursor: pointer; border-radius: 4px; margin-bottom: 4px; font-size: 14px; transition: background 0.15s;" 
                         onmouseover="this.style.background='#f0f0f0'" 
                         onmouseout="this.style.background='transparent'"
                         onclick="moveToFolder(${folder.id})">
                        📁 ${folder.name}
                    </div>
                `;
                
                if (folder.children && folder.children.length > 0) {
                    html += renderMoveFolders(folder.children, level + 1, currentId, itemType);
                }
                
                return html;
            }).join('');
        }
        
        function closeMoveModal() {
            document.getElementById('move-modal').style.display = 'none';
            moveItemType = null;
            moveItemId = null;
        }
        
        async function moveToFolder(targetFolderId) {
            if (!moveItemType || !moveItemId) return;
            
            try {
                if (moveItemType === 'note') {
                    // Get note info to know old folder
                    const noteRes = await fetch(`/api/notes/${moveItemId}`);
                    const noteData = await noteRes.json();
                    const oldFolderId = noteData.folder_id;
                    
                    // Move note
                    const res = await fetch(`/api/notes/${moveItemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ folder_id: targetFolderId })
                    });
                    
                    if (res.ok) {
                        console.log('Note moved successfully');
                        await loadFolders();
                        
                        // Update old folder card
                        if (oldFolderId) {
                            await updateFolderCards(oldFolderId);
                        }
                        
                        // Update new folder card
                        if (targetFolderId) {
                            await updateFolderCards(targetFolderId);
                        }
                    }
                } else if (moveItemType === 'folder') {
                    // Move folder
                    const res = await fetch(`/api/folders/${moveItemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ parent_id: targetFolderId })
                    });
                    
                    if (res.ok) {
                        console.log('Folder moved successfully');
                        await loadFolders();
                    }
                }
                
                closeMoveModal();
            } catch (e) {
                console.error('Error moving item:', e);
                alert('Failed to move item');
            }
        }

        // Close modal on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        async function deleteCard(cardId) {
            if (!confirm('Delete this card from canvas?')) return;

            await fetch(`/api/cards/${cardId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            cards = cards.filter(c => c.id !== cardId);
            renderCards();
        }

        function toggleSearch() {
            alert('Search feature coming soon!');
        }

        // Expand/Collapse Logic
        let isAllExpanded = false;

        function toggleExpandAll() {
            if (isAllExpanded) {
                expandedFolders.clear();
            } else {
                // Add all folder IDs to expanded set
                // We need to flatten the folder structure to get all IDs
                const addIds = (items) => {
                    items.forEach(item => {
                        expandedFolders.add(item.id);
                        if (item.children) addIds(item.children);
                    });
                };
                addIds(folders);
            }
            isAllExpanded = !isAllExpanded;
            renderFolderTree();
        }

        function toggleSortMenu() {
            alert('Sort menu coming soon');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const extBtn = document.getElementById('sidebar-toggle-external');
            
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                extBtn.style.display = 'none';
            } else {
                sidebar.classList.add('collapsed');
                extBtn.style.display = 'flex';
            }
        }

        function toggleUserMenu(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('user-dropdown');
            const notifDropdown = document.getElementById('notification-dropdown');
            
            // Close others
            if (notifDropdown) notifDropdown.style.display = 'none';

            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }

        function toggleNotifications(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notification-dropdown');
            const userDropdown = document.getElementById('user-dropdown');
            
            // Close others
            if (userDropdown) userDropdown.style.display = 'none';

            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }

        // Close dropdowns on click outside
        document.addEventListener('click', (e) => {
            const userDropdown = document.getElementById('user-dropdown');
            const notifDropdown = document.getElementById('notification-dropdown');
            
            if (userDropdown && userDropdown.style.display === 'block') {
                userDropdown.style.display = 'none';
            }
            if (notifDropdown && notifDropdown.style.display === 'block') {
                notifDropdown.style.display = 'none';
            }
        });

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        function toggleHistory() {
            const historyContainer = document.getElementById('history-container');
            const viewEl = document.getElementById('note-view');
            const editContainer = document.getElementById('edit-container');
            const notePanel = document.getElementById('note-panel');

            if (historyContainer.style.display === 'none') {
                // Show History
                historyContainer.style.display = 'block';
                viewEl.style.display = 'none';
                editContainer.style.display = 'none';
                
                const versions = JSON.parse(notePanel.dataset.versions || '[]');
                const historyList = document.getElementById('history-list');
                
                if (versions.length === 0) {
                    historyList.innerHTML = '<div style="color: #888; font-style: italic;">No history available</div>';
                } else {
                    historyList.innerHTML = versions.sort((a,b) => b.version - a.version).map(v => `
                        <div class="history-item" style="padding: 12px; border: 1px solid #e5e5e5; border-radius: 6px; margin-bottom: 8px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-weight: 600; font-size: 13px;">v${v.version} - ${v.user ? (v.user.username || v.user.name) : 'Unknown'}</span>
                                <span style="font-size: 11px; color: #999;">${new Date(v.created_at).toLocaleString()}</span>
                            </div>
                            <div style="font-size: 12px; color: #666; margin-bottom: 8px;">${v.change_note || 'No description'}</div>
                            <div style="display: flex; gap: 8px;">
                                <button onclick="viewVersionContent(${v.id})" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">View Content</button>
                                <button onclick="restoreVersion(${v.id})" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px; background: #10b981;">Restore</button>
                            </div>
                        </div>
                    `).join('');
                }
            } else {
                // Hide History, Show View
                historyContainer.style.display = 'none';
                viewEl.style.display = 'block';
            }
        }

        function viewVersionContent(versionId) {
            const notePanel = document.getElementById('note-panel');
            const versions = JSON.parse(notePanel.dataset.versions || '[]');
            const version = versions.find(v => v.id === versionId);
            if (version) {
                const viewEl = document.getElementById('note-view');
                const historyContainer = document.getElementById('history-container');
                
                // Switch to view mode and show version content
                historyContainer.style.display = 'none';
                viewEl.style.display = 'block';
                viewEl.innerHTML = `
                    <div style="background: #fff8e1; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #ffe082; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; color: #795548;">Viewing <b>Version ${version.version}</b> (Historical Mode)</span>
                        <button onclick="toggleHistory()" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">Back to History</button>
                    </div>
                    ${version.content ? marked.parse(version.content) : '<i style="color: #999;">Empty version</i>'}
                `;
            }
        }

        async function restoreVersion(versionId) {
            const notePanel = document.getElementById('note-panel');
            const versions = JSON.parse(notePanel.dataset.versions || '[]');
            const version = versions.find(v => v.id === versionId);
            const noteId = notePanel.dataset.noteId;
            
            if (!version || !confirm(`Restore note to Version ${version.version}? Current unsaved changes will be lost.`)) return;

            await fetch(`/api/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    content: version.content,
                    change_note: `Restored to v${version.version}`
                })
            });

            // Reload note to see changes
            const res = await fetch(`/api/notes/${noteId}`);
            const updatedNote = await res.json();
            
            // Sync with openedNotes
            const noteObj = openedNotes.find(n => n.id == noteId);
            if (noteObj) {
                noteObj.data = updatedNote;
            }
            
            renderActiveNoteContent();
            renderNoteTabs();
        }

        // Initialize
        async function init() {
            loadUsers();
            await loadFolders();
            renderCards();
            
            // Setup drop zone AFTER folders are loaded
            const dropZone = document.getElementById('folder-tree-drop-zone');
            console.log('Setting up drop zone:', dropZone);
            
            if (!dropZone) {
                console.error('Drop zone not found!');
                return;
            }
            
            dropZone.addEventListener('dragover', (ev) => {
                console.log('dragover on drop zone, draggedNoteId:', draggedNoteId);
                
                if (!draggedNoteId) return;
                
                ev.preventDefault();
                dropZone.classList.add('drag-over');
            });
            
            dropZone.addEventListener('dragleave', (ev) => {
                console.log('dragleave on drop zone');
                dropZone.classList.remove('drag-over');
            });
            
            dropZone.addEventListener('drop', async (ev) => {
                console.log('drop on drop zone, draggedNoteId:', draggedNoteId);
                
                ev.preventDefault();
                ev.stopPropagation();
                dropZone.classList.remove('drag-over');
                
                if (!draggedNoteId) {
                    console.warn('No draggedNoteId');
                    return;
                }
                
                console.log(`Moving note ${draggedNoteId} to root`);
                
                try {
                    // Get note info BEFORE moving to know the old folder
                    const noteInfoRes = await fetch(`/api/notes/${draggedNoteId}`);
                    const noteInfo = await noteInfoRes.json();
                    const oldFolderId = noteInfo.folder_id;
                    
                    console.log('Note info:', noteInfo, 'oldFolderId:', oldFolderId);
                    
                    // Only move if note is currently in a folder
                    if (!oldFolderId) {
                        console.log('Note is already in root');
                        return;
                    }
                    
                    // Move note to root (folder_id = null)
                    const res = await fetch(`/api/notes/${draggedNoteId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ folder_id: null })
                    });

                    if (res.ok) {
                        console.log('Note moved to root successfully');
                        await loadFolders();
                        
                        // Update OLD folder cards (remove note)
                        await updateFolderCards(oldFolderId);
                    } else {
                        console.error('Failed to move note to root');
                    }
                } catch (e) {
                    console.error('Error moving note to root:', e);
                }
            });
        }
        
        // Resource Library Functions
        let allResources = [];
        let isInsertMode = false; // Track if we're in insert mode
        
        function openResourceLibraryForInsert() {
            isInsertMode = true;
            openResourceLibrary();
        }
        
        async function openResourceLibrary() {
            isInsertMode = false; // Reset unless called from openResourceLibraryForInsert
            const modal = document.getElementById('resource-library-modal');
            modal.style.display = 'flex';
            await loadResources();
        }
        
        function closeResourceLibrary() {
            document.getElementById('resource-library-modal').style.display = 'none';
            isInsertMode = false; // Reset insert mode
        }
        
        async function loadResources() {
            try {
                const res = await fetch('/api/resources');
                allResources = await res.json();
                renderResources(allResources);
            } catch (e) {
                console.error('Error loading resources:', e);
                alert('Failed to load resources');
            }
        }
        
        function filterResources() {
            const search = document.getElementById('resource-search').value.toLowerCase();
            const typeFilter = document.getElementById('resource-type-filter').value;
            
            let filtered = allResources.filter(resource => {
                const matchesSearch = !search || 
                    resource.name.toLowerCase().includes(search) ||
                    (resource.description && resource.description.toLowerCase().includes(search));
                const matchesType = !typeFilter || resource.type === typeFilter;
                
                return matchesSearch && matchesType;
            });
            
            renderResources(filtered);
        }
        
        function renderResources(resources) {
            const container = document.getElementById('resource-library-content');
            
            if (resources.length === 0) {
                container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">No resources found</div>';
                return;
            }
            
            container.innerHTML = resources.map(resource => {
                const icon = getResourceIcon(resource.type);
                const isImage = resource.type === 'image';
                const thumbnail = isImage ? resource.url : '';
                
                return `
                    <div class="resource-card" style="border: 1px solid #e5e5e5; border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.2s; background: white;" onclick="selectResource(${resource.id}, event)">
                        <div style="aspect-ratio: 1; background: ${isImage ? 'transparent' : '#f5f5f5'}; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            ${isImage ? `<img src="${thumbnail}" style="width: 100%; height: 100%; object-fit: cover;">` : `<div style="font-size: 48px;">${icon}</div>`}
                        </div>
                        <div style="padding: 12px;">
                            <div style="font-size: 13px; font-weight: 500; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${resource.name}">${resource.name}</div>
                            <div style="font-size: 11px; color: #999;">${resource.formatted_size}</div>
                            <div style="font-size: 11px; color: #666; margin-top: 4px;">
                                <span style="background: #e8f4fd; color: #0066cc; padding: 2px 6px; border-radius: 3px;">${resource.type}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function getResourceIcon(type) {
            const icons = {
                'image': '🖼️',
                'document': '📄',
                'video': '🎥',
                'audio': '🎵',
                'other': '📎'
            };
            return icons[type] || icons.other;
        }
        
        async function selectResource(resourceId, event) {
            const resource = allResources.find(r => r.id === resourceId);
            if (!resource) return;
            
            // If in insert mode, directly insert without prompting
            if (isInsertMode) {
                insertResourceIntoNote(resource);
                return;
            }
            
            // Show context menu instead of prompt
            showResourceContextMenu(event, resource);
        }
        
        let currentResource = null;
        
        function showResourceContextMenu(event, resource) {
            currentResource = resource;
            const menu = document.getElementById('resource-context-menu');
            
            // Update insert text based on resource type
            const insertText = document.getElementById('resource-insert-text');
            if (resource.type === 'image') {
                insertText.textContent = 'Insert as Image';
            } else {
                insertText.textContent = 'Insert as Link';
            }
            
            // Position menu at click location
            menu.style.display = 'block';
            menu.style.left = event.pageX + 'px';
            menu.style.top = event.pageY + 'px';
            
            // Close menu when clicking outside
            setTimeout(() => {
                document.addEventListener('click', closeResourceContextMenu);
            }, 0);
        }
        
        function closeResourceContextMenu() {
            document.getElementById('resource-context-menu').style.display = 'none';
            document.removeEventListener('click', closeResourceContextMenu);
        }
        
        async function resourceContextAction(action) {
            if (!currentResource) return;
            
            const resource = currentResource;
            closeResourceContextMenu();
            
            switch (action) {
                case 'insert':
                    if (resource.type === 'image') {
                        insertResourceAsImage(resource);
                    } else {
                        insertResourceAsLink(resource);
                    }
                    break;
                case 'copy':
                    copyResourceLink(resource);
                    currentResource = null; // Reset after copy
                    break;
                case 'download':
                    downloadResource(resource);
                    currentResource = null; // Reset after download
                    break;
                case 'details':
                    viewResourceDetails(resource);
                    currentResource = null; // Reset after view
                    break;
                case 'delete':
                    await deleteResource(resource);
                    currentResource = null; // Reset after delete
                    break;
            }
            
            // Don't reset currentResource here for insert action
            // It will be reset after image size is selected
        }
        
        function insertResourceIntoNote(resource) {
            const textarea = document.getElementById('note-content');
            if (!textarea) {
                alert('Please open a note first');
                return;
            }
            
            // For images, show size selection
            if (resource.type === 'image') {
                currentResource = resource;
                document.getElementById('image-size-modal').style.display = 'flex';
                return;
            }
            
            // For non-images, insert directly as link
            const markdown = `\n[${resource.name}](${resource.url})\n`;
            
            // Insert at cursor position
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + markdown + text.substring(end);
            
            // Move cursor after inserted text
            textarea.selectionStart = textarea.selectionEnd = start + markdown.length;
            textarea.focus();
            
            if (typeof updatePreview === 'function') updatePreview();
            
            closeResourceLibrary();
            isInsertMode = false;
        }
        
        function insertResourceAsImage(resource) {
            const textarea = document.getElementById('note-content');
            if (!textarea) {
                alert('Please open a note first');
                return;
            }
            
            // Store resource for size selection
            currentResource = resource;
            
            // Show size selection modal
            document.getElementById('image-size-modal').style.display = 'flex';
        }
        
        function closeImageSizeModal() {
            document.getElementById('image-size-modal').style.display = 'none';
            currentResource = null;
        }
        
        function insertImageWithSize(size) {
            console.log('insertImageWithSize called with size:', size);
            console.log('currentResource:', currentResource);
            
            if (!currentResource) {
                console.error('No currentResource');
                alert('Error: No resource selected');
                return;
            }
            
            let textarea = document.getElementById('note-content');
            
            // If textarea not found, check if we need to open edit mode
            if (!textarea) {
                console.log('Textarea not found, checking if note panel exists');
                const notePanel = document.getElementById('note-panel');
                if (notePanel && notePanel.style.display !== 'none') {
                    // Note is open but not in edit mode, try to get note ID and open edit
                    const noteId = notePanel.dataset.noteId;
                    if (noteId) {
                        console.log('Opening edit mode for note:', noteId);
                        editNote(noteId);
                        // Wait a bit for edit mode to open
                        setTimeout(() => {
                            textarea = document.getElementById('note-content');
                            if (textarea) {
                                insertImageMarkdown(textarea, size, currentResource);
                            } else {
                                alert('Could not open edit mode. Please click Edit button first.');
                                closeImageSizeModal();
                            }
                        }, 100);
                        return;
                    }
                }
                
                console.error('No textarea found and cannot open edit mode');
                alert('Please open a note and click Edit first');
                closeImageSizeModal();
                return;
            }
            
            insertImageMarkdown(textarea, size, currentResource);
        }
        
        function insertImageMarkdown(textarea, size, resource) {
            let markdown;
            
            switch (size) {
                case 'original':
                    markdown = `\n![${resource.name}](${resource.url})\n`;
                    break;
                case 'large':
                    markdown = `\n<img src="${resource.url}" alt="${resource.name}" width="800">\n`;
                    break;
                case 'medium':
                    markdown = `\n<img src="${resource.url}" alt="${resource.name}" width="500">\n`;
                    break;
                case 'small':
                    markdown = `\n<img src="${resource.url}" alt="${resource.name}" width="300">\n`;
                    break;
                case 'thumbnail':
                    markdown = `\n<img src="${resource.url}" alt="${resource.name}" width="150">\n`;
                    break;
                case 'custom':
                    const customSize = prompt('Enter width in pixels:', '400');
                    if (!customSize) {
                        closeImageSizeModal();
                        return;
                    }
                    markdown = `\n<img src="${resource.url}" alt="${resource.name}" width="${customSize}">\n`;
                    break;
            }
            
            console.log('Inserting markdown:', markdown);
            
            // Insert at cursor position
            const start = textarea.selectionStart || 0;
            const end = textarea.selectionEnd || 0;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + markdown + text.substring(end);
            
            // Move cursor after inserted text
            textarea.selectionStart = textarea.selectionEnd = start + markdown.length;
            textarea.focus();
            
            if (typeof updatePreview === 'function') updatePreview();
            
            closeImageSizeModal();
            closeResourceLibrary();
            
            console.log('Image inserted successfully');
        }
        
        function insertResourceAsLink(resource) {
            const textarea = document.getElementById('note-content');
            if (!textarea) {
                alert('Please open a note first');
                currentResource = null;
                return;
            }
            
            const markdown = `\n[${resource.name}](${resource.url})\n`;
            textarea.value += markdown;
            if (typeof updatePreview === 'function') updatePreview();
            
            closeResourceLibrary();
            currentResource = null; // Reset after insert
            alert('Link inserted into note!');
        }
        
        function copyResourceLink(resource) {
            navigator.clipboard.writeText(resource.url).then(() => {
                alert('Link copied to clipboard!');
            });
        }
        
        function downloadResource(resource) {
            window.open(`/api/resources/${resource.id}/download`, '_blank');
        }
        
        function viewResourceDetails(resource) {
            alert(`Name: ${resource.name}\nType: ${resource.type}\nSize: ${resource.formatted_size}\nUploaded: ${new Date(resource.created_at).toLocaleString()}\nDownloads: ${resource.download_count}\n\nURL: ${resource.url}`);
        }
        
        async function deleteResource(resource) {
            if (!confirm(`Delete "${resource.name}"?`)) return;
            
            try {
                await fetch(`/api/resources/${resource.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                await loadResources();
                alert('Resource deleted!');
            } catch (e) {
                console.error('Error deleting resource:', e);
                alert('Failed to delete resource');
            }
        }
        
        async function uploadResource(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;
            
            // Show uploading indicator
            const uploadBtn = document.querySelector('#resource-library-modal button');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor; animation: spin 1s linear infinite;"><path d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z"/></svg> Uploading...';
            uploadBtn.disabled = true;
            
            for (let file of files) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('name', file.name);
                // Category will be auto-set by backend based on file type
                
                try {
                    await fetch('/api/resources', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                } catch (e) {
                    console.error('Error uploading:', e);
                    alert(`Failed to upload ${file.name}`);
                }
            }
            
            await loadResources();
            event.target.value = ''; // Reset input
            uploadBtn.innerHTML = originalText;
            uploadBtn.disabled = false;
            alert(`${files.length} file(s) uploaded successfully!`);
        }
        
        init();
    </script>
</body>
</html>
