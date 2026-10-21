<?php

namespace App\Services\Maintenance;

use App\Models\Equipment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\URL;

class InspectionQrCodeService
{
    public function signedUrl(Equipment $equipment): string
    {
        return URL::temporarySignedRoute('technical.inspections.prefilled', now()->addDays(30), ['equipment' => $equipment->getKey()]);
    }

    public function svg(Equipment $equipment): string
    {
        return (new SvgWriter)->write(new QrCode($this->signedUrl($equipment), size: 320, margin: 12))->getString();
    }
}
