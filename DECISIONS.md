# Decisions

Choices made while building Phases 4 and 5 that would normally have been a question.
Each one is easy to change; the file or setting to look at is given.

## Phase 3 follow-ups (shipped with Phase 4)

1. **Shipped in the Phase 4 commit.** Hospital deactivation and the hourly job both
   notify parents and write to the activity log, which are Phase 4 features, so the
   three follow-ups are committed together with Phase 4 rather than on their own.
2. **The 2-hour minimum notice is enforced on the server too**, not only hidden in the
   booking list, so a hand-made request can't book a slot that's too soon. It also
   applies to rescheduling. Setting: `config/vaccination.php` →
   `booking.min_notice_minutes` (default 120).
3. **"Decline" a pending hospital also runs the deactivation clean-up.** Any move to
   `inactive` cancels open appointments. Pending hospitals can't have appointments,
   so in practice this only matters for deactivating an active hospital.
4. **System cancellations skip the parent's 24-hour rule** (the rule is about what a
   parent can change online, not what the system does). They are logged with the
   causer "System".

## Notifications

5. **In-app (database) notifications only**, no email or SMS. Mail is configured to
   the log driver, and there is no mail provider for the demo.
6. **Parents are not notified about cancellations they make themselves.** They are
   notified when a hospital confirms, declines or marks a visit missed, and when the
   system cancels a booking (hospital deactivated, never confirmed).
7. **Reminders run daily at 08:00** (`reminders:send`) and are sent **once per dose per
   kind**: one "due within 7 days" and, if it becomes overdue, one "overdue". A child's
   doses are grouped into one notification. Doses with an open appointment are skipped.
   The seeder also runs reminders once, so demo parents have notifications to look at.
8. **The bell is shown to every role.** Only parents receive notifications today; the
   bell is there for hospitals and admins so future notifications (e.g. "new booking")
   need no layout change.

## Admin dashboard

9. **"Doses given this month" counts doses given through the app only**, not doses
   copied from previous records, which were given earlier and elsewhere.
10. **Overdue doses are counted by walking every child's schedule**, because statuses
    are worked out from dates and never stored. That's instant at demo scale (21
    children); with tens of thousands of children it would need a nightly snapshot
    table. See `App\Services\AdminStats::overdue()`.
11. **Charts are plain HTML/CSS bars, one hue, no legend** (each chart has a single
    series, so the title names it). Each bar is keyboard-focusable with a tooltip, and
    every chart has a "Show as table" view. The app has no dark mode, so there is no
    dark chart palette either.

## Certificate

12. **Only the parent can open a child's certificate.** Hospitals and admins get 403;
    hospitals already see the child's record on their appointment page.
13. **The PDF embeds the DejaVu Sans font** (about 1.1 MB per file) so names with
    non-Latin characters render correctly.

## Lists

14. **The children list only shows its search box when a parent has more than 3
    children**; it is always paginated (12 per page).
