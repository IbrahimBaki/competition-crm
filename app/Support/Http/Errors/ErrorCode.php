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
    case TicketAlreadyClaimed = 'ticket.already_claimed';
    case TicketVersionConflict = 'ticket.version_conflict';
    case TicketNotAssigned = 'ticket.not_assigned';
    case TicketSameDepartment = 'ticket.same_department';
    case SavedViewNameTaken = 'ticket.saved_view_name_taken';
    case TicketIllegalTransition = 'ticket.illegal_transition';
    case TicketReopenWindowExpired = 'ticket.reopen_window_expired';
    case TicketTransitionReasonRequired = 'ticket.transition_reason_required';
    case TicketAlreadyMerged = 'ticket.already_merged';
    case TicketCannotMergeIntoItself = 'ticket.cannot_merge_into_itself';
    case TicketCannotLinkToItself = 'ticket.cannot_link_to_itself';
    case TicketNotSpam = 'ticket.not_spam';
    case TicketIsReadOnly = 'ticket.is_read_only';

    case IllegalDeliveryTransition = 'illegal_delivery_transition';
    case MessageNotRetryable = 'message_not_retryable';
    case InternalNoteNotSendable = 'internal_note_not_sendable';
    case TicketConversationReadOnly = 'ticket_conversation_read_only';

    case SlaBreachImmutable = 'sla.breach_immutable';
    case SlaClockNotRunning = 'sla.clock_not_running';
    case SlaClockAlreadyPaused = 'sla.clock_already_paused';
    case SlaPolicyInUse = 'sla.policy_in_use';
    case TargetAlreadyExhausted = 'sla.target_already_exhausted';

    case AutomationUnsupportedCondition = 'automation.unsupported_condition_operator';
    case AutomationUnsupportedAction = 'automation.unsupported_action_type';
    case AutomationRuleKeyTaken = 'automation.rule_key_taken';
    case AutomationExecutionImmutable = 'automation.execution_immutable';
    case AutomationEscalationReasonRequired = 'automation.escalation_reason_required';
    case AutomationEscalationTargetUnavailable = 'automation.escalation_target_unavailable';

    case NotificationTemplateMissing = 'notification.template_missing';
    case NotificationChannelUnsupported = 'notification.channel_unsupported';
    case NotificationNotAuthorised = 'notification.not_authorised';
    case NotificationPreferenceInvalid = 'notification.preference_invalid';

    case IllegalAgentTaskTransition = 'agent_task.illegal_transition';
    case AgentTaskOwnerUnavailable = 'agent_task.owner_unavailable';
    case QuickReplyTitleTaken = 'quick_reply.title_taken';
    case QuickReplyScopeMismatch = 'quick_reply.scope_mismatch';
    case TicketAlreadyWatched = 'ticket.already_watched';
    case TicketNotWatched = 'ticket.not_watched';
    case MentionNotAllowedOnPublicReply = 'mention.not_allowed_on_public_reply';
    case MentionTargetNotVisible = 'mention.target_not_visible';

    case EmailInboundUnparseable = 'email.inbound_unparseable';
    case EmailInboundAlreadyProcessed = 'email.inbound_already_processed';
    case EmailLoopDetected = 'email.loop_detected';

    case WebFormNotFound = 'channels.web_form.not_found';
    case WebFormInactive = 'channels.web_form.inactive';
    case WebFormValidationFailed = 'channels.web_form.validation_failed';

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
            self::TicketCannotLinkToItself,
            self::TicketNotAssigned,
            self::IllegalDeliveryTransition,
            self::InternalNoteNotSendable,
            self::SlaClockNotRunning,
            self::AutomationUnsupportedCondition,
            self::AutomationUnsupportedAction,
            self::AutomationEscalationReasonRequired,
            self::NotificationTemplateMissing,
            self::NotificationChannelUnsupported,
            self::NotificationPreferenceInvalid,
            self::IllegalAgentTaskTransition,
            self::AgentTaskOwnerUnavailable,
            self::QuickReplyTitleTaken,
            self::QuickReplyScopeMismatch,
            self::MentionNotAllowedOnPublicReply,
            self::MentionTargetNotVisible,
            self::EmailInboundUnparseable,
            self::EmailInboundAlreadyProcessed,
            self::EmailLoopDetected,
            self::WebFormValidationFailed => 422,

            self::Unauthorized,
            self::CustomerBlocked,
            self::TicketIsReadOnly,
            self::SlaBreachImmutable => 403,
            self::Unauthenticated,
            self::AccountDeactivated => 401,
            self::NotFound,
            self::WebFormNotFound,
            self::WebFormInactive => 404,
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
            self::TicketAlreadyClaimed,
            self::TicketVersionConflict,
            self::TicketSameDepartment,
            self::SavedViewNameTaken,
            self::TicketAlreadyMerged,
            self::TicketCannotMergeIntoItself,
            self::TicketNotSpam,
            self::MessageNotRetryable,
            self::TicketConversationReadOnly,
            self::SlaClockAlreadyPaused,
            self::SlaPolicyInUse,
            self::TargetAlreadyExhausted,
            self::AutomationRuleKeyTaken,
            self::AutomationExecutionImmutable,
            self::AutomationEscalationTargetUnavailable,
            self::TicketAlreadyWatched,
            self::TicketNotWatched => 409,

            self::IdempotencyKeyConflict => 409,
            self::InternalError => 500,
            self::TwoFactorRequired => 202,
        };
    }
}
