/*
Minimal integration layer to connect the existing UI with the new PHP API endpoints.
Drop this file in your js/ directory and include it in dashboard.php after dashboard.js:
<script src="../js/api_integration.js"></script>
It will:
 - populate the file explorer from the server
 - allow New / Save / Save As / Rename / Delete / Download via the top File menu
 - bind Ctrl+S to Save
This script intentionally keeps UI changes minimal and uses existing CSS classes/DOM structure.
*/
(function(){
    const apiBase = '../api/';
    let currentName = localStorage.getItem('current_project') || '';
    const htmlEditorEl = document.getElementById('html-editor');
    const cssEditorEl = document.getElementById('css-editor');
    const jsEditorEl = document.getElementById('js-editor');
    // CodeMirror instances are created in dashboard.js as variables in that scope.
    // We'll try to access them by walking window object.
    function getEditors(){
        // heuristic: attempt to find CodeMirror instances attached to containers
        const cms = {
            html: htmlEditorEl && htmlEditorEl.CodeMirror ? htmlEditorEl.CodeMirror : null,
        };
        // If not found, try fallback via document.querySelector
        const cmEls = document.querySelectorAll('.CodeMirror');
        let found = {html:null, css:null, js:null};
        // naive: pick in-order
        if (cmEls.length >= 3) {
            found.html = cmEls[0].CodeMirror || null;
            found.css = cmEls[1].CodeMirror || null;
            found.js = cmEls[2].CodeMirror || null;
        }
        return found;
    }

    function ajax(url, opts={}){
        return fetch(url, opts).then(r => r.json());
    }

    function populateFileList(){
        const list = document.querySelector('.file-list');
        if (!list) return;
        ajax(apiBase + 'get_files.php').then(resp => {
            if (!resp.success) return;
            list.innerHTML = '';
            resp.files.forEach(f => {
                const li = document.createElement('li');
                li.className = 'file-item';
                li.textContent = f.file_name;
                li.dataset.name = f.file_name;
                li.addEventListener('click', () => loadFile(f.file_name));
                list.appendChild(li);
            });
        }).catch(err => console.error(err));
    }

    function loadFile(name){
        ajax(apiBase + 'load_file.php?file_name=' + encodeURIComponent(name)).then(resp => {
            if (!resp.success) return alert(resp.message || 'Failed to load');
            const editors = getEditors();
            if (editors.html && editors.css && editors.js) {
                editors.html.setValue(resp.file.html || '');
                editors.css.setValue(resp.file.css || '');
                editors.js.setValue(resp.file.js || '');
            } else {
                // fallback: try to set textarea if present
                const h = document.getElementById('html-editor');
            }
            currentName = name;
            localStorage.setItem('current_project', currentName);
            highlightActiveFile();
        }).catch(err => console.error(err));
    }

    function highlightActiveFile(){
        document.querySelectorAll('.file-item').forEach(li => {
            if (li.dataset.name === currentName) li.classList.add('active');
            else li.classList.remove('active');
        });
    }

    function saveAsPrompt(){
        const name = prompt('Save as (project name)', currentName || 'untitled');
        if (name) saveFile(name);
    }

    function saveFile(name){
        const editors = getEditors();
        const payload = new URLSearchParams();
        payload.append('file_name', name || currentName || 'untitled');
        payload.append('html', editors.html ? editors.html.getValue() : '');
        payload.append('css', editors.css ? editors.css.getValue() : '');
        payload.append('js', editors.js ? editors.js.getValue() : '');
        fetch(apiBase + 'save_file.php', { method: 'POST', body: payload }).then(r => r.json()).then(resp => {
            if (resp.success) {
                currentName = payload.get('file_name');
                localStorage.setItem('current_project', currentName);
                populateFileList();
                highlightActiveFile();
                alert('Saved');
            } else {
                alert('Save failed: ' + (resp.message || 'unknown'));
            }
        }).catch(err => console.error(err));
    }

    function newFile(){
        const editors = getEditors();
        if (editors.html) editors.html.setValue('');
        if (editors.css) editors.css.setValue('');
        if (editors.js) editors.js.setValue('');
        currentName = '';
        localStorage.removeItem('current_project');
        highlightActiveFile();
    }

    function deleteFile(){
        if (!currentName) return alert('Select a project to delete.');
        if (!confirm('Delete "' + currentName + '"? This cannot be undone.')) return;
        const payload = new URLSearchParams();
        payload.append('file_name', currentName);
        fetch(apiBase + 'delete_file.php', { method: 'POST', body: payload }).then(r => r.json()).then(resp => {
            if (resp.success) {
                currentName = '';
                populateFileList();
                newFile();
                alert('Deleted');
            } else {
                alert('Delete failed: ' + (resp.message || 'unknown'));
            }
        });
    }

    function renameFile(){
        if (!currentName) return alert('Select a project to rename.');
        const newName = prompt('Rename to:', currentName);
        if (!newName || newName === currentName) return;
        const payload = new URLSearchParams();
        payload.append('old_name', currentName);
        payload.append('new_name', newName);
        fetch(apiBase + 'rename_file.php', { method: 'POST', body: payload }).then(r=>r.json()).then(resp => {
            if (resp.success) {
                currentName = newName;
                populateFileList();
                highlightActiveFile();
                alert('Renamed');
            } else {
                alert('Rename failed: ' + (resp.message || 'unknown'));
            }
        });
    }

    function downloadFile(){
        if (!currentName) return alert('Select a project to download.');
        window.location = apiBase + 'download.php?file_name=' + encodeURIComponent(currentName);
    }

    function hookMenu(){
        document.querySelectorAll('.submenu-item').forEach(item => {
            const text = (item.textContent || '').trim().toLowerCase();
            item.addEventListener('click', (e) => {
                if (text === 'new') newFile();
                else if (text === 'save') {
                    if (!currentName) saveAsPrompt(); else saveFile();
                } else if (text === 'save as') saveAsPrompt();
                else if (text === 'delete') deleteFile();
                else if (text === 'rename') renameFile();
                else if (text === 'download') downloadFile();
            });
        });
    }

    function bindShortcuts(){
        window.addEventListener('keydown', function(e){
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                if (!currentName) saveAsPrompt(); else saveFile();
            }
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'o') {
                e.preventDefault();
                populateFileList();
            }
        });
    }

    // initialize
    document.addEventListener('DOMContentLoaded', function(){
        hookMenu();
        bindShortcuts();
        populateFileList();
        // if we had a project name, attempt to load it
        if (currentName) {
            loadFile(currentName);
        }
    });

    // expose for debugging
    
/* === CLIENT-ONLY FILE EXPLORER OVERRIDES ===
   Replaces server-project grouped file handling with an individual-file sidebar explorer
   backed by localStorage (key: 'user_files').
   - Users can create files with any name/extension
   - Each file is shown individually in the sidebar
   - Before creating a new file, it prompts to save current changes
   - Autosave is enabled (debounced) to save current file to localStorage
   This block intentionally does not change any CSS or layout.
*/
(function(){
  // Storage helpers
  const LS_KEY = 'user_files_v1';
  function readFiles(){ try{ return JSON.parse(localStorage.getItem(LS_KEY) || '{}'); }catch(e){ return {}; } }
  function writeFiles(obj){ localStorage.setItem(LS_KEY, JSON.stringify(obj)); }

  // Editor helpers: find a primary editor (CodeMirror or textarea)
  function findEditorInstance(){
    const cmEls = document.querySelectorAll('.CodeMirror');
    if (cmEls && cmEls.length){
      // prefer first CodeMirror instance that has setValue
      for (let i=0;i<cmEls.length;i++){
        if (cmEls[i].CodeMirror && typeof cmEls[i].CodeMirror.setValue === 'function') return cmEls[i].CodeMirror;
      }
      // fallback to property CodeMirror on element
      if (cmEls[0].CodeMirror) return cmEls[0].CodeMirror;
    }
    // fallback to textarea or contenteditable
    const ta = document.querySelector('textarea, [contenteditable="true"]');
    if (ta) return ta;
    return null;
  }

  // UI update: populate sidebar list (.file-list element)
  function populateFileListLocal(){
    const list = document.querySelector('.file-list');
    if (!list) return;
    const files = readFiles();
    list.innerHTML = '';
    Object.keys(files).forEach(name => {
      const li = document.createElement('li');
      li.className = 'file-item';
      li.textContent = name;
      li.dataset.name = name;
      li.addEventListener('click', () => loadFileLocal(name));
      list.appendChild(li);
    });
  }

  // Load file into editor
  function loadFileLocal(name){
    const files = readFiles();
    if (!files[name]) return alert('File not found: ' + name);
    const ed = findEditorInstance();
    if (!ed) return alert('No editor instance found to load file.');
    const content = files[name].content || '';
    if (ed.getValue && typeof ed.setValue === 'function'){
      ed.setValue(content);
      // store last saved snapshot
      ed.__lastSavedValue = content;
    } else if (ed.value !== undefined){
      ed.value = content;
      ed.setAttribute && ed.setAttribute('data-last-saved', content);
    } else if (ed.innerText !== undefined){
      ed.innerText = content;
      ed.setAttribute && ed.setAttribute('data-last-saved', content);
    }
    window.currentName = name;
    // highlight active
    document.querySelectorAll('.file-item').forEach(li => li.classList.toggle('active', li.dataset.name === name));
  }

  // Save current editor content to localStorage under currentName (or provided name)
  function saveFileLocal(name){
    const files = readFiles();
    const ed = findEditorInstance();
    if (!ed) { console.warn('saveFileLocal: no editor'); return false; }
    const content = (ed.getValue && typeof ed.getValue === 'function') ? ed.getValue() : (ed.value !== undefined ? ed.value : ed.innerText);
    const target = name || window.currentName || prompt('Save file as (enter file name)', 'untitled.txt');
    if (!target) return false;
    files[target] = { content: content, updated: Date.now() };
    writeFiles(files);
    window.currentName = target;
    // mark saved snapshot
    if (ed.getValue) ed.__lastSavedValue = content;
    else ed.setAttribute && ed.setAttribute('data-last-saved', content);
    populateFileListLocal();
    // highlight active
    document.querySelectorAll('.file-item').forEach(li => li.classList.toggle('active', li.dataset.name === target));
    return true;
  }

  // New file creation with save prompt
  async function newFileLocal(){
    // check unsaved: call existing helper if present
    if (typeof window.saveCurrentIfDirty === 'function'){
      const ok = await window.saveCurrentIfDirty();
      if (!ok) return; // user cancelled or save failed
    } else {
      // basic check of editor's last-saved snapshot
      const ed = findEditorInstance();
      if (ed){
        const cur = (ed.getValue && ed.getValue()) || (ed.value !== undefined ? ed.value : ed.innerText);
        const last = (ed.getValue && ed.__lastSavedValue) || (ed.getAttribute && ed.getAttribute('data-last-saved')) || '';
        if (cur !== last){
          const r = confirm('You have unsaved changes. Save before creating a new file?');
          if (r) { if (!saveFileLocal()) return; }
        }
      }
    }
    const name = prompt('Create new file (include extension)', 'untitled.txt');
    if (!name) return;
    const files = readFiles();
    if (files[name]) { alert('File already exists'); return loadFileLocal(name); }
    files[name] = { content: '', updated: Date.now() };
    writeFiles(files);
    populateFileListLocal();
    loadFileLocal(name);
  }

  // Autosave: debounce and save current to localStorage
  let autosaveTimer = null;
  function scheduleAutosaveLocal(delay){
    if (autosaveTimer) clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(function(){
      if (window.currentName) saveFileLocal(window.currentName);
    }, delay || 700);
  }

  // Attach change listeners to editor
  function attachEditorAutosave(){
    const ed = findEditorInstance();
    if (!ed) return;
    if (ed.on && typeof ed.on === 'function'){ // CodeMirror
      try{ ed.on('change', function(){ scheduleAutosaveLocal(700); }); }catch(e){}
    } else if (ed.addEventListener){
      ed.addEventListener('input', function(){ scheduleAutosaveLocal(700); });
    }
  }

  // Expose local functions and override previous API names
  window.populateFileList = populateFileListLocal;
  window.loadFile = loadFileLocal;
  window.saveFile = saveFileLocal;
  window.newFile = newFileLocal;
  window.deleteFileLocal = function(name){ const files = readFiles(); if(files[name]){ delete files[name]; writeFiles(files); populateFileListLocal(); if(window.currentName===name) window.currentName = ''; } }
  window.renameFileLocal = function(oldName, newName){ const files = readFiles(); if(files[oldName] && !files[newName]){ files[newName]=files[oldName]; delete files[oldName]; writeFiles(files); populateFileListLocal(); } }
  // initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function(){ populateFileListLocal(); attachEditorAutosave(); });
  // Also attach to mutation observer in case editor created later
  new MutationObserver(function(){ attachEditorAutosave(); }).observe(document.body || document.documentElement, {childList:true, subtree:true});
})();  

window.editorAPI = { populateFileList, loadFile, saveFile, newFile, deleteFile, renameFile, downloadFile };
})();