<?php

namespace App;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
}
