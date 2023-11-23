/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/form.scss';

// start the Stimulus application
import './bootstrap';

// Get all the input fields
var inputs = document.querySelectorAll(".form-input-field");
// Listen for changes to all the input fields
inputs.forEach(function(inputElement) {
    let input = inputElement.querySelector('input');
    if (!input){
        return;
    }

    // checkboxes should have always a visible labe
    console.log(input.classList);
    if(input.classList.contains('toggle-box')){
        var id = input.getAttribute("id");
        var label = document.querySelector("label[for='" + id + "']");
        if (label.innerHTML === ''){
            label.innerHTML = input.placeholder;
        }
        label.style.display = "block";
    }


    input.addEventListener("input", function() {
        // If the input field is not empty, show the corresponding label
        if (this.value) {
            let id = this.getAttribute("id");
            let label = document.querySelector("label[for='" + id + "']");
            if (label.innerHTML === ''){
                label.innerHTML = this.placeholder;
            }
            label.style.display = "block";
        } else {
            let id = this.getAttribute("id");
            let label = document.querySelector("label[for='" + id + "']");
            label.style.display = "none";
        }
    });
});

// curreny form field
// const currencyField = document.getElementById('currency-field');
var currencyField = document.querySelectorAll("input[data-type='currency']").item(0);

if (currencyField){
    currencyField.addEventListener('blur', (event) => {
        let value = event.target.value;

        // Replace commas with dots
        value = value.replace(',', '.');

        // Parse the value as a float
        const floatValue = parseFloat(value);

        // If the value is valid, format it as a currency
        if (!isNaN(floatValue)) {
            event.target.value = floatValue.toLocaleString('de-DE', { style: 'currency', currency: 'EUR' });
        }
    });
}


let passwordFirst = document.getElementById("user_password_first");
if (passwordFirst){
    passwordFirst.placeholder = 'Passwort';
}
let passwordSecond = document.getElementById("user_password_second");
if (passwordSecond){
    passwordSecond.placeholder = 'Passwort wiederholen';
}

// multiTransaction
