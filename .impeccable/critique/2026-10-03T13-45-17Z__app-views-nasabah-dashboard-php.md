---
target: Nasabah dashboard (app/views/nasabah/dashboard.php)
total_score: 21
max_score: 36
na_heuristics: 7
p0_count: 1
p1_count: 4
target_identity: "file:C:\\xampp\\htdocs\\belajar_ai\\app-views-nasabah-dashboard-php"
timestamp: 2026-10-03T13-45-17Z
slug: app-views-nasabah-dashboard-php
---
# Critique Report: app/views/nasabah/dashboard.php

## Design Health Score
| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 3 | Flash messages and badges work; no loading states or withdrawal progress indicator |
| 2 | Match System / Real World | 3 | Bahasa Indonesia and emoji anchors work; "KOREKSI" may confuse some users |
| 3 | User Control and Freedom | 2 | Navigation works; logout blends into sidebar; no breadcrumb after drilling in |
| 4 | Consistency and Standards | 2 | Sidebar gradients differ across roles; inline `<style>` blocks vs shared CSS classes |
| 5 | Error Prevention | 2 | Pencairan validated; no guard on concurrent active withdrawal requests |
| 6 | Recognition Rather Than Recall | 2 | Sidebar nav visible; SETOR buried; no recent-activity summary |
| 7 | Flexibility and Efficiency | n/a | Operate/transaction surface — power-user features not expected |
| 8 | Aesthetic and Minimalist Design | 3 | Village Green appropriate; equal-weight saldo cards lack hierarchy |
| 9 | Error Recovery | 2 | Flash messages present; form draft not preserved; no retry guidance |
| 10 | Help and Documentation | 1 | No tooltips, no onboarding, no contextual help, no empty-state guidance |
| **Total** | | **21/36** | **Acceptable** |

## Design Specificity
- LLM verdict: Category-interchangeable dashboard structure; Village Green aesthetic appropriate but composition not product-specific
- Detector: 66 advisory findings — systematic inline style pollution, CSS variables unused, alert colors undocumented

## Priority Issues
1. [P0] No prominent Setor Sampah CTA — dashboard shows numbers, invites no action
2. [P1] Sidebar gradient differs across every role dashboard (3 different implementations)
3. [P1] Every view has its own `<style>` block with literal colors — CSS variables unused
4. [P1] Logout link fails WCAG AA contrast (#ffcdd2 on #2e7d32 = ~3.5:1)
5. [P1] Empty state is cold and unhelpful for first-time users
6. [P2] Amount sign colors use inline `style=` instead of CSS classes
7. [P2] Alert tonal colors undocumented in DESIGN.md

## Persona Red Flags
- Nasabah: No prominent action, no empty state guidance, no withdrawal confirmation
- Sam: Missing ARIA labels, emoji-only status, color-only amount meaning
- Casey: CTA below fold on mobile, no connection error handling, logout invisible on small screens

## Provocative Questions
- What if the hero was balance + "Setor Sekarang" + last deposit date?
- Should core actions be surfaced above the fold, with settings in sidebar?
- Is quiet logout appropriate for shared-device village contexts?
