<header class="header">
    <div class="logo">Code Editor</div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            File
            <div class="submenu">
                <div class="submenu-item">New</div>
                <div class="submenu-item">Save</div>
                <div class="submenu-item">Save As</div>
                <div class="submenu-item">Download</div>
                <div class="submenu-item">Rename</div>
                <div class="submenu-item">Delete</div>
            </div>
        </li>
        <li class="nav-item">
            Edit
            <div class="submenu">
                <div class="submenu-item">Undo</div>
                <div class="submenu-item">Redo</div>
                <div class="submenu-item">Cut</div>
                <div class="submenu-item">Copy</div>
                <div class="submenu-item">Paste</div>
                <div class="submenu-item">Find & Replace</div>
            </div>
        </li>
        <li class="nav-item">
            View
            <div class="submenu">
                <div class="submenu-item">Word Wrap</div>
                <div class="submenu-item" data-action="toggle-layout">Toggle Layout</div>
                <div class="submenu-item">Zoom In</div>
                <div class="submenu-item">Zoom Out</div>
            </div>
        </li>
        <li class="nav-item">
            Run
            <div class="submenu">
                <div class="submenu-item">Run Code</div>
                <div class="submenu-item">Toggle Auto-Run</div>
            </div>
        </li>
    </ul>
    
    <div class="header-actions">
        <button id="run-button" class="run-btn">Run</button>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <div class="user-menu">
            <span class="user-name"><?php  echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>