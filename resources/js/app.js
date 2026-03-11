import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';

document.addEventListener('alpine:init', () => {
    Alpine.data('tiptapEditor', () => {
        let editor;

        return {
            updatedAt: Date.now(),

            init() {
                editor = new Editor({
                    element: this.$refs.editorEl,
                    extensions: [
                        StarterKit,
                        Underline,
                        Link.configure({ openOnClick: false }),
                    ],
                    content: '',
                    editorProps: {
                        attributes: {
                            class: 'prose prose-sm prose-invert focus:outline-none max-w-none min-h-[120px] px-3 py-2 text-zinc-200',
                        },
                    },
                    onUpdate: ({ editor: e }) => {
                        this.$wire.set('message', e.getHTML());
                        this.updatedAt = Date.now();
                    },
                    onSelectionUpdate: () => {
                        this.updatedAt = Date.now();
                    },
                    onTransaction: () => {
                        this.updatedAt = Date.now();
                    }
                });

                window.addEventListener('resetEditor', () => {
                    editor.commands.setContent('');
                    this.content = '';
                });

                window.addEventListener('loadAiSuggestion', (event) => {
                    const text = event.detail[0]?.content || event.detail?.content || '';
                    editor.commands.setContent(text);
                    editor.commands.focus();
                });
            },

            destroy() {
                if (editor) editor.destroy();
            },

            isActive(type, options = {}) {
                this.updatedAt; // Reactive dependency
                return editor ? editor.isActive(type, options) : false;
            },

            bold() { editor?.chain().focus().toggleBold().run(); },
            italic() { editor?.chain().focus().toggleItalic().run(); },
            underline() { editor?.chain().focus().toggleUnderline().run(); },
            bulletList() { editor?.chain().focus().toggleBulletList().run(); },
            orderedList() { editor?.chain().focus().toggleOrderedList().run(); },
            codeBlock() { editor?.chain().focus().toggleCodeBlock().run(); },
            getLinkUrl() { return editor?.getAttributes('link').href || ''; },
            setLink(url) {
                if (url) {
                    editor?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                } else {
                    editor?.chain().focus().extendMarkRange('link').unsetLink().run();
                }
            },
        };
    });
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
