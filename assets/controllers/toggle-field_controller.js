import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ['label', 'checkbox', 'emblem'];

    static values = {
        checked: String,
        unchecked: String,
    };

    connect() {
        this.updateLabel();
    }

    updateLabel() {
        const isChecked = this.checkboxTarget.checked;
        const icon = this.emblemTarget;
        if (isChecked){
            icon.classList.remove('text-danger');
            icon.classList.add('text-success');
        }else{
            icon.classList.remove('text-success');
            icon.classList.add('text-danger');
        }

        // Change the textContent of the label based on the toggle state
        this.labelTarget.textContent = isChecked
            ? this.checkedValue
            : this.uncheckedValue;
    }
}