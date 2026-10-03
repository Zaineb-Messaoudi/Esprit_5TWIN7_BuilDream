<?php

it('redirects guests to the sign-in page', function () {
    $this->get('/')->assertRedirect(route('login'));
});
