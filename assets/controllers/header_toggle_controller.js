import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['name']; // Declare the 'name' element (h5 in the header)

    connect() {


        if (this.nameTarget) {
            this.nameTarget.addEventListener('show.bs.collapse', this.expandName.bind(this));
            this.nameTarget.addEventListener('hide.bs.collapse', this.truncateName.bind(this));
        } else {
            console.warn(`Collapse element not found for selector: ${collapseSelector}`);
        }
    }

    collapseSelector() {
        // Dynamically find the ID of the element this button controls
        return this.element.getAttribute('data-bs-target');
    }

    expandName() {
        // Show the full name in the header
        if (this.nameTarget) {
            console.log('expand');
            console.log(this.nameTarget.innerHTML);
            console.log(this.nameTarget.getAttribute('data-expanded-text'));
            this.nameTarget.innerHTML = this.nameTarget.getAttribute('data-expanded-text');
            console.log(this.nameTarget.innerHTML);
        }
    }

    truncateName() {
        // Show the truncated name back in the header
        if (this.nameTarget) {
            this.nameTarget.textContent = this.nameTarget.getAttribute('data-truncated-text');
        }
    }

    disconnect() {
        // Clean up the event listeners when the controller is disconnected
        if (this.collapseElement) {
            this.collapseElement.removeEventListener('show.bs.collapse', this.expandName.bind(this));
            this.collapseElement.removeEventListener('hide.bs.collapse', this.truncateName.bind(this));
        }
    }
}