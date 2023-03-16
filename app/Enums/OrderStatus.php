<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 1;
    case PAID = 2;
    case COMPLETED = 3;
    case CANCELED = 4;

    public static function all()
    {
        return [1, 2, 3, 4];
    }
}
