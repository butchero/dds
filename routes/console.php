<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('revisions:remind')->dailyAt('08:00');
