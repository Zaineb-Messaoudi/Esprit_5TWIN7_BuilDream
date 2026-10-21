<?php

return [
    // Decimal rate: 0.19 means 19%. Keep invoice and payment totals aligned.
    'tax_rate' => (float) env('BILLING_TAX_RATE', 0.19),
];
