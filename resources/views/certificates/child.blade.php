{{--
    Vaccination certificate. One template for the print page and the PDF (dompdf),
    so it uses plain CSS only (dompdf supports CSS 2.1, not Tailwind).
    $pdf = true hides the on-screen toolbar.
--}}
@use('App\Enums\DoseStatus')
@php
    $given = $schedule->doses()->filter(fn ($d) => $d->status === DoseStatus::Completed)
        ->sortBy(fn ($d) => $d->dose->administered_at->timestamp)->values();
    $outstanding = $schedule->doses()->filter(fn ($d) => in_array($d->status, [DoseStatus::Due, DoseStatus::Overdue], true));
    $missed = $schedule->doses()->filter(fn ($d) => $d->status === DoseStatus::Missed);
    $historical = $given->contains(fn ($d) => $d->dose->isHistoricalRecord());
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Vaccination certificate · {{ $child->fullName() }} · {{ config('app.name') }}</title>
    <style>
        @page { margin: 18mm 16mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: #1d2a29; font-size: 11pt; line-height: 1.45; margin: 0; background: #fff; }
        .sheet { max-width: 800px; margin: 0 auto; padding: 24px; }
        .toolbar { max-width: 800px; margin: 16px auto 0; padding: 0 24px; display: flex; gap: 8px; }
        .toolbar a, .toolbar button { font: inherit; font-size: 10pt; font-weight: bold; padding: 8px 14px; border-radius: 8px; border: 1px solid #8c8273; background: #fff; color: #1d2a29; text-decoration: none; cursor: pointer; }
        .toolbar .primary { background: #1b5b56; border-color: #1b5b56; color: #fff; }
        header { border-bottom: 3px solid #1b5b56; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 10pt; font-weight: bold; color: #1b5b56; letter-spacing: 0.04em; text-transform: uppercase; }
        h1 { font-size: 20pt; margin: 4px 0 0; }
        h2 { font-size: 12pt; margin: 22px 0 8px; color: #1b5b56; }
        .details { width: 100%; border-collapse: collapse; }
        .details td { padding: 3px 12px 3px 0; vertical-align: top; }
        .details td.label { color: #56625f; width: 130px; }
        table.doses { width: 100%; border-collapse: collapse; font-size: 10pt; }
        table.doses th { text-align: left; background: #f4efe7; color: #1d2a29; padding: 7px 8px; border-bottom: 1px solid #cfc5b6; }
        table.doses td { padding: 7px 8px; border-bottom: 1px solid #e6dfd4; vertical-align: top; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 9pt; }
        .note { display: inline-block; margin-top: 2px; font-size: 8.5pt; color: #7a5205; background: #fdf5e1; border: 1px solid #faeac2; border-radius: 4px; padding: 1px 5px; }
        .muted { color: #56625f; }
        .list { margin: 0; padding-left: 18px; }
        footer { margin-top: 28px; padding-top: 10px; border-top: 1px solid #e6dfd4; font-size: 8.5pt; color: #56625f; }
        @media print { .toolbar { display: none; } .sheet { padding: 0; } }
    </style>
</head>
<body>
    @unless ($pdf ?? false)
        <div class="toolbar">
            <button type="button" class="primary" onclick="window.print()">Print</button>
            <a href="{{ route('parent.children.certificate.pdf', $child) }}">Download PDF</a>
            <a href="{{ route('parent.children.show', $child) }}">Back to schedule</a>
        </div>
    @endunless

    <div class="sheet">
        <header>
            <div class="brand">{{ config('app.name') }}</div>
            <h1>Vaccination certificate</h1>
        </header>

        <table class="details">
            <tr><td class="label">Child</td><td><strong>{{ $child->fullName() }}</strong></td></tr>
            <tr><td class="label">Date of birth</td><td>{{ $child->date_of_birth->format('j F Y') }} ({{ $child->ageLabel() }})</td></tr>
            <tr><td class="label">Gender</td><td>{{ $child->gender->label() }}</td></tr>
            @if ($child->blood_group)
                <tr><td class="label">Blood group</td><td>{{ $child->blood_group }}</td></tr>
            @endif
            <tr><td class="label">Parent</td><td>{{ $child->parent->name }}</td></tr>
            <tr><td class="label">Issued</td><td>{{ now()->format('j F Y') }}</td></tr>
        </table>

        <h2>Vaccines given ({{ $given->count() }})</h2>
        @if ($given->isEmpty())
            <p class="muted">No vaccines have been recorded yet.</p>
        @else
            <table class="doses">
                <thead>
                    <tr><th>Date</th><th>Vaccine</th><th>Given at</th><th>Batch</th></tr>
                </thead>
                <tbody>
                    @foreach ($given as $dose)
                        <tr>
                            <td>{{ $dose->dose->administered_at->format('j M Y') }}</td>
                            <td>
                                <strong>{{ $dose->label() }}</strong><br>
                                <span class="muted">{{ $dose->schedule()->vaccine->name }}</span>
                            </td>
                            <td>
                                {{ $dose->dose->administeredBy?->name ?? 'Not recorded' }}
                                @if ($dose->dose->isHistoricalRecord())
                                    <br><span class="note">Recorded from previous records</span>
                                @endif
                            </td>
                            <td class="mono">{{ $dose->dose->batch_number ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($historical)
                <p class="muted" style="font-size: 9pt; margin-top: 6px;">
                    "Recorded from previous records" means the dose was copied by the named hospital from an earlier vaccination record, not given through {{ config('app.name') }}.
                </p>
            @endif
        @endif

        @if ($outstanding->isNotEmpty())
            <h2>Still to be given</h2>
            <ul class="list">
                @foreach ($outstanding as $dose)
                    <li>{{ $dose->label() }} ({{ strtolower($dose->status->label()) }})</li>
                @endforeach
            </ul>
        @endif

        @if ($missed->isNotEmpty())
            <h2>No longer possible</h2>
            <p class="muted" style="margin: 0 0 4px;">These doses were not given before their age limit.</p>
            <ul class="list">
                @foreach ($missed as $dose)
                    <li>{{ $dose->label() }}</li>
                @endforeach
            </ul>
        @endif

        <footer>
            Generated by {{ config('app.name') }} on {{ now()->format('j F Y, g:i a') }}.
            The routine schedule in this app is demonstration data and must be verified against the official EPI Pakistan schedule.
            Always check with your doctor or vaccination centre.
        </footer>
    </div>
</body>
</html>
