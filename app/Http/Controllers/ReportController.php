<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TasksExport;

class ReportController extends Controller
{
    public function exportTasks(Request $request)
    {
        $status = $request->query('status');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        return Excel::download(
            new TasksExport($status, $start, $end),
            'tasks_report.xlsx'
        );
    }
}
