# Owner dashboard: replacing preview data with real records

The owner dashboard and workspace screens are currently **frontend-only previews**. Dashboard charts, KPI values, tables, forms, status changes and activity are sample values for UI review only; they must not be used for business decisions. The preview includes equipment list/create/edit/detail, availability, reservation detail and list, rental/contract detail, extension requests, earnings/invoices, maintenance and inspection screens.

## Preview coverage

- **Dashboard KPIs (8):** total equipment, available now, active rentals, pending reservations, extension requests, revenue this month, returns due this week, and equipment in maintenance.
- **Charts (10):** revenue per month, reservations by status, equipment status, rentals by equipment, revenue by category, equipment occupancy rate, maintenance cost by equipment, payments by method, extension requests over time, and revenue vs. maintenance cost.
- **Chart placement:** dashboard (1–4); equipment detail → Revenue tab (6); maintenance (7); extensions (9); earnings (1, 5, 8, 10).
- **Screens:** owner overview; equipment list, create/edit and detail tabs; availability calendar; reservation list/detail; rental list/detail and printable contract; extension decisions; earnings/invoices; maintenance history/reports; inspections; shared account profile and notification preview.
- List search/status/category/date controls, equipment tabs, print actions, image preview, delete confirmation, and decision messages are presentational/client-side interactions only. Pagination controls are static; there is no saved state.

Forms and decision controls intentionally do not submit to a backend. The listing search and filters, equipment tabs, calendar equipment selector, print actions, image preview, and preview-only notifications are client-side interactions. Do not represent a preview action as a saved reservation, payment, approval or equipment change.

## Current backend boundary

This repository does not yet define persisted `Equipment`, `Reservation`, `Payment`, `Invoice`, `Rental`, `RentalExtension`, `Maintenance`, `Inspection`, or `EnergyProfile` models/migrations. The owner UI therefore cannot query or save real SolarShare activity today. Do not make the view appear live by changing these sample values.

## Data model prerequisites

Create and migrate the project entities first, following the team's agreed schema and enums. At minimum, the reporting relationships need to be:

- `User` has many owned `Equipment` (`equipment.owner_id`); `Equipment` belongs to `Category` and has one `EnergyProfile`.
- `Equipment` has many `Reservation`, `Rental`, `Maintenance`, and `Inspection` records.
- `Reservation` belongs to a renter and equipment, has many `Payment` records, and has one `Invoice`.
- `Rental` belongs to a renter and equipment, has one `RentalContract`, and has many `RentalExtension` and `Inspection` records.
- Store timestamps/date columns in a consistent timezone; use explicit reservation, payment, rental, maintenance, and extension status enums/constants.
- Add indexes for foreign keys and fields used by the dashboard, particularly `owner_id`, `equipment_id`, status, `payment_date`, and the rental/maintenance date ranges.

## Wiring real dashboard data

1. Move the dashboard data arrays out of the Blade view and into a dedicated owner-dashboard query/service, called from `PageController` when rendering `owner-dashboard`.
2. Start every query from the authenticated owner's equipment relationship (or an owner-scoped `Equipment` query). Never accept an owner ID from query parameters. Use that same owner scope for every chart, count, activity event, and to-do list.
3. Compute the eight KPI values from persisted records: total equipment; available equipment; active rentals; pending reservations; pending extension requests; paid revenue for the current month; rentals due for return during the current week; and equipment in maintenance. Define “available now” consistently with reservation/rental conflicts instead of trusting a stale status alone.
4. Build the action panel from actual pending reservations, pending extension requests, and rentals due for return. Return records (not only counts) so each action can link to its detail page.
5. Build recent activity from persisted reservation, payment, rental, extension, inspection, and maintenance events; sort by event timestamp descending and limit the result.
6. Aggregate chart data in the controller/service and pass only labels and numeric series to ApexCharts. Revenue should sum successful/paid `Payment.amount` values by payment date, joined through reservation/equipment and restricted to the owner. Reservations and equipment charts should group by their stored status. Rentals-by-equipment should group by equipment and use an explicit top-five ordering.
7. Keep database queries out of Blade. The page view should receive typed/structured data, not execute queries. Paginate table/list pages independently; do not load every owner's record just to build dashboard summaries.
8. Add feature tests with two owners and records belonging to both. Assert counts and chart values exclude the other owner, date windows are correct, unpaid transactions do not inflate revenue, and empty data renders as zero/empty state.
9. Remove the “Illustrative dashboard data” notice only after the page has been switched fully to database-backed values and the feature tests cover all displayed data.

## Chart option shape

The existing `<x-charts.apex>` component accepts an `options` array. Keep chart rendering in this existing component; replace the hardcoded `series` and category arrays in the dashboard view with values supplied by the query/service. Preserve labels, units, and empty states when an owner has no records.
