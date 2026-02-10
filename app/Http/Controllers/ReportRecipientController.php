<?php

namespace App\Http\Controllers;

use App\Models\ReportRecipient;
use Illuminate\Http\Request;

class ReportRecipientController extends Controller
{
    public function index()
    {
        $recipients = ReportRecipient::orderBy('email')->get();
        return view('ERP.report_recipients', compact('recipients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:report_recipients,email',
            'report_type' => 'required|string',
        ]);

        ReportRecipient::create([
            'email' => $request->email,
            'report_type' => $request->report_type,
            'is_active' => true,
        ]);

        return back()->with('success', 'Recipient added successfully.');
    }

    public function toggle(ReportRecipient $recipient)
    {
        $recipient->update([
            'is_active' => !$recipient->is_active
        ]);

        return back()->with('success', 'Recipient status updated.');
    }

    public function destroy(ReportRecipient $recipient)
    {
        $recipient->delete();
        return back()->with('success', 'Recipient removed successfully.');
    }
}
