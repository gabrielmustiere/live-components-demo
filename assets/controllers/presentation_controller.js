import { Controller } from '@hotwired/stimulus';
import Reveal from 'reveal.js';
import RevealHighlight from 'reveal.js/dist/plugin/highlight.mjs';
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
            plugins: [RevealHighlight],
        });

        await this.deck.initialize();
    }

    disconnect() {
        if (this.deck) {
            this.deck.destroy();
            this.deck = null;
        }
    }
}
