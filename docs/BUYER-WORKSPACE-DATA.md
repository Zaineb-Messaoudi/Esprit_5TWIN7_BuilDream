# Buyer workspace frontend preview

The renter experience is a **frontend-only preview**. Sample reservations, rentals, payments, invoices, notifications, extension statuses, dashboard KPIs and all chart values are illustrative. No booking is created, no payment is processed, and print actions only open the browser print dialog.

## Screens

- Public discovery: home search, catalogue search and filters, equipment details, sample availability calendar and sign-in link for visitors who try to reserve.
- Buyer dashboard: six sample KPIs, upcoming reservation, reminders, spending-by-month, rentals-by-category and reservation-status charts.
- Buyer account: reservations and detail/stepper, rentals and contract/inspection detail, extension request/history, payments/invoices, notifications and profile link.
- Rental analytics: rental days by month and extension statuses. Payments analytics: spending by month and payment methods.

Buyer routes are declared in `config/front.php`; dedicated account preview screens are rendered by `PageController` and `resources/views/pages/front/buyer-workspace.blade.php`. The dashboard uses the shared workspace template and buyer navigation partial.

## Connecting real records later

1. Implement persisted reservations, payments, invoices, rentals, contracts, extensions and inspections with their agreed foreign keys before removing preview labels.
2. Replace the static arrays in `buyer-workspace.blade.php` and `workspace-dashboard.blade.php` with controller-provided, typed data. Keep database queries out of Blade.
3. Scope every account query through the authenticated renter (`user_id = auth()->id()`) and every equipment booking check must reject the authenticated equipment owner (`owner_id = auth()->id()`). Enforce these rules server-side; a hidden button or frontend check is not authorization.
4. Validate date ranges and prevent overlaps on the server before saving a reservation. Compute totals from trusted prices on the server, not from the browser's Alpine state.
5. Connect payment confirmation to the team's approved simulation or payment service, then create invoices and rentals according to the agreed booking lifecycle. A print view is not a downloadable PDF implementation.
6. Aggregate chart labels and numeric series from buyer-scoped persisted records. Add tests proving that records belonging to a different renter are excluded, cancelled/unpaid records are treated correctly, and empty accounts render helpful empty states.
7. Add real pagination and validated filter parameters once the screens are backed by persistent records.
