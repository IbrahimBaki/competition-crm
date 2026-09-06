import { useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import type { UseQueryResult } from "@tanstack/react-query";
import { useGetCustomer } from "@/api/generated/customers/customers";
import { AsyncBoundary } from "@/shell/AsyncBoundary";
import { NotFoundState } from "@/shell/states/NotFoundState";
import { ForbiddenState } from "@/shell/states/ForbiddenState";
import { RequirePermission } from "@/auth/RequirePermission";
import { PERMISSIONS } from "@/auth/permissions";
import { toCustomerDetail } from "@/features/customers/api/wire";
import { CustomerHeader } from "@/features/customers/detail/CustomerHeader";
import { CustomerDangerActions } from "@/features/customers/detail/CustomerDangerActions";
import { CustomerIdentityPanel } from "@/features/customers/detail/CustomerIdentityPanel";
import { CustomerNotesPanel } from "@/features/customers/detail/CustomerNotesPanel";
import { CustomerAttachmentsPanel } from "@/features/customers/detail/CustomerAttachmentsPanel";
import { CustomerTimeline } from "@/features/customers/detail/CustomerTimeline";
import { ErpContextPanel } from "@/features/customers/detail/ErpContextPanel";
import { DuplicateCandidatesPanel } from "@/features/customers/duplicates/DuplicateCandidatesPanel";
import { ActionGuard } from "@/shell/ActionGuard";
import { V2PortalBoundary } from "@/design-system/foundations/V2PortalBoundary";
import { LoadingState } from "@/design-system/patterns/LoadingState";
import styles from "./CustomerDetailPageV2.module.css";

export function CustomerDetailPage() {
    const { customerId } = useParams<{ customerId: string }>();
    const { t, i18n } = useTranslation();

    const rawQuery = useGetCustomer(customerId ?? "", {
        query: { enabled: !!customerId },
    }) as unknown as UseQueryResult<unknown, unknown>;

    const query = {
        ...rawQuery,
        data: rawQuery.data ? toCustomerDetail(rawQuery.data) : undefined,
    } as UseQueryResult<ReturnType<typeof toCustomerDetail>, unknown>;

    if (!customerId)
        return (
            <V2PortalBoundary>
                <NotFoundState />
            </V2PortalBoundary>
        );

    return (
        <V2PortalBoundary
            dir={i18n.dir(i18n.language) === "rtl" ? "rtl" : "ltr"}
            lang={i18n.language === "ar" ? "ar" : "en"}
        >
            <RequirePermission
                permission={PERMISSIONS.CUSTOMERS_VIEW}
                fallback={<ForbiddenState />}
            >
                <AsyncBoundary
                    query={query}
                    loading={
                        <LoadingState title={t("pages.customers.title")} />
                    }
                    error={
                        (rawQuery.error as { status?: number } | undefined)
                            ?.status === 404 ? (
                            <NotFoundState />
                        ) : (rawQuery.error as { status?: number } | undefined)
                              ?.status === 403 ? (
                            <ForbiddenState />
                        ) : undefined
                    }
                >
                    {(customer) => (
                        <div className={styles.page}>
                            <CustomerHeader
                                customer={customer}
                                actions={
                                    <CustomerDangerActions
                                        customer={customer}
                                    />
                                }
                            />

                            <div className={styles.workbench}>
                                <div className={styles.primary}>
                                    <section className={styles.group}>
                                        <CustomerIdentityPanel
                                            customerUuid={customer.uuid}
                                        />
                                    </section>
                                    <section className={styles.group}>
                                        <CustomerNotesPanel
                                            customerUuid={customer.uuid}
                                        />
                                    </section>
                                    <section className={styles.group}>
                                        <CustomerAttachmentsPanel
                                            customerUuid={customer.uuid}
                                        />
                                    </section>
                                    <section className={styles.group}>
                                        <ActionGuard
                                            permission={
                                                PERMISSIONS.CUSTOMERS_DUPLICATE_VIEW
                                            }
                                        >
                                            <DuplicateCandidatesPanel
                                                customerUuid={customer.uuid}
                                            />
                                        </ActionGuard>
                                    </section>
                                </div>

                                <aside className={styles.inspector}>
                                    <section className={styles.group}>
                                        <ErpContextPanel
                                            customerUuid={customer.uuid}
                                        />
                                    </section>
                                    <section className={styles.group}>
                                        <h2>
                                            {t(
                                                "customers.detail.timeline_heading",
                                            )}
                                        </h2>
                                        <ActionGuard
                                            permission={
                                                PERMISSIONS.CUSTOMERS_TIMELINE_VIEW
                                            }
                                        >
                                            <CustomerTimeline
                                                customerUuid={customer.uuid}
                                            />
                                        </ActionGuard>
                                    </section>
                                </aside>
                            </div>
                        </div>
                    )}
                </AsyncBoundary>
            </RequirePermission>
        </V2PortalBoundary>
    );
}
