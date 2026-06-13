<?php
// patch_editor.php
// Integrasi CodeMirror Engine & Markdown Toolbar pada Notepad

$file = __DIR__ . '/notepad/index.php';
if (!file_exists($file)) {
    die("File notepad/index.php tidak ditemukan.");
}

$content = file_get_contents($file);

// 1. Injeksi Library CodeMirror ke <head>
if (strpos($content, 'codemirror.min.css') === false) {
    $head = <<<EOF
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/markdown/markdown.min.js"></script>
    <style>
        .CodeMirror { height: 100% !important; flex: 1; font-family: 'Fira Code', monospace; font-size: 13.5px; line-height: 1.8; color: #1e293b; background: transparent; padding: 0; }
        .CodeMirror-gutters { border-right: 1px solid #e2e8f0; background-color: #f8fafc; padding-right: 5px; }
        .CodeMirror-linenumber { color: #94a3b8; padding-left: 10px; }
        .cm-s-default .cm-header { color: #0ea5e9; font-weight: bold; }
        .cm-s-default .cm-strong { font-weight: 800; color: #0f172a; }
        .cm-s-default .cm-em { font-style: italic; color: #0f172a; }
        .cm-s-default .cm-link { color: #10b981; text-decoration: underline; }
    </style>
</head>
EOF;
    $content = str_replace('</head>', $head, $content);
}

// 2. Injeksi Panel Toolbar & Editor Wrapper
$new_editor_html = <<<EOF
<div class="bg-slate-50 border-b border-slate-200 px-6 py-3 flex items-center gap-1.5 z-10 flex-wrap shadow-sm" id="editorToolbar" style="opacity: 0.5; pointer-events: none;">
                <button onclick="formatCM('bold')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Bold (Ctrl+B)"><i class="fa-solid fa-bold"></i></button>
                <button onclick="formatCM('italic')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Italic (Ctrl+I)"><i class="fa-solid fa-italic"></i></button>
                <button onclick="formatCM('strikethrough')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
                <div class="w-px h-5 bg-slate-300 mx-2"></div>
                <button onclick="formatCM('h1')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Heading 1"><i class="fa-solid fa-heading"></i></button>
                <button onclick="formatCM('h2')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Heading 2"><i class="fa-solid fa-heading text-sm"></i><sub class="text-[10px]">2</sub></button>
                <div class="w-px h-5 bg-slate-300 mx-2"></div>
                <button onclick="formatCM('ul')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Bullet List"><i class="fa-solid fa-list-ul"></i></button>
                <button onclick="formatCM('ol')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Number List"><i class="fa-solid fa-list-ol"></i></button>
                <div class="w-px h-5 bg-slate-300 mx-2"></div>
                <button onclick="formatCM('quote')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Quote"><i class="fa-solid fa-quote-right"></i></button>
                <button onclick="formatCM('code')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Code Block"><i class="fa-solid fa-code"></i></button>
                <button onclick="formatCM('link')" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-600 hover:text-emerald-600 flex items-center justify-center font-bold transition-all" title="Insert Link"><i class="fa-solid fa-link"></i></button>
                <div class="flex-1"></div>
                <div class="text-[10px] font-mono text-slate-400 tracking-widest uppercase mr-2 bg-white px-3 py-1.5 border border-slate-200 rounded-lg shadow-inner" id="cmStats">Ln 1, Col 1</div>
            </div>
            <div class="flex-1 relative w-full flex flex-col overflow-hidden bg-white" id="cmWrapper">
                <textarea id="rawEditor" style="display:none;"></textarea>
            </div>
EOF;

$content = preg_replace('/