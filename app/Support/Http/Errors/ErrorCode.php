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
    case PasswordResetTokenInvalid = 'password_reset_token_invalid';
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

    case WhatsappFreeFormWindowClosed = 'channels.whatsapp.free_form_window_closed';
    case WhatsappOptInRequired = 'channels.whatsapp.opt_in_required';
    case SmsOptedOut = 'channels.sms.opted_out';
    case ProviderTemplateNotApproved = 'channels.provider_template.not_approved';
    case ProviderTemplateVariableMissing = 'channels.provider_template.variable_missing';
    case ProviderMessageSendFailed = 'channels.provider_message.send_failed';

    case ChatIllegalTransition = 'chat.illegal_transition';
    case ChatSessionNotReconnectable = 'chat.session_not_reconnectable';
    case ChatSessionAlreadyEnded = 'chat.session_already_ended';
    case ChatTransferReasonRequired = 'chat.transfer_reason_required';
    case ChatTransferTargetUnavailable = 'chat.transfer_target_unavailable';
    case ChatUnavailable = 'chat.unavailable';
    case ChatAgentAtCapacity = 'chat.agent_at_capacity';
    case ChatVisitorContactRequired = 'chat.visitor_contact_required';

    case IllegalArticleTransition = 'illegal_article_transition';
    case ArticleNotPublished = 'article_not_published';
    case ArticleVisibilityForbidden = 'article_visibility_forbidden';
    case ArticleVersionNotFound = 'article_version_not_found';
    case DuplicateArticleFeedback = 'duplicate_article_feedback';
    case KnowledgeCategoryDepthExceeded = 'knowledge_category_depth_exceeded';

    case AiProviderUnavailable = 'ai.provider_unavailable';
    case AiFeatureDisabled = 'ai.feature_disabled';
    case AiBudgetExceeded = 'ai.budget_exceeded';
    case AiSuggestionAlreadyResolved = 'ai.suggestion_already_resolved';
    case AiSuggestionNotApproved = 'ai.suggestion_not_approved';

    case ReportRangeTooLarge = 'reports.range_too_large';
    case ReportExportTooLarge = 'reports.export_too_large';
    case ReportExportNotReady = 'reports.export_not_ready';
    case ReportExportExpired = 'reports.export_expired';
    case UnknownReport = 'reports.unknown_report';
    case UnsupportedExportFormat = 'reports.unsupported_export_format';
    case ReportScheduleRecipientLimitExceeded = 'reports.schedule_recipient_limit_exceeded';

    case PortalSessionInvalid = 'portal.session_invalid';
    case PortalAccountNotVerified = 'portal.account_not_verified';
    case PortalVerificationTokenInvalid = 'portal.verification_token_invalid';
    case PortalGuestGrantExpired = 'portal.guest_grant_expired';
    case PortalFeedbackAlreadySubmitted = 'portal.feedback_already_submitted';
    case PortalFeedbackInvitationInvalid = 'portal.feedback_invitation_invalid';

    case InsufficientTokenScope = 'integrations.insufficient_token_scope';
    case IntegrationDependencyUnavailable = 'integrations.dependency_unavailable';
    case ImportValidationFailed = 'integrations.import_validation_failed';
    case ImportFileUnreadable = 'integrations.import_file_unreadable';

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
            self::PasswordResetTokenInvalid,
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
            self::WebFormValidationFailed,
            self::WhatsappFreeFormWindowClosed,
            self::WhatsappOptInRequired,
            self::SmsOptedOut,
            self::ProviderTemplateNotApproved,
            self::ProviderTemplateVariableMissing,
            self::ProviderMessageSendFailed,
            self::IllegalArticleTransition,
            self::ArticleNotPublished,
            self::KnowledgeCategoryDepthExceeded,
            self::AiSuggestionNotApproved,
            self::PortalVerificationTokenInvalid,
            self::PortalFeedbackInvitationInvalid,
            self::ReportRangeTooLarge,
            self::ReportExportTooLarge,
            self::UnsupportedExportFormat,
            self::ReportScheduleRecipientLimitExceeded,
            self::ImportValidationFailed,
            self::ImportFileUnreadable,
            self::ChatIllegalTransition,
            self::ChatTransferReasonRequired,
            self::ChatVisitorContactRequired => 422,

            self::Unauthorized,
            self::CustomerBlocked,
            self::TicketIsReadOnly,
            self::SlaBreachImmutable,
            self::PortalSessionInvalid,
            self::ArticleVisibilityForbidden,
            self::InsufficientTokenScope => 403,
            self::Unauthenticated,
            self::AccountDeactivated,
            self::PortalAccountNotVerified => 401,
            self::NotFound,
            self::WebFormNotFound,
            self::WebFormInactive,
            self::ArticleVersionNotFound,
            self::UnknownReport => 404,

            self::ReportExportExpired => 410,
            self::ReportExportNotReady => 202,
            self::RateLimited,
            self::AiBudgetExceeded => 429,

            self::PortalGuestGrantExpired => 410,

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
            self::TicketNotWatched,
            self::DuplicateArticleFeedback,
            self::AiFeatureDisabled,
            self::AiSuggestionAlreadyResolved,
            self::PortalFeedbackAlreadySubmitted,
            self::ChatTransferTargetUnavailable,
            self::ChatSessionAlreadyEnded,
            self::ChatSessionNotReconnectable,
            self::ChatAgentAtCapacity => 409,

            self::AccountLocked => 423,

            self::IdempotencyKeyConflict => 409,
            self::InternalError => 500,
            self::AiProviderUnavailable,
            self::IntegrationDependencyUnavailable,
            self::ChatUnavailable => 503,
            self::TwoFactorRequired => 202,
        };
    }
}
