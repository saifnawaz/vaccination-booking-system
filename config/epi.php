<?php

/*
|--------------------------------------------------------------------------
| Routine childhood immunisation schedule (EPI Pakistan)
|--------------------------------------------------------------------------
|
| !!! MUST BE VERIFIED AGAINST THE OFFICIAL EPI PAKISTAN SCHEDULE !!!
|
| This schedule was written from general knowledge for development and demo
| purposes. It has NOT been checked against the current official schedule
| published by the Federal Directorate of Immunization (EPI Pakistan). Before
| this app is used with real children, a qualified person must verify every
| vaccine, dose, age, minimum interval and maximum age below, and update this
| file. The maximum ages (max_age_days) in particular are placeholders.
|
| Loaded into the `vaccines` and `vaccine_schedule` tables by
| Database\Seeders\EpiScheduleSeeder, which is safe to re-run after edits:
|     php artisan db:seed --class=EpiScheduleSeeder
|
| Per dose:
|   dose               1, 2, 3… form a series: dose N needs dose N-1 first, and if
|                      dose N-1 is missed the rest of the series is missed too.
|                      0 is a standalone extra dose (e.g. the OPV birth dose):
|                      it neither needs nor blocks dose 1.
|   age_days           due at this age (days from birth)
|   min_interval_days  minimum gap after the previous dose of the same vaccine
|                      (ignored for dose 1; default config('vaccination.default_min_interval_days'))
|   max_age_days       after this age the dose can no longer be given: "missed"
|                      (null = no upper age limit)
|   grace_days         days after the effective due date before it is "overdue"
|                      (default config('vaccination.default_grace_days'))
|
*/

return [

    'vaccines' => [
        'BCG' => [
            'name' => 'BCG',
            'description' => 'Protects against severe forms of tuberculosis.',
            'doses' => [
                ['dose' => 1, 'label' => 'BCG', 'age_days' => 0, 'max_age_days' => 365],
            ],
        ],

        'HEPB' => [
            'name' => 'Hepatitis B (birth dose)',
            'description' => 'Birth dose protecting against hepatitis B.',
            'doses' => [
                ['dose' => 1, 'label' => 'Hepatitis B birth dose', 'age_days' => 0, 'max_age_days' => 14],
            ],
        ],

        'OPV' => [
            'name' => 'Oral polio vaccine (OPV)',
            'description' => 'Oral drops protecting against poliomyelitis.',
            'doses' => [
                // Dose 0: a standalone birth dose, see "dose" below.
                ['dose' => 0, 'label' => 'OPV-0', 'age_days' => 0, 'max_age_days' => 14],
                ['dose' => 1, 'label' => 'OPV-1', 'age_days' => 42],
                ['dose' => 2, 'label' => 'OPV-2', 'age_days' => 70],
                ['dose' => 3, 'label' => 'OPV-3', 'age_days' => 98],
            ],
        ],

        'PENTA' => [
            'name' => 'Pentavalent (DTP-HepB-Hib)',
            'description' => 'Protects against diphtheria, tetanus, whooping cough, hepatitis B and Hib.',
            'doses' => [
                ['dose' => 1, 'label' => 'Pentavalent-1', 'age_days' => 42],
                ['dose' => 2, 'label' => 'Pentavalent-2', 'age_days' => 70],
                ['dose' => 3, 'label' => 'Pentavalent-3', 'age_days' => 98],
            ],
        ],

        'PCV' => [
            'name' => 'Pneumococcal (PCV10)',
            'description' => 'Protects against pneumococcal pneumonia and meningitis.',
            'doses' => [
                ['dose' => 1, 'label' => 'PCV-1', 'age_days' => 42],
                ['dose' => 2, 'label' => 'PCV-2', 'age_days' => 70],
                ['dose' => 3, 'label' => 'PCV-3', 'age_days' => 98],
            ],
        ],

        'ROTA' => [
            'name' => 'Rotavirus',
            'description' => 'Oral drops protecting against severe rotavirus diarrhoea.',
            'doses' => [
                ['dose' => 1, 'label' => 'Rota-1', 'age_days' => 42, 'max_age_days' => 105],
                ['dose' => 2, 'label' => 'Rota-2', 'age_days' => 70, 'max_age_days' => 240],
            ],
        ],

        'IPV' => [
            'name' => 'Inactivated polio vaccine (IPV)',
            'description' => 'Injected polio vaccine, given alongside OPV.',
            'doses' => [
                ['dose' => 1, 'label' => 'IPV-1', 'age_days' => 98],
                ['dose' => 2, 'label' => 'IPV-2', 'age_days' => 270],
            ],
        ],

        'MR' => [
            'name' => 'Measles-rubella (MR)',
            'description' => 'Protects against measles and rubella.',
            'doses' => [
                ['dose' => 1, 'label' => 'MR-1', 'age_days' => 270],
                ['dose' => 2, 'label' => 'MR-2', 'age_days' => 450],
            ],
        ],

        'TCV' => [
            'name' => 'Typhoid conjugate (TCV)',
            'description' => 'Protects against typhoid fever.',
            'doses' => [
                ['dose' => 1, 'label' => 'TCV', 'age_days' => 270],
            ],
        ],
    ],

];
