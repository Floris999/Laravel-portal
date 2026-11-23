export default ({ customers, selectedId }) => ({
    customers,
    selectedId,
    query: "",
    open: false,
    highlighted: 0,
    init() {
        this.query = this.selectedName();
    },
    selectedName() {
        return this.customers.find((customer) => customer.id === this.selectedId)?.name ?? "";
    },
    get filtered() {
        const term = this.query.trim().toLowerCase();
        if (term === "" || this.query === this.selectedName()) return this.customers;
        return this.customers.filter((customer) => customer.name.toLowerCase().includes(term));
    },
    select(customer) {
        this.selectedId = customer.id;
        this.query = customer.name;
        this.open = false;
    },
    choseHighlighted() {
        const customer = this.filtered[this.highlighted];
        if (customer) this.select(customer);
    },
    close() {
        this.open = false;
        this.query = this.selectedName();
    },
});
