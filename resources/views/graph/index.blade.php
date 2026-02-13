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
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            border-color: #b5b5b5;
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
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            gap: 6px;
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
            line-height: 1.8;
            color: #2e2e2e;
            white-space: pre-wrap;
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
            padding: 0;
            border: none;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            line-height: 1.8;
            outline: none;
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

            <!-- Canvas Wrapper -->
            <div style="position: relative; flex: 1; overflow: hidden; background: #f8f8f8;">
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
    
    <!-- Status Change Modal -->
    <div id="status-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 24px; border-radius: 8px; max-width: 300px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; color: #333;">Change Status</h3>
            <div style="display: flex; gap: 12px; justify-content: center; margin-bottom: 20px;">
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

        const canvas = document.getElementById('canvas');
        const modal = document.getElementById('modal');
        const modalContent = document.getElementById('modal-content');
        const contextMenu = document.getElementById('context-menu');

        let rootNotes = [];

        // Load folders and notes
        async function loadFolders() {
            const [foldersRes, rootNotesRes] = await Promise.all([
                fetch('/api/folders/tree'),
                fetch('/api/notes?root=true')
            ]);
            folders = await foldersRes.json();
            rootNotes = await rootNotesRes.json();
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
                const noteStatus = note.status && statusIcons[note.status] ? `<span style="font-size: 10px; margin-left: auto;">${statusIcons[note.status]}</span>` : '';
                return `
                    <div class="tree-item note-tree-item" draggable="true" data-type="note" data-note-id="${note.id}">
                        <span class="tree-item-icon" style="visibility: hidden;"></span>
                        <span class="tree-item-name" contenteditable="false" onblur="saveItemName(${note.id}, 'note', this)" onkeydown="handleKeyDown(event, this)">${note.name}</span>
                        ${noteStatus}
                    </div>
                `;
            }).join('');

            tree.innerHTML = foldersHtml + rootNotesHtml;
            attachNoteClickHandlers();
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
                const statusIcon = item.status && statusIcons[item.status] ? `<span style="font-size: 10px; margin-left: 4px;">${statusIcons[item.status]}</span>` : '';

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
                            ${item.notes && item.notes.length === 0 && !item.children ? statusIcon : ''} 
                        </div>
                        <div class="tree-children ${isExpanded ? 'expanded' : ''}" id="folder-${item.id}">
                            ${item.notes ? item.notes.map(note => {
                                const noteStatus = note.status && statusIcons[note.status] ? `<span style="font-size: 10px; margin-left: auto;">${statusIcons[note.status]}</span>` : '';
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

            const endpoint = type === 'folder' ? `/api/folders/${id}` : `/api/notes/${id}`;
            
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
            }
            
            loadFolders();
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
                        body: JSON.stringify({ note_id: draggedNoteId, position_x: x, position_y: y })
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
                        body: JSON.stringify({ folder_id: draggedFolderId, position_x: x, position_y: y })
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
                    card.linked_notes.forEach(linkedNote => {
                        // Use loose equality (==) to handle string/number ID mismatch
                        // Find target card by note_id or folder_id
                        const targetCard = cards.find(c => {
                            // Check if target is a note card
                            if (c.note && c.note.id == linkedNote.id) return true;
                            // Check if target is a folder card
                            if (c.folder && c.folder.id == linkedNote.id) return true;
                            return false;
                        });
                        
                        if (targetCard) {
                            try {
                                drawLine(svg, card, targetCard);
                            } catch (e) {
                                console.error('Error drawing line:', e, 'card:', card, 'targetCard:', targetCard);
                            }
                        } else {
                            console.warn('Target card not found for linked item:', linkedNote, 'Available cards:', cards.map(c => ({
                                id: c.id,
                                noteId: c.note?.id,
                                folderId: c.folder?.id
                            })));
                        }
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
                cardEl.className = 'card';
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
                    const folderDescription = card.folder.description || 'No description';
                    const descriptionPreview = folderDescription.length > 100 ? folderDescription.substring(0, 100) + '...' : folderDescription;
                    
                    const notesListHtml = showDetails && card.folder.notes && card.folder.notes.length > 0 ? `
                        <div class="card-links">
                            ${card.folder.notes.map(note => `
                                <div class="card-link" onclick="openNote(${note.id})">� ${note.name}</div>
                            `).join('')}
                        </div>
                    ` : (showDetails && (!card.folder.notes || card.folder.notes.length === 0) ? '<div style="color: #8e8e8e; font-size: 12px; margin-top: 8px;">No notes in this folder</div>' : '');

                    cardEl.innerHTML = `
                        <div class="card-header">📁 ${card.folder.name}</div>
                        ${showDetails ? `<div class="card-subheader" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">${descriptionPreview}</div>` : ''}
                        ${notesListHtml}
                        ${showDetails ? `
                        <div class="card-actions">
                            <button onclick="openFolder(${card.folder.id})" class="card-btn">View</button>
                            <button onclick="deleteCard(${card.id})" class="card-btn delete">Delete</button>
                        </div>
                        ` : ''}
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
                        ${showDetails ? `
                        <div class="card-actions">
                            <button onclick="openNote(${card.note.id})" class="card-btn">Edit</button>
                            <button onclick="deleteCard(${card.id})" class="card-btn delete">Delete</button>
                        </div>
                        ` : ''}
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
                        draggedCard = card;
                        dragStartX = e.clientX;
                        dragStartY = e.clientY;
                    }
                });

                canvas.appendChild(cardEl);
            });
            
            renderConnections();



        }

        // Pan canvas
        canvas.addEventListener('mousedown', (e) => {
            if (!draggedCard && e.target === canvas) {
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
            const res = await fetch(`/api/notes/${noteId}`);
            const note = await res.json();
            
            const statusColors = {
                'draft': '#ef4444',
                'improving': '#f59e0b',
                'standardized': '#10b981'
            };
            const currentColor = statusColors[note.status || 'draft'];

            modalContent.innerHTML = `
                <div class="modal-header">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            ${note.name}
                            <span style="width: 12px; height: 12px; border-radius: 50%; background: ${currentColor};" title="Status indicator"></span>
                            <span style="font-size: 11px; padding: 2px 6px; border-radius: 4px; background: #e0e0e0; color: #555;">v${note.current_version || 1}</span>
                        </h3>
                    </div>
                    <div class="modal-header-actions">
                        <button onclick="toggleHistory()" class="btn btn-secondary" title="View History">
                            <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3"/></svg>
                        </button>
                        <button onclick="editNote(${note.id})" class="btn btn-primary" id="edit-btn">Edit</button>
                        <button onclick="closeModal()" class="btn btn-secondary">Close</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="note-content-view" id="note-view">${note.content || ''}</div>
                    <div id="edit-container" style="display: none;">
                        <textarea id="note-content">${note.content || ''}</textarea>
                        
                        <div style="margin-top: 12px;">
                            <label style="display: block; font-size: 12px; color: #666; margin-bottom: 8px;">Status Color</label>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="selectStatus('draft')" class="status-color-btn" data-status="draft" style="width: 32px; height: 32px; border-radius: 6px; border: 2px solid ${note.status === 'draft' ? '#333' : '#ddd'}; background: #ef4444; cursor: pointer; transition: all 0.15s;" title="Red - Issues/Draft"></button>
                                <button type="button" onclick="selectStatus('improving')" class="status-color-btn" data-status="improving" style="width: 32px; height: 32px; border-radius: 6px; border: 2px solid ${note.status === 'improving' ? '#333' : '#ddd'}; background: #f59e0b; cursor: pointer; transition: all 0.15s;" title="Yellow - In Progress"></button>
                                <button type="button" onclick="selectStatus('standardized')" class="status-color-btn" data-status="standardized" style="width: 32px; height: 32px; border-radius: 6px; border: 2px solid ${note.status === 'standardized' ? '#333' : '#ddd'}; background: #10b981; cursor: pointer; transition: all 0.15s;" title="Green - Complete"></button>
                            </div>
                            <input type="hidden" id="note-status" value="${note.status || 'draft'}">
                        </div>

                        <input type="text" id="change-note" placeholder="Describe your changes (optional)..." style="width: 100%; margin-top: 12px; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    
                    <div id="history-container" style="display: none; padding-top: 16px; border-top: 1px solid #e5e5e5; margin-top: 16px;">
                        <h4 style="font-size: 14px; margin-bottom: 12px;">Version History</h4>
                        <div id="history-list"></div>
                    </div>
                </div>
            `;
            
            // Store versions for history view
            modal.dataset.versions = JSON.stringify(note.versions || []);

            modal.classList.add('active');
            modal.dataset.noteId = noteId;
        }

        function editNote(noteId) {
            const viewEl = document.getElementById('note-view');
            const editContainer = document.getElementById('edit-container');
            const editBtn = document.getElementById('edit-btn');
            
            viewEl.style.display = 'none';
            editContainer.style.display = 'block';
            document.getElementById('note-content').focus();
            
            editBtn.textContent = 'Save';
            editBtn.onclick = () => saveNote(noteId);
        }

        async function saveNote(noteId) {
            const content = document.getElementById('note-content').value;
            const changeNote = document.getElementById('change-note').value;
            const status = document.getElementById('note-status').value;

            await fetch(`/api/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    content, 
                    change_note: changeNote,
                    status: status
                })
            });

            // Reload note to get updated version info
            const res = await fetch(`/api/notes/${noteId}`);
            const note = await res.json();

            // Update view
            const viewEl = document.getElementById('note-view');
            const editContainer = document.getElementById('edit-container');
            const editBtn = document.getElementById('edit-btn');
            
            viewEl.textContent = content;
            viewEl.style.display = 'block';
            editContainer.style.display = 'none';
            
            // Update status color indicator in header
            const statusColors = {
                'draft': '#ef4444',
                'improving': '#f59e0b',
                'standardized': '#10b981'
            };
            const statusIndicator = document.querySelector('.modal-header h3 span[title="Status indicator"]');
            if (statusIndicator) {
                statusIndicator.style.background = statusColors[note.status || 'draft'];
            }
            
            // Update version badge
            const versionBadge = document.querySelector('.modal-header h3 span:not([title]):not([style*="background"])');
            if (versionBadge) {
                versionBadge.textContent = `v${note.current_version}`;
            }
            
            editBtn.textContent = 'Edit';
            editBtn.onclick = () => editNote(noteId);
            
            // Update card if exists
            const card = cards.find(c => c.note && c.note.id === noteId);
            if (card) {
                card.note.content = content;
                card.note.status = status;
                renderCards();
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
                    <div class="note-content-view" id="folder-view">${folder.description || '<i style="color: #999;">No description</i>'}</div>
                    <div id="folder-edit-container" style="display: none;">
                        <textarea id="folder-description" placeholder="Enter folder description...">${folder.description || ''}</textarea>
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
            
            editBtn.textContent = 'Save';
            editBtn.onclick = () => saveFolder(folderId);
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
            
            viewEl.innerHTML = description || '<i style="color: #999;">No description</i>';
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

            if (historyContainer.style.display === 'none') {
                // Show History
                historyContainer.style.display = 'block';
                viewEl.style.display = 'none';
                editContainer.style.display = 'none';
                
                const versions = JSON.parse(modal.dataset.versions || '[]');
                const historyList = document.getElementById('history-list');
                
                if (versions.length === 0) {
                    historyList.innerHTML = '<div style="color: #888; font-style: italic;">No history available</div>';
                } else {
                    historyList.innerHTML = versions.sort((a,b) => b.version - a.version).map(v => `
                        <div class="history-item" onclick="viewVersionContent(${v.id})">
                            <div class="history-meta">
                                <span>v${v.version} - ${v.user ? v.user.username : 'Unknown'}</span>
                                <span>${new Date(v.created_at).toLocaleString()}</span>
                            </div>
                            <div class="history-note">${v.change_note || 'No description'}</div>
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
            const versions = JSON.parse(modal.dataset.versions || '[]');
            const version = versions.find(v => v.id === versionId);
            if (version) {
                alert(`Viewing Content of v${version.version}:\n\n` + version.content);
                // Future: Implement Restore button here
            }
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
        
        init();
    </script>
</body>
</html>
