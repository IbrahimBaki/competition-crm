<?php

namespace App\Support\Http\Errors;

enum ErrorCode: string
{
    case ValidationFailed = 'validation_failed';
    case Unauthorized = 'unauthorized';
    case Unauthenticated = 'unauthenticated';
    case NotFound = 'not_found';
    case RateLimited = 'rate_limited';
    case IdempotencyKeyConflict = 'idempotency_key_conflict';
    case InternalError = 'internal_error';

    case DepartmentHasOpenTickets = 'department.has_open_tickets';
    case BranchHasActiveDepartments = 'branch.has_active_departments';
    case UserBranchNotAttached = 'user.branch_not_attached';
    case AdminRoleLocked = 'admin_role_locked';
    case SystemRoleImmutable = 'system_role_immutable';
    case InvitationExpired = 'invitation_expired';
    case InvitationInvalid = 'invitation_invalid';
    case InvitationAlreadyPending = 'invitation_already_pending';
    case AccountLocked = 'account_locked';
    case AccountDeactivated = 'account_deactivated';
    case CannotDeactivateSelf = 'cannot_deactivate_self';
    case CannotDeactivateLastAdmin = 'cannot_deactivate_last_administrator';
    case InvalidTwoFactorCode = 'invalid_two_factor_code';
    case TwoFactorRequired = 'two_factor_required';
    case TwoFactorAlreadyEnabled = 'two_factor_already_enabled';
    case AuditLogImmutable = 'audit_log_immutable';

    case AttachmentScanPending = 'attachment.scan_pending';
    case AttachmentScanFailed = 'attachment.scan_failed';
    case AttachmentTooLarge = 'attachment.too_large';
    case AttachmentTypeNotAllowed = 'attachment.type_not_allowed';
    case BotProtectionFailed = 'bot_protection_failed';

    case UserAlreadyAnonymised = 'user.already_anonymised';
    case CannotAnonymiseLastAdmin = 'cannot_anonymise_last_administrator';
    case AuditRetentionWindowTooShort = 'audit_retention_window_too_short';

    case CustomerBlocked = 'customer.blocked';
    case CustomerAlreadyBlocked = 'customer.already_blocked';
    case CustomerNotBlocked = 'customer.not_blocked';
    case DuplicateContactIdentity = 'customer.duplicate_contact_identity';
    case CustomerMustHaveContact = 'customer.must_have_contact';
    case CannotMergeCustomerIntoItself = 'customer.cannot_merge_into_itself';
    case CustomerAlreadyMerged = 'customer.already_merged';

    case TicketCategoryDepthExceeded = 'ticket.category_depth_exceeded';
    case InvalidTicketCustomField = 'ticket.invalid_custom_field';
    case TicketCategoryInactive = 'ticket.category_inactive';
    case TicketAlreadyAssigned = 'ticket.already_assigned';
    case SavedViewNameTaken = 'ticket.saved_view_name_taken';
    case TicketIllegalTransition = 'ticket.illegal_transition';
    case TicketReopenWindowExpired = 'ticket.reopen_window_expired';
    case TicketTransitionReasonRequired = 'ticket.transition_reason_required';
    case TicketAlreadyMerged = 'ticket.already_merged';
    case TicketCannotMergeIntoItself = 'ticket.cannot_merge_into_itself';
    case TicketCannotLinkToItself = 'ticket.cannot_link_to_itself';
    case TicketNotSpam = 'ticket.not_spam';
    case TicketIsReadOnly = 'ticket.is_read_only';

    public function httpStatus(): int
    {
        return match ($this) {
            self::ValidationFailed,
            self::UserBranchNotAttached,
            self::AdminRoleLocked,
            self::SystemRoleImmutable,
            self::InvitationExpired,
            self::InvitationInvalid,
            self::InvitationAlreadyPending,
            self::InvalidTwoFactorCode,
            self::TwoFactorAlreadyEnabled,
            self::UserAlreadyAnonymised,
            self::AttachmentScanFailed,
            self::AttachmentTooLarge,
            self::AttachmentTypeNotAllowed,
            self::BotProtectionFailed,
            self::CustomerMustHaveContact,
            self::TicketCategoryDepthExceeded,
            self::InvalidTicketCustomField,
            self::TicketCategoryInactive,
            self::TicketIllegalTransition,
            self::TicketReopenWindowExpired,
            self::TicketTransitionReasonRequired,
            self::TicketCannotLinkToItself => 422,

            self::Unauthorized,
            self::CustomerBlocked,
            self::TicketIsReadOnly => 403,
            self::Unauthenticated,
            self::AccountDeactivated => 401,
            self::NotFound => 404,
            self::RateLimited => 429,

            self::AccountLocked,
            self::CannotDeactivateSelf,
            self::CannotDeactivateLastAdmin,
            self::CannotAnonymiseLastAdmin,
            self::DepartmentHasOpenTickets,
            self::BranchHasActiveDepartments,
            self::AuditLogImmutable,
            self::AuditRetentionWindowTooShort,
            self::AttachmentScanPending,
            self::CustomerAlreadyBlocked,
            self::CustomerNotBlocked,
            self::DuplicateContactIdentity,
            self::CannotMergeCustomerIntoItself,
            self::CustomerAlreadyMerged,
            self::TicketAlreadyAssigned,
            self::SavedViewNameTaken,
            self::TicketAlreadyMerged,
            self::TicketCannotMergeIntoItself,
            self::TicketNotSpam => 409,

            self::IdempotencyKeyConflict => 409,
            self::InternalError => 500,
            self::TwoFactorRequired => 202,
        };
    }
}
