<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case DRAFT = 'Draft';
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case PARTIALLY_RECEIVED = 'Partially Received';
    case RECEIVED = 'Received';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';

    public function getColor(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::PARTIALLY_RECEIVED => 'primary',
            self::RECEIVED => 'success',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public static function getSelectOptions(): array
    {
        return [
            self::DRAFT->value => 'Draft',
            self::PENDING->value => 'Pending',
            self::APPROVED->value => 'Approved',
            self::PARTIALLY_RECEIVED->value => 'Partially Received',
            self::RECEIVED->value => 'Received',
            self::COMPLETED->value => 'Completed',
            self::CANCELLED->value => 'Cancelled',
        ];
    }
} 