<?php

// All status
const PAYMENT_STATUS_PENDING = 0;
const PAYMENT_STATUS_PAID = 1;
const PAYMENT_STATUS_CANCELLED = 2;

const STATUS_PENDING = 0;
const STATUS_SUCCESS = 1;
const STATUS_REJECT = 3;
const STATUS_ACTIVE = 1;
const STATUS_DRAFT = 2;
const STATUS_DISABLE = 3;
const STATUS_DEACTIVATE = 3;
const STATUS_EXPIRED = 4;
const STATUS_SUSPENDED = 5;
const STATUS_CANCELED = 2;
const STATUS_CANCELLED = 2;
const STATUS_REFUND = 6;
const STATUS_INACTIVE = 0;

// User Role Type
const USER_STATUS_ACTIVE = 1;
const USER_STATUS_INACTIVE = 0;
const USER_ROLE_SUPER_ADMIN = 1;
const USER_ROLE_ADMIN = 2;
const USER_ROLE_USER = 3;
const USER_ROLE_STAFF = 4;

// Message
const SOMETHING_WENT_WRONG = "Something went wrong! Please try again";
const CREATED_SUCCESSFULLY = "Created Successfully";
const UPDATED_SUCCESSFULLY = "Updated Successfully";
const DELETED_SUCCESSFULLY = "Deleted Successfully";
const UPLOADED_SUCCESSFULLY = "Uploaded Successfully";
const DATA_FETCH_SUCCESSFULLY = "Data Fetch Successfully";
const SENT_SUCCESSFULLY = "Sent Successfully";
const DO_NOT_HAVE_PERMISSION = 7;
const CONFIG_NOT_FOUND = "Configuration not found";
const VALIDATION_FAILED = "Validation failed";
const CONFIG_VALIDATED = "Configuration is valid and ready to use";

// Currency placement
const CURRENCY_SYMBOL_BEFORE = 1;

const ACTIVE = 1;
const DEACTIVATE = 0;

// Agricultural / Consignment Statuses
const SHIPMENT_STATUS_DRAFT = 0;
const SHIPMENT_STATUS_DISPATCHED = 1;
const SHIPMENT_STATUS_RECEIVED = 2;
const SHIPMENT_STATUS_SELLING = 3;
const SHIPMENT_STATUS_RECONCILING = 4;
const SHIPMENT_STATUS_SETTLED = 5;
const SHIPMENT_STATUS_DISPUTED = 6;

const SETTLEMENT_STATUS_PENDING = 0;
const SETTLEMENT_STATUS_APPROVED = 1;

// Stock Movement Types
const STOCK_PURCHASE = 1;
const STOCK_DISPATCH = 2;
const STOCK_RECEIVE = 3;
const STOCK_SALE = 4;
const STOCK_RETURN = 5;
const STOCK_DAMAGE = 6;
const STOCK_ADJUSTMENT = 7;

// Account Types
const ACCOUNT_CASH = 1;
const ACCOUNT_BANK = 2;
const ACCOUNT_BKASH = 3;
const ACCOUNT_NAGAD = 4;
const ACCOUNT_ROCKET = 5;
const ACCOUNT_OTHER = 6;

// Market Types
const MARKET_SOURCE = 1;
const MARKET_DESTINATION = 2;
const MARKET_BOTH = 3;

// Expense Payment Statuses
const EXPENSE_PAYMENT_STATUS_UNPAID = 0;
const EXPENSE_PAYMENT_STATUS_PAID = 1;
const EXPENSE_PAYMENT_STATUS_DUE = 2;

// Transaction Types
const TRANSACTION_TYPE_DEBIT = 'debit';
const TRANSACTION_TYPE_CREDIT = 'credit';

if (!function_exists("getStatus")) {
    function getStatus($input = null)
    {
        $output = [
            STATUS_PENDING => __("Pending"),
            STATUS_SUCCESS => __("Success"),
            STATUS_ACTIVE => __("Active"),
            STATUS_INACTIVE => __("Inactive"),
            STATUS_DISABLE => __("Disabled"),
            STATUS_DRAFT => __("Draft"),
            STATUS_DEACTIVATE => __("Deactivate"),
            STATUS_EXPIRED => __("Expired"),
            STATUS_SUSPENDED => __("Suspended"),
        ];

        if (is_null($input)) {
            return $output;
        } else {
            return $output[$input] ?? '';
        }
    }
}

if (!function_exists("status_label")) {
    function status_label(int|null $status): string
    {
        return getStatus($status) ?: 'Unknown';
    }
}

if (!function_exists('expense_payment_status_label')) {
    function expense_payment_status_label(?int $status): ?string
    {
        return match ($status) {
            EXPENSE_PAYMENT_STATUS_PAID => 'paid',
            EXPENSE_PAYMENT_STATUS_UNPAID => 'unpaid',
            EXPENSE_PAYMENT_STATUS_DUE => 'due',
            default => null,
        };
    }
}

if (!function_exists("getMessage")) {
    function getMessage($input = null)
    {
        $output = [
            CREATED_SUCCESSFULLY => __("Created Successfully"),
            UPDATED_SUCCESSFULLY => __("Updated Successfully"),
            DELETED_SUCCESSFULLY => __("Deleted Successfully"),
            UPLOADED_SUCCESSFULLY => __("Uploaded Successfully"),
            DATA_FETCH_SUCCESSFULLY => __("Data Fetch Successfully"),
            SENT_SUCCESSFULLY => __("Sent Successfully"),
            SOMETHING_WENT_WRONG => __("Something went wrong! Please try again"),
            DO_NOT_HAVE_PERMISSION => __("You don't have the permission"),
        ];

        if (is_null($input)) {
            return $output;
        } else {
            return $output[$input] ?? '';
        }
    }
}

// Commission Types
const COMMISSION_TYPE_PERCENTAGE = 1;
const COMMISSION_TYPE_FIXED      = 2;

if (!function_exists('commission_type_label')) {
    function commission_type_label(int|null $type): string
    {
        $labels = [
            COMMISSION_TYPE_PERCENTAGE => __('Percentage'),
            COMMISSION_TYPE_FIXED      => __('Fixed'),
        ];

        return $labels[$type] ?? 'Unknown';
    }
}

if (!function_exists('market_type_label')) {
    function market_type_label(int|null $type): string
    {
        $labels = [
            MARKET_SOURCE      => __('Source'),
            MARKET_DESTINATION => __('Destination'),
            MARKET_BOTH        => __('Both'),
        ];

        return $labels[$type] ?? 'Unknown';
    }
}

if (!function_exists('account_type_label')) {
    function account_type_label(int|null $type): string
    {
        $labels = [
            ACCOUNT_CASH   => __('Cash'),
            ACCOUNT_BANK   => __('Bank'),
            ACCOUNT_BKASH  => __('bKash'),
            ACCOUNT_NAGAD  => __('Nagad'),
            ACCOUNT_ROCKET => __('Rocket'),
            ACCOUNT_OTHER  => __('Other'),
        ];

        return $labels[$type] ?? 'Unknown';
    }
}
// Purchase Statuses
const PURCHASE_STATUS_DRAFT     = 0;
const PURCHASE_STATUS_CONFIRMED = 1;
const PURCHASE_STATUS_RECEIVED  = 2;
const PURCHASE_STATUS_CANCELLED = 3;

if (!function_exists('purchase_status_label')) {
    function purchase_status_label(int|null $status): string
    {
        $labels = [
            PURCHASE_STATUS_DRAFT     => __('Draft'),
            PURCHASE_STATUS_CONFIRMED => __('Confirmed'),
            PURCHASE_STATUS_RECEIVED  => __('Received'),
            PURCHASE_STATUS_CANCELLED => __('Cancelled'),
        ];

        return $labels[$status] ?? 'Unknown';
    }
}