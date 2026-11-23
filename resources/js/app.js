import customerCombobox from "./alpine/customer-combobox";
import expenseForm from "./alpine/expense-form";
import registrationForm from "./alpine/registration-form";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("customerCombobox", customerCombobox);
    window.Alpine.data("expenseForm", expenseForm);
    window.Alpine.data("registrationForm", registrationForm);
});
