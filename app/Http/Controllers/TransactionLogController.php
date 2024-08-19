<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionLogController extends Controller
{
    public function viewTransactionLog()
    {
        return view('admin.transaction-log');
    }
}
