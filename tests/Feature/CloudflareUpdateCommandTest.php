<?php

it('cloudflare update', function () {
    $this->artisan('update')->assertExitCode(0);
});
