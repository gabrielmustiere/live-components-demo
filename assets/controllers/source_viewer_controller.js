import { Controller } from '@hotwired/stimulus';
import hljs from 'highlight.js';
import 'reveal.js/dist/plugin/highlight/monokai.css';

export default class extends Controller {
    static targets = ['code', 'filename', 'source', 'copyLabel'];

    open(event) {
        const path = event.currentTarget.dataset.path;
        const tpl = this.sourceTargets.find((t) => t.dataset.path === path);

        if (!tpl) {
            return;
        }

        const rawLanguage = tpl.dataset.language || 'plaintext';
        const hljsLanguage = rawLanguage === 'twig' ? 'xml' : rawLanguage;

        this.codeTarget.textContent = tpl.content.textContent;
        this.codeTarget.className = `hljs block p-5 text-sm leading-relaxed language-${hljsLanguage}`;
        this.codeTarget.removeAttribute('data-highlighted');
        hljs.highlightElement(this.codeTarget);

        this.filenameTarget.textContent = path;
    }

    async copy() {
        try {
            await navigator.clipboard.writeText(this.codeTarget.textContent || '');
            this.copyLabelTarget.textContent = 'Copié !';
        } catch {
            this.copyLabelTarget.textContent = 'Échec';
        }

        setTimeout(() => {
            this.copyLabelTarget.textContent = 'Copier';
        }, 1500);
    }
}
