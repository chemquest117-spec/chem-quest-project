<?php

it('exposes a csrf refresh endpoint for long-lived tabs', function () {
    $res = $this->get(route('csrf.refresh'));

    $res->assertOk();
    $res->assertJsonStructure(['token']);
    expect($res->json('token'))->toBeString()->not->toBeEmpty();
});
