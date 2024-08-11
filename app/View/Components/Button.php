<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $type = "button",
        public string $href = "",
        public string $class = "",
        public string $size = "md",
        public string $tint = "bg-primary text-white hover:bg-primary-dark",
        public string $attrs = ""
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.button');
    }
}
