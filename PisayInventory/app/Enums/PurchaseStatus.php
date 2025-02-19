<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case DRAFT = 'Draft';
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case PARTIALLY_RECEIVED = 'Partially Received';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';

    public function getColor(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::PARTIALLY_RECEIVED => 'primary',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }
} 