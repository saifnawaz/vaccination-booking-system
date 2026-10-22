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

## Phase 5: brand and polish

15. **Emerald for text is one step darker than the brand emerald.** White on `#1F8A5B` is
    4.33:1, just under the 4.5:1 WCAG AA needs for normal text, and emerald links on sage
    are 3.85:1. Buttons, links and active navigation therefore use `#1A754D` (5.7:1 with
    white, 5.0:1 on sage). The brand `#1F8A5B` is used where 3:1 is enough: focus rings,
    icons, chart bars, borders and the logo. Token: `--color-primary-700`.
16. **Mustard is never text.** `#D4A72C` on white is 2.2:1. It is used as a fill under
    forest text (5.4:1), as a tint behind dark mustard text `#6A5416` (6.5:1), and for the
    unread-count badge.
17. **Light emerald `#2FB37A` is only used for progress bars that always have a text
    label next to them** ("11/19 doses done"). The bar itself is 2.7:1 on white, below the
    3:1 for meaningful graphics, so it never carries information on its own.
18. **Red is reserved** for overdue doses, errors and destructive actions. So a *declined*
    appointment is grey, a *missed appointment* (no-show) is mustard, and the unread
    badge is mustard, not red. An *expired* stock batch is red, as an error state
    (it can't be used).
19. **"Upcoming" doses are mustard**, as specified ("due/upcoming = mustard"). Due and
    upcoming are told apart by their icon (bell vs clock) and label.
20. **The forest colour is used for the top header; there is no sidebar.** The brief
    mentions a sidebar, but every role already had a top header (and parents a bottom tab
    bar on phones). Moving admin and hospital to a sidebar layout would be a bigger
    restructure than a polish pass; easy to do later if wanted.
21. **Cards are sage on a white page; fields, tables, menus and dialogs stay white.**
22. **The logo's multiply blend only applies between its own bars** (`isolation: isolate`),
    so the overlaps darken as designed and the mark stays visible on the dark forest header.
    Each bar also carries an SVG `transform` attribute, so the mark renders correctly where
    the app's CSS isn't loaded (the certificate, the PDF and the favicon).
23. **Under `prefers-reduced-motion` the loading spinner keeps a gentle fade** instead of
    rotating; everything else (page and card fades, slides, lifts, the logo intro,
    skeleton shimmer) is switched off.
24. **Skeleton loaders appear when a list is reloading** (submitting a filter, following
    a pagination link or a filter tab). Pages are server-rendered, so there is no skeleton
    on the very first load.
25. **Admin and hospital tables become stacked cards on phones** (`.table-stack`), with each
    cell labelled, instead of scrolling sideways.
26. **Phone screenshots show one screen** (390×844), as a parent sees it; desktop
    screenshots are full-page. A full-page phone capture paints the fixed bottom tab bar
    halfway down the image.
27. **The mail sender is `hello@tika.test`** (a reserved test domain), matching the app name.
