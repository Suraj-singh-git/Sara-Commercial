<?php

namespace App\Enums;

enum PaymentMode: string
{
    case FullOnline = 'full_online';
    case PartialCod = 'partial_cod';
    case Cod = 'cod';
}
