# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

PHP 7.3+ with MySQL 5.7+, Apache/Nginx, pure PHP MVC pattern without framework. TCPDF for PDF generation.

## Users

**Primary:** Nasabah (Village Residents) — community members who deposit recyclable waste at the waste bank, track their savings balance, and request withdrawals.

**Supporting:**
- **Pengelola (Operator)** — waste bank staff who weigh incoming waste, record transactions, manage pricing, sell to collectors (pengepul), and handle withdrawals
- **Admin** — technical staff who configure the system, manage users, villages, and waste banks
- **Kades (Village Head)** — village leadership who monitor operations and review financial reports

## Product Purpose

Bank Sampah Digital Desa enables villages to digitize their waste bank operations: converting community waste deposits into traceable savings, with balance tracking, withdrawal requests, and financial reporting for village government oversight.

**Success definition:** A Nasabah can deposit waste, see their balance grow, and withdraw savings with full transparency. Pengelola can run daily operations without paper. Village leadership gets real-time visibility into the program's impact.

## Positioning

A self-hosted, zero-license-cost waste bank management system purpose-built for Indonesian village governments (Pemerintah Desa). Unlike generic accounting software or commercial SaaS, it models the specific workflows of waste bank operations: waste categorization, live pricing per category, deposit-to-balance conversion, and withdrawal approval flows.

## Operating Context

- **Environment:** Rural Indonesian villages with limited internet connectivity; self-hosted on local server or shared hosting
- **Users:** Community members with varying digital literacy; 操作简单 is critical
- **Workflows:**
  - Nasabah brings waste → Pengelola weighs and categorizes → system credits Nasabah's balance
  - Nasabah requests withdrawal → Pengelola verifies → balance debited → cash paid
  - Kades reviews daily/weekly summaries
- **Documents:** Physical cash exchanges still happen; the system records digital proof, not replaces cash handling
- **Rituals:** Weekly or bi-weekly deposit schedules; monthly balance reconciliation

## Capabilities and Constraints

**Capabilities:**
- Role-based authentication (Admin, Pengelola, Kades, Nasabah)
- CRUD for villages (desa), waste banks, users, and customers (nasabah)
- Waste categorization with per-bank pricing (buy/sell rates)
- Deposit transactions (TransaksiBeli) with real-time balance updates
- Sales transactions to collectors (TransaksiJual)
- Withdrawal request workflow (PengajuanPencairan)
- Balance mutation tracking (MutasiSaldo) for full audit trail
- Financial reports and analytics
- PDF report generation (TCPDF)
- Audit logging and notification system

**Constraints:**
- PHP 7.3+ compatibility required (no PHP 8.x features)
- Single-bank-per-deployment architecture (no multi-bank SaaS in current version)
- Authentication via phone number (no email-based auth)
- Manual cash handling outside the system

## Brand Commitments

- **Name:** "Bank Sampah Digital Desa" or similar
- **Colors:** Green primary palette (#2e7d32, #4caf50) reflecting environmental/sustainability theme
- **Typography:** Segoe UI system font for maximum compatibility
- **Language:** Bahasa Indonesia throughout
- **Target:** Mobile-first web access, usable on low-end smartphones

## Evidence on Hand

- Complete database schema (16 tables) with proper foreign keys and indexes
- Full CRUD implementation for all major modules
- Role-based routing and session management
- Sample data including test accounts with phone number + password login
- PROGRESS.md documenting all 8 development phases completed
- Existing CSS variables and design tokens in style.css

## Product Principles

1. **Zero门槛** — Interface must be usable by community members with basic smartphone literacy
2. **Full traceability** — Every balance change has a corresponding MutasiSaldo record
3. **Village ownership** — Self-hosted, no vendor lock-in, data stays with the village government
4. **Operational fidelity** — The system models the real waste bank workflow, not a generic ledger
5. **Reliability over features** — Core deposit → balance → withdrawal flow must be bulletproof

## Accessibility & Inclusion

- Responsive design for low-end smartphones (320px+ viewport)
- High contrast text on light backgrounds
- Large touch targets for waste bank environment (gloved hands)
- Bahasa Indonesia only (no English required)
