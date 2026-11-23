const vatTotals = {
    get excl() {
        return this.includesVat ? this.gross / (1 + this.vatRate / 100) : this.gross;
    },
    get vat() {
        return this.includesVat ? this.gross - this.excl : (this.excl * this.vatRate) / 100;
    },
    get incl() {
        return this.includesVat ? this.gross : this.excl + this.vat;
    },
    euro(value) {
        return new Intl.NumberFormat("nl-NL", { style: "currency", currency: "EUR" }).format(value);
    },
};

/**
 * Adds the shared VAT getters to a component that defines `gross`, `vatRate` and `includesVat`.
 */
export const withVatTotals = (component) =>
    Object.defineProperties(component, Object.getOwnPropertyDescriptors(vatTotals));
