<?php

namespace App\Enums;

enum EndpointLogStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
}
