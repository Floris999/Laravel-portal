import { withVatTotals } from "./vat-totals";

export default ({ amount, vatRate, includesVat }) =>
    withVatTotals({
        amount,
        vatRate,
        includesVat,
        get gross() {
            return Number(this.amount) || 0;
        },
    });
