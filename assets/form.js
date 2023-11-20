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


// // select all input elements with data-type attribute equals to "currency"
// var currencyInputs = document.querySelectorAll("input[data-type='currency']");
//
// // loop through each input element and add event listeners
// currencyInputs.forEach(function(input) {
//     input.addEventListener("keyup", function() {
//         formatCurrency(input);
//     });
//     input.addEventListener("blur", function() {
//         formatCurrency(input, "blur");
//     });
// });
//
// function formatNumber(n) {
//     // format number 1000000 to 1,234,567
//     return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")
// }
//
// function formatCurrency(input, blur) {
//     // appends $ to value, validates decimal side
//     // and puts cursor back in right position.
//
//     // get input value
//     var input_val = input.value;
//     // input_val = input_val.replace('€', '');
//     // input_val = input_val.replace(" \u20AC", '');
//
//     // don't validate empty input
//     if (input_val === "") { return; }
//
//     // // don't validate empty input
//     // let numberCheck = Number(input_val);
//     //
//     // console.log(isNaN(numberCheck));
//     // if (isNaN(numberCheck)) {input.value = ''; return; }
//
//     // original length
//     var original_len = input_val.length;
//
//     // initial caret position
//     var caret_pos = input.selectionStart;
//
//     // check for decimal
//     if (input_val.indexOf(",") >= 0) {
//
//         // get position of first decimal
//         // this prevents multiple decimals from
//         // being entered
//         var decimal_pos = input_val.indexOf(",");
//
//         // split number by decimal point
//         var left_side = input_val.substring(0, decimal_pos);
//         var right_side = input_val.substring(decimal_pos);
//
//         // add commas to left side of number
//         left_side = formatNumber(left_side);
//
//         // validate right side
//         right_side = formatNumber(right_side);
//
//         // On blur make sure 2 numbers after decimal
//         if (blur === "blur") {
//             right_side += "00";
//         }
//
//         // Limit decimal to only 2 digits
//         right_side = right_side.substring(0, 2);
//
//         // join number by .
//         // input_val = left_side + "," + right_side;
//         input_val = left_side + "," + right_side + " \u20AC";
//
//     } else {
//         // no decimal entered
//         // add commas to number
//         // remove all non-digits
//         input_val = formatNumber(input_val);
//         input_val = input_val+" \u20AC";
//
//         // final formatting
//         if (blur === "blur") {
//             input_val += ",00";
//         }
//     }
//
//     // send updated string to input
//     input.value = input_val;
//
//     // put caret back in the right position
//     var updated_len = input_val.length;
//     caret_pos = updated_len - original_len + caret_pos;
//     input.setSelectionRange(caret_pos, caret_pos);
// }

let passwordFirst = document.getElementById("user_password_first");
if (passwordFirst){
    passwordFirst.placeholder = 'Passwort';
}
let passwordSecond = document.getElementById("user_password_second");
if (passwordSecond){
    passwordSecond.placeholder = 'Passwort wiederholen';
}

// multiTransaction
let multiTransactions = document.querySelectorAll(".multi-transaction");
multiTransactions.forEach(function (multiTransaction) {
    let totalField = multiTransaction.querySelector('.multi-transaction-total-field');
    let totalFieldValue = totalField.querySelector('.box-value .number');
    let userCounterField = multiTransaction.querySelector('.multi-transaction-use-counter-field');
    let userCounterFieldValue = userCounterField.querySelector('.box-value .number');
    let splitField = multiTransaction.querySelector('.multi-transaction-split-field');
    let splitFieldValue = splitField.querySelector('.box-value .number');
    let amountValue = document.getElementById('transaction_create_multiple_amount');
    let useTotal = document.getElementById('transaction_create_multiple_useTotal');
    let include = document.getElementById('transaction_create_multiple_includeCreator');

    include.addEventListener('change', (event) => {
        let additional = -1;
        if (include.checked) {
            additional = 1;
        }

        userCounterFieldValue.innerHTML = parseInt(userCounterFieldValue.innerHTML) + additional;
        calculateDistribution();
    });

    // check for amount change events
    let currencyField = document.querySelectorAll("input[data-type='currency']").item(0);
    currencyField.addEventListener('blur', (event) => {
        let value = event.target.value;

        // Replace commas with dots
        value = value.replace(',', '.');
        calculateDistribution(value);
    });

    // amount of user changed
    let selectedPanel = document.querySelector('.selected-panel .user-list');
    selectedPanel.addEventListener('click', function (event) {
        let newAmount = selectedPanel.querySelectorAll('.selected-user').length;

        let additional = 0;
        if (include.checked){
            additional = 1;
        }

        userCounterFieldValue.innerHTML = newAmount-1+additional;
        calculateDistribution();
    });
    let optionsPanel = document.querySelector('.options-panel .user-list');
    optionsPanel.addEventListener('click', function (event) {
        let additional = 0;
        if (include.checked) {
            additional = 1;
        }
        let newAmount = selectedPanel.querySelectorAll('.selected-user').length;
        userCounterFieldValue.innerHTML = newAmount + 1 + additional;
        calculateDistribution();

    });

    function calculateDistribution(value){
        if (!value){
            value = amountValue.value;
        }

        // calculate by total
        if (useTotal.value == 1){
            // Parse the value as a float
            const totalValue = parseFloat(value);

            // calculate split value
            let splitValue = totalValue/userCounterFieldValue.innerHTML;
            if (userCounterFieldValue.innerHTML == 0){
                splitValue = 0;
            }

            // // If the value is valid, format it as a currency
            if (!isNaN(totalValue)) {
                totalFieldValue.innerHTML = totalValue.toFixed(2);
            }
            if (!isNaN(splitValue)) {
                splitFieldValue.innerHTML = splitValue.toFixed(2);
            }
        }else{
            const splitValue = parseFloat(value);
            let totalValue = splitValue*userCounterFieldValue.innerHTML;
            if (userCounterFieldValue.innerHTML == 0){
                totalValue = 0;
            }

            // // If the value is valid, format it as a currency
            if (!isNaN(totalValue)) {
                totalFieldValue.innerHTML = totalValue.toFixed(2);
            }
            if (!isNaN(splitValue)) {
                splitFieldValue.innerHTML = splitValue.toFixed(2);
            }
        }
    }

    // check mode selection
    totalField.addEventListener('click', function (event) {
        switchVariant();
        amountValue.value = totalFieldValue.innerHTML;
    });
    splitField.addEventListener('click', function (event) {
        switchVariant();
        amountValue.value = splitFieldValue.innerHTML;
    });

    function switchVariant(){
        if (useTotal.value == 1){
            useTotal.value = 0;
            totalField.classList.remove('chosen');
            splitField.classList.add('chosen');
        }else{
            useTotal.value = 1;
            totalField.classList.add('chosen');
            splitField.classList.remove('chosen');
        }
    }
});
