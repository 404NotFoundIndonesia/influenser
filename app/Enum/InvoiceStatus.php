<?php

namespace App\Enum;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';

    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }
}
