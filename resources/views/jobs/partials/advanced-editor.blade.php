<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4.13.23/es2021/jodit.min.css">
<style>
    .job-advanced-editor.jodit-container { --jd-color-icon: #f1f5f9; --jd-color-text-icons: #f1f5f9; --jd-color-text: #e2e8f0; --jd-color-label: #e2e8f0; --jd-color-separator: #64748b; --jd-dark-icon-color: #f1f5f9; --jd-dark-text-color: #f8fafc; border: 1px solid rgba(148, 163, 184, .45) !important; border-radius: .75rem; overflow: hidden; background: #0f172a !important; color: #f8fafc !important; }
    .job-advanced-editor .jodit-toolbar__box { border: 0 !important; border-bottom: 1px solid rgba(148, 163, 184, .25) !important; background: #172033 !important; }
    .job-advanced-editor .jodit-toolbar-button__button, .job-advanced-editor .jodit-toolbar-button__trigger { color: #dbeafe !important; }
    .job-advanced-editor .jodit-toolbar-button:not([disabled]) .jodit-toolbar-button__icon svg { fill: #e2e8f0 !important; stroke: #e2e8f0 !important; }
    .job-advanced-editor .jodit-toolbar-button:not([disabled]) .jodit-toolbar-button__icon path { fill: inherit; stroke: inherit; }
    .job-advanced-editor .jodit-toolbar-button__text, .job-advanced-editor .jodit-toolbar-button__trigger svg { color: #f1f5f9 !important; fill: #f1f5f9 !important; stroke: #f1f5f9 !important; }
    .job-advanced-editor .jodit-ui-group_separated_true:not(:last-child) { border-color: #64748b !important; }
    .job-advanced-editor .jodit-toolbar-button[disabled] { opacity: .48 !important; }
    .job-advanced-editor .jodit-workplace, .job-advanced-editor .jodit-wysiwyg, .job-advanced-editor .jodit-source { background: #0f172a !important; color: #f8fafc !important; }
    .job-advanced-editor .jodit-wysiwyg p, .job-advanced-editor .jodit-wysiwyg div, .job-advanced-editor .jodit-wysiwyg li, .job-advanced-editor .jodit-wysiwyg td, .job-advanced-editor .jodit-wysiwyg th { color: inherit; }
    .job-advanced-editor .jodit-wysiwyg { min-height: 300px !important; padding: 1rem !important; font-size: 15px; line-height: 1.65; }
    .job-advanced-editor .jodit-status-bar { border-top: 1px solid rgba(148, 163, 184, .2) !important; background: #111827 !important; color: #94a3b8 !important; }
    .job-advanced-editor .jodit-toolbar-button__button:hover, .job-advanced-editor .jodit-toolbar-button__button[aria-pressed="true"] { background: rgba(34, 211, 238, .18) !important; }
    .job-advanced-editor .jodit-wysiwyg table { width: 100%; border-collapse: collapse; }
    .job-advanced-editor .jodit-wysiwyg th, .job-advanced-editor .jodit-wysiwyg td { border: 1px solid #64748b; padding: .45rem; }
    .job-advanced-editor .jodit-wysiwyg img { max-width: 100%; height: auto; }
</style>
<script src="https://cdn.jsdelivr.net/npm/jodit@4.13.23/es2021/jodit.min.js"></script>
<script>
    (() => {
        const startAdvancedJobEditor = () => {
            const editorElement = document.getElementById('job-description-editor');
            const descriptionInput = document.getElementById('job-description-input');
            if (!editorElement || !descriptionInput || !window.Jodit || editorElement.dataset.ready) return;

            editorElement.dataset.ready = '1';
            editorElement.classList.add('job-advanced-editor');

            const transformSelection = (editor, transform, emptyMessage) => {
                const range = editor.s.range;
                if (!range || range.collapsed) {
                    editor.message.info(emptyMessage);
                    return;
                }
                const fragment = range.cloneContents();
                const walker = document.createTreeWalker(fragment, NodeFilter.SHOW_TEXT);
                let node;
                while ((node = walker.nextNode())) node.nodeValue = transform(node.nodeValue || '');
                range.deleteContents();
                range.insertNode(fragment);
                editor.synchronizeValues();
                editor.e.fire('change');
            };

            const editor = Jodit.make(editorElement, {
                theme: 'dark',
                height: 430,
                minHeight: 300,
                toolbarAdaptive: false,
                toolbarSticky: true,
                removeButtons: ['ai-commands', 'ai-assistant', 'file'],
                spellcheck: true,
                showCharsCounter: true,
                showWordsCounter: true,
                showXPathInStatusbar: true,
                placeholder: 'Describe the role, responsibilities, requirements, benefits and application process...',
                askBeforePasteHTML: false,
                askBeforePasteFromWord: false,
                defaultActionOnPaste: 'insert_as_html',
                defaultActionOnPasteFromWord: 'insert_as_html',
                processPasteHTML: true,
                cleanHTML: { fillEmptyParagraph: false },
                imageDefaultWidth: 480,
                uploader: {
                    url: @json($uploadUrl),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
                    imagesExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    isSuccess: response => response.success === true && !response.error,
                    getMessage: response => response.msg || 'Image upload failed.',
                    process: response => ({
                        files: response.files || [],
                        path: response.path || '',
                        baseurl: response.baseurl || '',
                        error: response.error || 0,
                        msg: response.msg || ''
                    })
                },
                extraButtons: [
                    {
                        name: 'uppercase',
                        text: 'ABC',
                        tooltip: 'UPPERCASE selected text',
                        exec: ed => transformSelection(ed, value => value.toUpperCase(), 'Select text to convert to uppercase.')
                    },
                    {
                        name: 'lowercase',
                        text: 'abc',
                        tooltip: 'lowercase selected text',
                        exec: ed => transformSelection(ed, value => value.toLowerCase(), 'Select text to convert to lowercase.')
                    },
                    {
                        name: 'titlecase',
                        text: 'Aa',
                        tooltip: 'Title Case selected text',
                        exec: ed => transformSelection(ed, value => value.toLowerCase().replace(/\b\p{L}/gu, letter => letter.toUpperCase()), 'Select text to convert to title case.')
                    }
                ]
            });

            const visibleContainer = editorElement.previousElementSibling?.matches('.jodit-container')
                ? editorElement.previousElementSibling
                : editorElement.parentElement?.querySelector('.jodit-container');
            visibleContainer?.classList.add('job-advanced-editor');

            editor.value = descriptionInput.value || '';
            const syncDescription = () => {
                const plainText = editor.editor?.innerText?.trim() || '';
                const containsMediaOrTable = /<(img|table|iframe|video)\b/i.test(editor.value);
                descriptionInput.value = (plainText || containsMediaOrTable) ? editor.value : '';
                editorElement.dataset.syncedLength = String(descriptionInput.value.length);
            };
            editor.e.on('change', syncDescription);
            editor.editor.addEventListener('input', syncDescription);
            const contentObserver = new MutationObserver(syncDescription);
            contentObserver.observe(editor.editor, {
                subtree: true,
                childList: true,
                characterData: true,
                attributes: true
            });
            descriptionInput.closest('form')?.addEventListener('submit', syncDescription);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startAdvancedJobEditor, { once: true });
        } else {
            startAdvancedJobEditor();
        }
    })();
</script>
