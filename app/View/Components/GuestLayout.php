<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * @param  string|null  $title  Page name for the browser tab, e.g. "Log in".
     * @param  bool  $wide  Wider card, for longer forms like hospital registration.
     */
    public function __construct(public ?string $title = null, public bool $wide = false) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
