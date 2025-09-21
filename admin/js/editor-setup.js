$(document).ready(function() {
    // Initialize TinyMCE editors
    tinymce.init({
        selector: 'textarea.editor',
        height: 300,
        menubar: false,
        branding: false,
        elementpath: false,
        statusbar: false,
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
        plugins: [
            'advlist autolink lists link charmap',
            'searchreplace visualblocks code',
            'insertdatetime table paste code wordcount'
        ],
        toolbar: [
            'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify',
            'bullist numlist | outdent indent | removeformat'
        ],
        formats: {
            bold: { inline: 'strong' },
            italic: { inline: 'em' }
        },
        style_formats: [
            { title: 'Heading', block: 'h3' },
            { title: 'Subheading', block: 'h4' },
            { title: 'Paragraph', block: 'p' }
        ],
        content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                padding: 15px;
            }
            p { margin: 0 0 1em 0; }
            ul, ol { margin: 0 0 1em 2em; }
        `,
        setup: function(editor) {
            editor.on('init', function() {
                this.getContainer().style.transition = 'border-color .15s ease-in-out';
                this.getContainer().style.border = '1px solid #ced4da';
                this.getContainer().style.borderRadius = '4px';
            });

            editor.on('change', function() {
                editor.save();
                validateEditor(editor);
            });
        }
    });

    // Validation helper
    function validateEditor(editor) {
        const content = editor.getContent().replace(/<[^>]*>/g, '').trim();
        const editorContainer = editor.getContainer();
        
        if (content === '') {
            editorContainer.style.borderColor = '#dc3545';
        } else {
            editorContainer.style.borderColor = '#28a745';
        }
    }

    // Form validation
    $('#jobForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate TinyMCE editors
        let isValid = true;
        
        ['job_description', 'required_skills', 'education_experience'].forEach(function(editorId) {
            const editor = tinymce.get(editorId);
            if (editor) {
                const content = editor.getContent().replace(/<[^>]*>/g, '').trim();
                if (content === '') {
                    isValid = false;
                    validateEditor(editor);
                    editor.focus();
                }
            }
        });

        if (!isValid) {
            showNotification('danger', 'Please fill in all required fields');
            return false;
        }

        // If validation passes, submit the form
        this.submit();
    });
});
