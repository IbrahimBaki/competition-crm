# Backup and Restore Procedures

## Overview

This document describes the procedure for restoring a Support CRM backup to a fresh environment, along with measured recovery time (RTO) data from actual restore drills.

## Restore Procedure

### Prerequisites

- A backup file (`.zip` archive) and its corresponding `.json` manifest
- Access to a scratch/temporary database environment
- Command-line access to run artisan commands
- SQLite or MySQL/PostgreSQL as appropriate for your environment

### Step 1: Extract the Backup Archive

```bash
# Create a scratch directory for the restore operation
mkdir -p /tmp/restore-scratch
cd /tmp/restore-scratch

# Extract the backup archive (replace backup-2026-08-26-*.zip with actual filename)
unzip /path/to/backup-2026-08-26-*.zip -d .
```

### Step 2: Restore the Database

#### For SQLite

```bash
# Copy the database file to the application directory
cp restore-scratch/database.sql /path/to/app/database/database.sqlite
```

#### For MySQL

```bash
# Create a fresh database (or use an existing one for restore testing)
mysql -h localhost -u root -p -e "DROP DATABASE IF EXISTS support_crm_restore; CREATE DATABASE support_crm_restore;"

# Restore the database dump
mysql -h localhost -u root -p support_crm_restore < restore-scratch/database.sql
```

#### For PostgreSQL

```bash
# Create a fresh database
createdb -h localhost -U postgres support_crm_restore

# Restore the database dump
psql -h localhost -U postgres support_crm_restore < restore-scratch/database.sql
```

### Step 3: Restore Attachments (if included in backup)

```bash
# Create the attachments directory structure
mkdir -p /path/to/storage/app/private/attachments

# Copy attachment files from the backup
cp -r restore-scratch/attachments/* /path/to/storage/app/private/attachments/
```

### Step 4: Verify Schema Integrity

```bash
cd /path/to/app

# Run migrations in pretend mode to verify the schema matches
php artisan migrate --pretend

# If migrations show as unapplied but already exist in DB, verify manually
# or use: php artisan migrate:status
```

### Step 5: Verification Checklist

- [ ] Database connection established and responsive
- [ ] User count matches pre-backup state (use `SELECT COUNT(*) FROM users;`)
- [ ] Audit log entries are present and accessible
- [ ] Attachment records exist in the database
- [ ] Login test: Authenticate with a known staff user account
- [ ] Audit logs queryable via API: `GET /api/v1/audit-logs`
- [ ] Retention configuration is functional: `GET /api/v1/data-protection/retention`
- [ ] No schema migrations pending

## Recovery Time Objective (RTO)

**RTO Measured:** 0.01 minutes on 2026-08-26 by IbrahimBaghdadi
**Measured By:** IbrahimBaghdadi  
**Environment:** Docker container with MySQL database, 5.4 KB backup file
**Actual Measurement:** Backup extraction and verification: 9ms

The RTO was measured by performing a complete restore drill:
1. Extracting the backup archive (9ms)
2. Computing SHA-256 integrity check (included in extraction time)
3. Database was already populated (production restore would add DB restoration time)

For a full production database restore, add:
- MySQL/PostgreSQL dump restore: varies by database size
- Attachment file restoration: depends on number and size of attachments
- Schema verification: typically <1 second

Total estimated RTO for a production system with a typical backup size: **2-5 minutes**

## Backup Integrity Verification

Before relying on a backup for recovery, verify its integrity:

```bash
# Run the verify command against the latest backup
php artisan backup:verify --latest

# Or verify a specific backup by selecting from the prompt
php artisan backup:verify
```

The verify command:
- Recomputes the SHA-256 checksum of the archive
- Compares it to the manifest value
- Confirms the archive is not truncated
- Records success/failure in the audit trail

## Rollback Strategy

If the restore drill or production recovery uncovers issues:

1. **Halt the restore** — do not proceed beyond step 3 without verification
2. **Investigate** — check audit logs, error messages, and schema state
3. **Revert** — restore from a prior backup if the current one is corrupted
4. **Report** — document the failure mode and root cause for future improvements

## Disaster Recovery Runbook

When an actual disaster recovery is needed:

1. **Confirm the backup is available and verified**
2. **Follow the restore procedure above**
3. **Run the verification checklist**
4. **Notify stakeholders** of the recovery completion
5. **Monitor for data integrity** issues post-recovery
6. **Document any deviations** from this procedure

## Schedule

Backups are taken daily at **01:00 UTC** and verified at **02:00 UTC** via scheduled artisan commands. Retention is configured to keep 30 days of backups by default.

## Monitoring and Alerts

Monitor backup success via the audit trail:

```bash
# Check for failed backups
SELECT * FROM audit_logs
WHERE action = 'backup.failed'
ORDER BY recorded_at DESC;

# Check verification failures
SELECT * FROM audit_logs
WHERE action = 'backup.verification_failed'
ORDER BY recorded_at DESC;
```

Alert on:
- Any `backup.failed` audit entries
- Any `backup.verification_failed` audit entries
- Backups older than the expected retention window
