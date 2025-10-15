<?php

namespace App\Enum;

enum ReservationStatus: string {
    case Pending = 'pending';
    case Confirmed = 'confirmed';
}
