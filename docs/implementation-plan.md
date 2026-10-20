# Step-by-Step Implementation Plan: Agricultural Trading Business Management

This plan establishes the execution sequence for transforming `AgroHisab-ecosystem-business-service` into a specialized agricultural trading & consignment management platform, integrated with `AgroHisab-ecosystem-api-gateway` and `AgroHisab-ecosystem-auth-service`.

---

## Phase Matrix

| Phase | Title | Scope & Objectives | Deliverables / Acceptance Criteria | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Phase 0** | **Audit & Architecture** | Inspect repos, microservices, auth, env configs; establish conventions and audit unresolved decisions. | `docs/architecture.md`, `docs/business-rules.md`, `docs/implementation-plan.md`. | **Completed** |
| **Phase 1** | **Domain Model & Migrations** | Clean out old unmigrated legacy references; create standard migrations for all agricultural trading entities. | Migrations run cleanly on `agrobusinessservice`; DB constraints, foreign keys, and indexes established. | Ready |
| **Phase 2** | **Master Data & Legacy Cleanup** | Safely remove old `Outlet`, `Visit`, `Beat` controllers/routes; implement Master Data APIs (Products, Units, Suppliers, Aratdars, Markets, Transporters, Accounts). | Full CRUD + validation tests for master data; legacy routes removed cleanly. | Pending |
| **Phase 3** | **Purchases & Supplier Ledger** | Implement purchase orders, line items, supplier payments, handling expenses, and source stock lot intake. | Purchases create stock movement into source lot; supplier payable ledger balances properly. | Pending |
| **Phase 4** | **Shipments, Challans & Goods Receipt** | Implement consignment shipments, lot allocations, truck/driver freight tracking, challan PDF generation, and arat receipt with shortage/damage logs. | Stock moves to transit, then to Arat; weight discrepancies documented with reasons. | Pending |
| **Phase 5** | **Sales Reports, Commission & Stock** | Record partial/multi-date sales by Aratdars, automatic contract commission calculation, unsold stock tracking. | Sales deduct available arat stock; historical commission immutable upon recording. | Pending |
| **Phase 6** | **Payments, Ledger & Consignment Settlement** | Payment receipts from aratdars, payment allocations across shipments, shipment reconciliation statement, realized P&L, owner settlement lock. | Final settlement locks records; profit calculation separates unsold stock from loss. | Pending |
| **Phase 7** | **Documents, Dashboard & Reports** | File attachments (memos, photos, receipts), timeline diary, real-time dashboard, cashbook, receivables aging, P&L reports with PDF/CSV export. | Dashboard displays verified vs estimated totals; reports reconcile to underlying ledgers. | Pending |
| **Phase 8** | **Security, Audit & Backup** | Transaction atomicity review, audit trails on critical financial actions, automated DB and document backup scripts. | Backup & restore validated; financial transactions wrapped in atomic DB transactions. | Pending |
| **Phase 9** | **End-to-End Simulation & Verification** | Run the complete 1,000 kg test scenario: purchase -> partial payment -> dispatch -> receipt -> partial sales -> commission -> remittance -> settlement -> report verification. | 100% mathematical reconciliation; test suite passing. | Pending |
| **Phase 10**| **Post-MVP Extensions** | Multi-branch support, offline sync, aratdar restricted portal, QR codes. | Deferred until core flow is verified in production. | Deferred |

---

## Detailed Step-by-Step Task Breakdown

### Phase 1: Domain Model & Database Schema
1. **Migrations Creation:**
   - `0001_01_02_000001_create_units_and_conversions_tables.php`
   - `0001_01_02_000002_create_products_and_categories_tables.php`
   - `0001_01_02_000003_create_parties_tables.php` (`suppliers`, `aratdars`, `markets`, `transporters`, `commission_agreements`)
   - `0001_01_02_000004_create_accounts_and_expense_categories_tables.php`
   - `0001_01_02_000005_create_purchases_and_purchase_items_tables.php`
   - `0001_01_02_000006_create_shipments_and_allocations_tables.php`
   - `0001_01_02_000007_create_goods_receipts_tables.php`
   - `0001_01_02_000008_create_sales_and_sale_items_tables.php`
   - `0001_01_02_000009_create_stock_movements_table.php`
   - `0001_01_02_000010_create_expenses_table.php`
   - `0001_01_02_000011_create_payments_and_allocations_tables.php`
   - `0001_01_02_000012_create_settlements_and_lines_tables.php`
   - `0001_01_02_000013_create_documents_and_business_diary_tables.php`
   - `0001_01_02_000014_create_audit_logs_table.php`
2. **Eloquent Models & Relationships:**
   - Strict typing, casts for `decimal:2` and `decimal:4`, and `HasCompany` tenant scope.

### Phase 2: Master Data & Deprecation of Outdated Code
1. Safely remove legacy models (`Outlet`, `Visit`, `Beat`, `Competitor`, `KpiTarget`).
2. Clean `routes/api/v1.php` of unused routes.
3. Build Controllers, Form Requests, Resources, and Services for:
   - Units & Unit Conversions (`/api/v1/units`, `/api/v1/unit-conversions`)
   - Product Categories & Products (`/api/v1/categories`, `/api/v1/products`)
   - Suppliers (`/api/v1/suppliers`)
   - Aratdars & Commission Agreements (`/api/v1/aratdars`, `/api/v1/aratdars/{id}/agreements`)
   - Markets (`/api/v1/markets`)
   - Transporters (`/api/v1/transporters`)
   - Accounts (`/api/v1/accounts`)
   - Expense Categories (`/api/v1/expense-categories`)

### Phase 3: Purchases & Supplier Financials
1. `POST /api/v1/purchases`: Record purchase line items, bag count, gross/net weight.
2. `POST /api/v1/purchases/{id}/receive`: Confirm stock receipt at source, issuing initial `stock_movements`.
3. `POST /api/v1/purchases/{id}/payments`: Allocate supplier payments from selected financial account.
4. `GET /api/v1/suppliers/{id}/ledger`: Supplier balance and transaction history.

### Phase 4: Shipment, Delivery Challan & Arat Receipt
1. `POST /api/v1/shipments`: Create shipment, allocate available lots, assign transporter and destination Aratdar.
2. `POST /api/v1/shipments/{id}/dispatch`: Move stock from source to in-transit.
3. `GET /api/v1/shipments/{id}/challan-pdf`: Generate printable delivery challan.
4. `POST /api/v1/shipments/{id}/receive`: Record arrival at arat with actual received weights, shortage, and damage reasons. Stock shifts from in-transit to arat inventory.

### Phase 5: Sales Reports, Deductions & Inventory
1. `POST /api/v1/shipments/{id}/sales`: Record sales transactions with quantity, rate, discount, and buyer notes.
2. Compute commission dynamically according to the aratdar's active agreement rule and persist it.
3. Post negative `stock_movements` against the consignment at the Aratdar location.
4. `GET /api/v1/shipments/{id}/stock`: Consignment balance (dispatched vs received vs sold vs remaining unsold vs loss).

### Phase 6: Payments, Settlement & Reconciliation
1. `POST /api/v1/payments/aratdar-remittance`: Receive payment from Aratdar into cash/bank/MFS account.
2. `POST /api/v1/payments/{id}/allocate`: Allocate remittance across open shipments.
3. `GET /api/v1/shipments/{id}/settlement-preview`: Compare gross sales, commission, deductions, remittances, and calculate realized profit.
4. `POST /api/v1/shipments/{id}/settle`: Explicit owner approval locks the settlement and generates the final financial statement.

### Phase 7: Documents, Reports & Dashboard
1. Document upload & attachment to parties, purchases, shipments, payments.
2. Dashboard metrics: Cash & MFS balances, open receivables, active consignments by status, unrealized vs realized profit.
3. Reports: Cashbook, party ledgers, shipment profitability, inventory valuation, PDF/Excel export.
