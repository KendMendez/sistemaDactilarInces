<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('inasistencias:generar')->dailyAt('18:00');
