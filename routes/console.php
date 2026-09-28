<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
*/

// Mark sent invoices as overdue if due_date has passed
Schedule::command('invoices:mark-overdue')->daily();