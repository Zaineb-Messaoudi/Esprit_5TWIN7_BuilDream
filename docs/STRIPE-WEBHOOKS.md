# Stripe Checkout webhooks

Reservation payments are marked as paid only after a signed Stripe webhook confirms the Checkout Session. The browser redirect is informational and does not finalize a payment.

## Local development

1. Start Laravel:

   ```powershell
   php artisan serve
   ```

2. In another terminal, forward Stripe events to the API endpoint:

   ```powershell
   stripe listen --events checkout.session.completed,checkout.session.async_payment_succeeded,checkout.session.async_payment_failed,checkout.session.expired,charge.refunded,refund.updated --forward-to http://127.0.0.1:8000/api/stripe/webhook
   ```

3. Copy the temporary `whsec_...` value printed by Stripe CLI into `.env` as `STRIPE_WEBHOOK_SECRET`, then reload configuration:

   ```powershell
   php artisan config:clear
   ```

Keep the Stripe CLI running while testing. Do not commit the webhook secret.

## Stripe Dashboard

For a deployed environment, create a webhook endpoint at `https://your-domain.example/api/stripe/webhook`, select the Checkout events below, and set the endpoint's signing secret as `STRIPE_WEBHOOK_SECRET` in the server environment:

- `checkout.session.completed`
- `checkout.session.async_payment_succeeded`
- `checkout.session.async_payment_failed`
- `checkout.session.expired`
- `charge.refunded`
- `refund.updated`

The endpoint verifies `Stripe-Signature` against the raw request body. Invalid signatures receive HTTP 400. Processing failures receive HTTP 500 so Stripe retries delivery. Successfully processed event IDs are stored and duplicate deliveries are acknowledged without repeating payment, rental, contract, or invoice creation.

## Reservation payment installments

After an owner approves a reservation, the renter pays a 30% deposit. The remaining balance is due seven days before the rental starts and can be paid earlier. If the rental starts within seven days of approval, the full amount is due immediately. Amounts include the existing 19% tax.

Each payment attempt records its stage and due date in `payments`. The reservation invoice is marked `partially_paid` after a verified deposit and `paid` only after all payments cover the full invoice total. A rental and its contract are created only after the full amount has been verified. Both the signed webhook and the verified Checkout return use the local payment attempt amount when validating a Stripe session.

The daily `payments:send-overdue-reminders` command sends separate balance reminders three days before the due date, on the due date, and once after the due date if a balance remains. Each reminder is recorded independently on the invoice to prevent repeat notifications. Reminders use the notification center and, when enabled in the renter's preferences, email. Production deployments must run Laravel's scheduler (for example, `php artisan schedule:run` every minute); locally, use `php artisan schedule:work`.

## Reservation cancellation and refunds

The renter can request cancellation from the live reservation details. The policy is evaluated using calendar days until the rental start: at least seven days means a full refund, one to six days means a 50% refund, and on or after the start date means no refund. The percentage applies to the net amount actually paid (after previous refunds). Partial amounts are allocated across paid installments, and the remaining net paid amount is retained.

For eligible Stripe Checkout payments, the application sends full or partial refunds with an idempotency key. Cancellation is finalized only after Stripe confirms the amount due; `refund.updated` and `charge.refunded` events reconcile the actual refund. Refunds below Stripe's minimum are not silently rounded up.

Payments made outside Stripe, requests with mixed payment methods, and cancellation on/after the rental start require administrator review. An administrator must confirm that the calculated amount for off-Stripe payments has actually been transferred. After the start date the refund is zero, but administrator approval is still required before cancelling an active reservation/rental. Approval cancels the reservation/rental and terminates its contract; denial restores the reservation to confirmed if no refund has been initiated. An in-flight Stripe payment must be resolved before cancellation can be requested.

Run the focused tests with:

```powershell
php artisan test tests\Feature\StripeWebhookTest.php
```
