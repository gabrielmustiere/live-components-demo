import { Controller } from '@hotwired/stimulus';
import Reveal from 'reveal.js';
import 'reveal.js/dist/reveal.css';
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
