<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-orphaned-tokens')->daily();
