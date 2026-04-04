<?php

test('architecture')->expect('App\Http\Controllers')->toHaveSuffix('Controller');
test('globals')->expect(['dd', 'dump', 'var_dump'])->not->toBeUsed();
test('models')->expect('App\Models')->toExtend('Illuminate\Database\Eloquent\Model');
