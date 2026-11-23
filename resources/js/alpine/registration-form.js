import { withVatTotals } from "./vat-totals";

export default ({ rates, productId, hours, vatRate, includesVat }) =>
    withVatTotals({
        rates,
        productId,
        hours,
        vatRate,
        includesVat,
        get rate() {
            return this.rates[this.productId] ?? 0;
        },
        get gross() {
            return (Number(this.hours) || 0) * this.rate;
        },
    });
