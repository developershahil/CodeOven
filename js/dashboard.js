document.addEventListener('DOMContentLoaded', function() {
    // Initialize CodeMirror editors
    const htmlEditor = CodeMirror(document.getElementById('html-editor'), {
        mode: 'xml',
        theme: 'eclipse',
        lineNumbers: true,
        autoCloseTags: true,
        matchBrackets: true,
        lineWrapping: true,
        extraKeys: {"Ctrl-Space": "autocomplete"}        
    });
    
    const cssEditor = CodeMirror(document.getElementById('css-editor'), {
        mode: 'css',
        theme: 'eclipse',
        lineNumbers: true,
        matchBrackets: true,
        lineWrapping: true,
        extraKeys: {"Ctrl-Space": "autocomplete"}
    });
    
    const jsEditor = CodeMirror(document.getElementById('js-editor'), {
        mode: 'javascript',
        theme: 'eclipse',
        lineNumbers: true,
        matchBrackets: true,
        lineWrapping: true,
        extraKeys: {"Ctrl-Space": "autocomplete"}
    });

    // Track active editor
    let activeEditor = htmlEditor;
    function getActiveEditor() { return activeEditor; }

    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    const editorPanes = document.querySelectorAll('.editor-pane');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            editorPanes.forEach(pane => {
                if (pane.id === `${tabName}-editor`) {
                    pane.classList.remove('hidden');
                } else {
                    pane.classList.add('hidden');
                }
            });
            if (tabName === "html") activeEditor = htmlEditor;
            if (tabName === "css") activeEditor = cssEditor;
            if (tabName === "js") activeEditor = jsEditor;
            console.log("Active editor set to:", tabName);
        });
    });

    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    let isDarkTheme = false;
    themeToggle.addEventListener('click', () => {
        isDarkTheme = !isDarkTheme;
        const theme = isDarkTheme ? "dracula" : "eclipse";
        htmlEditor.setOption('theme', theme);
        cssEditor.setOption('theme', theme);
        jsEditor.setOption('theme', theme);
        themeToggle.innerHTML = isDarkTheme ? '☀️' : '🌙';
    });

    // Layout toggle
    const layoutToggle = document.querySelector('.submenu-item[data-action="toggle-layout"]');
    const editorPreviewContainer = document.querySelector('.editor-preview-container');
    let isHorizontalLayout = true;
    if (layoutToggle) {
        layoutToggle.addEventListener('click', () => {
            isHorizontalLayout = !isHorizontalLayout;
            editorPreviewContainer.classList.toggle('horizontal', isHorizontalLayout);
            editorPreviewContainer.classList.toggle('vertical', !isHorizontalLayout);
        });
    }

    // Run preview
    const runButton = document.getElementById('run-button');
    runButton.addEventListener('click', updatePreview);

    let updateTimeout;
    [htmlEditor, cssEditor, jsEditor].forEach(ed => {
        ed.on('change', () => {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(updatePreview, 1000);
        });
    });

    function updatePreview() {
        const htmlCode = htmlEditor.getValue();
        const cssCode = cssEditor.getValue();
        const jsCode = jsEditor.getValue();
        const previewFrame = document.getElementById('live-preview');
        const previewDocument = previewFrame.contentDocument || previewFrame.contentWindow.document;
        previewDocument.open();
        previewDocument.write(`
            <!DOCTYPE html>
            <html>
            <head><style>${cssCode}</style></head>
            <body>
                ${htmlCode}
                <script>${jsCode}<\/script>
            </body>
            </html>
        `);
        previewDocument.close();
    }

    updatePreview();

    // File explorer
    const fileItems = document.querySelectorAll('.file-item');
    fileItems.forEach(item => {
        item.addEventListener('click', () => {
            fileItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // ================= EDIT / VIEW FUNCTIONS =================
    function handleUndo() { getActiveEditor().undo(); }
    function handleRedo() { getActiveEditor().redo(); }
    function handleCopy() {
        const text = getActiveEditor().getSelection();
        if (text) navigator.clipboard.writeText(text);
    }
    function handleCut() {
        const ed = getActiveEditor();
        const text = ed.getSelection();
        if (text) {
            navigator.clipboard.writeText(text);
            ed.replaceSelection("");
        }
    }
    function handlePaste() {
        const ed = getActiveEditor();
        navigator.clipboard.readText().then(text => {
            if (text) ed.replaceSelection(text);
        });
    }
    function handleFind() { getActiveEditor().execCommand("find"); }
    function handleReplace() { getActiveEditor().execCommand("replace"); }
    function toggleWordWrap() {
        const ed = getActiveEditor();
        ed.setOption("lineWrapping", !ed.getOption("lineWrapping"));
    }
    function handleZoomIn() {
        const cm = document.querySelector(".CodeMirror");
        let size = parseInt(window.getComputedStyle(cm).fontSize);
        cm.style.fontSize = (size + 2) + "px";
    }
    function handleZoomOut() {
        const cm = document.querySelector(".CodeMirror");
        let size = parseInt(window.getComputedStyle(cm).fontSize);
        cm.style.fontSize = (size - 2) + "px";
    }

    // ================= MENU BINDINGS =================
    function bindMenuActions() {
        document.querySelectorAll('.submenu-item').forEach(item => {
            const text = item.textContent.trim();
            console.log("Binding action:", text);
            switch (text) {
                case "Undo": item.addEventListener("click", handleUndo); break;
                case "Redo": item.addEventListener("click", handleRedo); break;
                case "Cut": item.addEventListener("click", handleCut); break;
                case "Copy": item.addEventListener("click", handleCopy); break;
                case "Paste": item.addEventListener("click", handlePaste); break;
                case "Find & Replace": item.addEventListener("click", handleFind); break;
                case "Word Wrap": item.addEventListener("click", toggleWordWrap); break;
                case "Zoom In": item.addEventListener("click", handleZoomIn); break;
                case "Zoom Out": item.addEventListener("click", handleZoomOut); break;
                case "Run Code": item.addEventListener("click", updatePreview); break;
                case "Toggle Auto-Run":
                    item.addEventListener("click", () => {
                        window.autoRun = !window.autoRun;
                        alert("Auto-Run is now " + (window.autoRun ? "enabled" : "disabled"));
                    });
                    break;
            }
        });
    }
    bindMenuActions();
});
