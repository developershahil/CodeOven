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
    
    // Set initial content
    htmlEditor.setValue(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Code</title>
</head>
<body>
    <h1>Hello World!</h1>
    <p>Welcome to the code editor.</p>
</body>
</html>`);
    
    cssEditor.setValue(`body {
    font-family: Arial, sans-serif;
    margin: 40px;
    background-color: #f5f5f5;
}

h1 {
    color: #4a86e8;
}`);
    
    jsEditor.setValue(`console.log('Hello from JavaScript!');
document.querySelector('h1').addEventListener('click', function() {
    alert('Heading clicked!');
});`);
    
    // Tab switching logic
    const tabs = document.querySelectorAll('.tab');
    const editorPanes = document.querySelectorAll('.editor-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Show corresponding editor pane
            editorPanes.forEach(pane => {
                if (pane.id === `${tabName}-editor`) {
                    pane.classList.remove('hidden');
                } else {
                    pane.classList.add('hidden');
                }
            });
        });
    });
    
    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    let isDarkTheme = false;
    
    themeToggle.addEventListener('click', () => {
        isDarkTheme = !isDarkTheme;
        
        if (isDarkTheme) {
            document.body.setAttribute('data-theme', 'dark');
            htmlEditor.setOption('theme', 'dracula');
            cssEditor.setOption('theme', 'dracula');
            jsEditor.setOption('theme', 'dracula');
            themeToggle.innerHTML = '☀️';
        } else {
            document.body.removeAttribute('data-theme');
            htmlEditor.setOption('theme', 'eclipse');
            cssEditor.setOption('theme', 'eclipse');
            jsEditor.setOption('theme', 'eclipse');
            themeToggle.innerHTML = '🌙';
        }
    });
    
    // Layout toggle functionality
    const layoutToggle = document.querySelector('.submenu-item[data-action="toggle-layout"]');
    const editorPreviewContainer = document.querySelector('.editor-preview-container');
    let isHorizontalLayout = true;
    
    if (layoutToggle) {
        layoutToggle.addEventListener('click', () => {
            isHorizontalLayout = !isHorizontalLayout;
            
            if (isHorizontalLayout) {
                editorPreviewContainer.classList.remove('vertical');
                editorPreviewContainer.classList.add('horizontal');
            } else {
                editorPreviewContainer.classList.remove('horizontal');
                editorPreviewContainer.classList.add('vertical');
            }
        });
    }
    
    // Run code functionality
    const runButton = document.getElementById('run-button');
    
    runButton.addEventListener('click', updatePreview);
    
    // Auto-update preview on code change (with debounce)
    let updateTimeout;
    
    htmlEditor.on('change', () => {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(updatePreview, 1000);
    });
    
    cssEditor.on('change', () => {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(updatePreview, 1000);
    });
    
    jsEditor.on('change', () => {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(updatePreview, 1000);
    });
    
    // Update preview function
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
            <head>
                <style>${cssCode}</style>
            </head>
            <body>
                ${htmlCode}
                <script>${jsCode}<\/script>
            </body>
            </html>
        `);
        previewDocument.close();
    }
    
    // Initial preview update
    updatePreview();
    
    // File explorer functionality (placeholder)
    const fileItems = document.querySelectorAll('.file-item');
    
    fileItems.forEach(item => {
        item.addEventListener('click', () => {
            fileItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            // In a real implementation, this would load the file content
        });
    });
});