<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case RECEIVED = 'Received';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';

    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::RECEIVED => 'success',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public static function getSelectOptions(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::APPROVED->value => 'Approved',
            self::RECEIVED->value => 'Received',
            self::COMPLETED->value => 'Completed',
            self::CANCELLED->value => 'Cancelled',
        ];
    }
} 