# Tenancy Audit Summary

## Command
Run `php artisan webileri:tenancy:audit` after migrations or data imports to verify:
- No tenant table contains `NULL company_id` rows.
- No child record references a parent with a different `company_id` (detected via heuristic joins).
- Results are appended to `storage/logs/tenancy_audit.log` (customise via `--log=`).【F:app/Core/Tenancy/Console/Commands/TenancyAuditCommand.php†L17-L110】

## Sample Output
```
--- Tenancy audit run at 2024-10-15 12:34:56 ---
products: 0 record(s) with NULL company_id
  - products.category_id vs product_categories.company_id mismatches: 0
orders: 0 record(s) with NULL company_id
  - orders.customer_id vs customers.company_id mismatches: 0
--- End of tenancy audit ---
```

## Follow-up Actions
1. Investigate tables flagged with missing `company_id` and backfill using parent references, then rerun the audit.
2. If cross-tenant mismatches appear, validate corresponding foreign key columns and add composite constraints in subsequent migrations.
3. Archive the generated log artefact in deployment pipelines for traceability.

