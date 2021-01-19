<?php

namespace Rashidul\Hailstorm\JavaScript;

interface ViewBinder
{
    /**
     * Bind the JavaScript variables to the view.
     *
     * @param string $js
     */
    public function bind($js);
}
