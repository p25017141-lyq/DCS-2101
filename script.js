var discountPercent = 0;

function updateSeat(changedSeat) {
    var seats = document.getElementsByName("seat_ids[]");
    var count = 0;
    for (var i = 0; i < seats.length; i++) {
        if (seats[i].checked && !seats[i].disabled) {
            count++;
        }
    }
    if (count > 4) {
        changedSeat.checked = false;
        alert("Please select no more than 4 seats.");
    }
    calculateTotal();
}

function calculateTotal() {
    var seats = document.getElementsByName("seat_ids[]");
    var subtotal = 0;
    var count = 0;
    var labels = "";
    for (var i = 0; i < seats.length; i++) {
        var box = document.getElementById("seat-box-" + seats[i].value);
        box.className = "seat-option";
        if (seats[i].disabled) {
            box.className = "seat-option blocked";
        } else if (seats[i].checked) {
            subtotal = subtotal + Number(seats[i].getAttribute("data-price"));
            count++;
            if (labels != "") {
                labels = labels + ", ";
            }
            labels = labels + seats[i].getAttribute("data-label");
            box.className = "seat-option selected";
        }
    }
    var discount = Math.round(subtotal * discountPercent / 100);
    document.getElementById("subtotal").textContent = "Subtotal: " + money(subtotal);
    document.getElementById("discount").textContent = "Discount: −" + money(discount);
    document.getElementById("total").textContent = "Total: " + money(subtotal - discount);
    if (count == 0) {
        document.getElementById("selected-seats").textContent = "No seats selected";
    } else {
        document.getElementById("selected-seats").textContent = count + " seat(s): " + labels;
    }
    document.getElementById("form-message").textContent = "";
}

function money(sen) {
    return "RM" + (sen / 100).toFixed(2);
}

function applyPromo() {
    var input = document.getElementById("promo-code");
    var code = input.value.trim().toUpperCase();
    var rules = document.getElementById("promo-rules").options;
    var valid = false;
    discountPercent = 0;
    for (var i = 0; i < rules.length; i++) {
        if (code != "" && code == rules[i].value) {
            discountPercent = Number(rules[i].getAttribute("data-percent"));
            valid = true;
        }
    }
    var message = document.getElementById("promo-message");
    if (valid) {
        input.value = code;
        message.className = "success";
        message.textContent = "Promo code applied: " + discountPercent + "% off.";
    } else {
        message.className = "error";
        message.textContent = "Invalid or expired code. Use your personal reward code or leave it empty.";
    }
    calculateTotal();
}

function clearPromo() {
    discountPercent = 0;
    document.getElementById("promo-message").textContent = "";
    calculateTotal();
}

function clearSelection() {
    var seats = document.getElementsByName("seat_ids[]");
    for (var i = 0; i < seats.length; i++) {
        seats[i].checked = false;
    }
    document.getElementById("promo-code").value = "";
    clearPromo();
}

function validateBooking() {
    var seats = document.getElementsByName("seat_ids[]");
    var count = 0;
    for (var i = 0; i < seats.length; i++) {
        if (seats[i].checked && !seats[i].disabled) {
            count++;
        }
    }
    var name = document.getElementById("full-name").value.trim();
    var phone = document.getElementById("phone").value.trim();
    var digits = 0;
    var phoneValid = true;
    for (var j = 0; j < phone.length; j++) {
        var c = phone.charAt(j);
        if (c >= "0" && c <= "9") {
            digits++;
        } else if ("+ ().-".indexOf(c) == -1) {
            phoneValid = false;
        }
    }
    document.getElementById("name-error").textContent = "";
    document.getElementById("phone-error").textContent = "";
    document.getElementById("form-message").textContent = "";
    if (count < 1 || count > 4) {
        document.getElementById("form-message").textContent = "Select 1 to 4 seats.";
        return false;
    }
    if (name.length < 2 || name.length > 80) {
        document.getElementById("name-error").textContent = "Enter a full name with 2 to 80 characters.";
        document.getElementById("full-name").focus();
        return false;
    }
    if (!phoneValid || digits < 7 || digits > 15) {
        document.getElementById("phone-error").textContent = "Enter a phone number with 7 to 15 digits.";
        document.getElementById("phone").focus();
        return false;
    }
    return true; // 正常提交到 review.php。
}

function showHelp() {
    alert(
        "1. Browse or search for an event.\n" +
        "2. Choose its date and time first.\n" +
        "3. Select 1 to 4 seats. Grey seats are unavailable.\n" +
        "4. Apply your personal reward from My account.\n" +
        "5. Enter your details and review the booking.\n" +
        "6. Confirm the simulated payment and view My Tickets."
    );
}

if (document.getElementById("seat-grid")) {
    calculateTotal();
}


function validateManagement(form) {
    var inputs = form.querySelectorAll("input[type=text], input[type=email], input[type=tel], textarea");
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].value.trim() === "") {
            alert("Please complete all fields."); inputs[i].focus(); return false;
        }
    }
    if (form.elements.action.value === "delete") return confirm("Delete this record permanently?");
    return true;
}
