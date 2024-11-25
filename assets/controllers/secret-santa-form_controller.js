// assets/controllers/secret-santa-form_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["checkbox", "field"];

    connect() {
        this.fieldTarget.style.display = 'none';
    }

    toggle() {
        if (this.checkboxTarget.checked) {
            this.fieldTarget.style.display = 'block';
        } else {
            this.fieldTarget.style.display = 'none';
        }
    }
}