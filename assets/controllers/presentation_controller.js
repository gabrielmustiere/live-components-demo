import { Controller } from '@hotwired/stimulus';
import Reveal from 'reveal.js';
import RevealHighlight from 'reveal.js/dist/plugin/highlight.mjs';
import mermaid from 'mermaid';
import 'reveal.js/dist/reveal.css';
import 'reveal.js/dist/plugin/highlight/monokai.css';
import '../styles/presentation.css';

export default class extends Controller {
    async connect() {
        this.deck = new Reveal(this.element, {
            embedded: true,
            hash: true,
            history: true,
            transition: 'slide',
            controls: true,
            progress: true,
            slideNumber: 'c/t',
            width: 1280,
            height: 800,
            margin: 0.04,
            minScale: 0.2,
            maxScale: 2.0,
            center: false,
            plugins: [RevealHighlight],
        });

        mermaid.initialize({
            startOnLoad: false,
            theme: 'base',
            securityLevel: 'loose',
            fontFamily: 'ui-sans-serif, system-ui, sans-serif',
            flowchart: {
                htmlLabels: true,
                curve: 'basis',
                nodeSpacing: 45,
                rankSpacing: 70,
                padding: 16,
            },
            themeVariables: {
                fontSize: '20px',
                fontFamily: '"Helvetica Neue", Helvetica, Arial, sans-serif',
                // Palette indigo / slate alignée sur presentation.css
                background: 'transparent',
                primaryColor: '#312e81',
                primaryTextColor: '#e0e7ff',
                primaryBorderColor: '#818cf8',
                lineColor: '#a5b4fc',
                secondaryColor: '#1e293b',
                tertiaryColor: '#0f172a',
                clusterBkg: 'rgba(129, 140, 248, 0.06)',
                clusterBorder: 'rgba(129, 140, 248, 0.45)',
                titleColor: '#c7d2fe',
                edgeLabelBackground: 'rgba(15, 23, 42, 0.85)',
            },
        });

        await this.deck.initialize();
        await this.renderMermaid();

        this.deck.on('slidechanged', () => this.renderMermaid());
    }

    async renderMermaid() {
        const nodes = this.element.querySelectorAll('.mermaid:not([data-processed="true"])');
        for (const [idx, node] of [...nodes].entries()) {
            // On lit innerHTML puis on décode les entités, tout en gardant les
            // <br/> tels quels : Mermaid les comprend en htmlLabels, alors que
            // `textContent` les supprimerait complètement.
            const raw = node.innerHTML
                .replace(/<br\s*\/?\s*>/gi, '<br/>')
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&quot;/g, '"')
                .replace(/&#39;/g, "'");
            const source = raw.trim();
            if (!source) continue;
            try {
                const id = `mermaid-${Date.now()}-${idx}`;
                const { svg } = await mermaid.render(id, source);
                node.innerHTML = svg;
                node.setAttribute('data-processed', 'true');
            } catch (e) {
                console.error('Mermaid render error', e);
                node.setAttribute('data-processed', 'true');
            }
        }
    }

    disconnect() {
        if (this.deck) {
            this.deck.destroy();
            this.deck = null;
        }
    }
}
