<?php

namespace App\Http\Controllers;

use App\Models\MaidsDB;
use App\Models\CustomerSurviMaid;
use Illuminate\Http\Request;

class SurveyMaidController extends Controller
{
    /**
     * Display the maid survey form.
     */
    public function show($maidId, Request $request)
    {
        $maid = MaidsDB::findOrFail($maidId);

        // Get ?customer=1 from URL
        $customerId = $request->query('customer', null);

        return view('ERP.maids.survey', [
            'maid' => $maid,
            'SAT'  => CustomerSurviMaid::SATISFACTION_MAP,
            'PERF' => CustomerSurviMaid::PERF_MAP,
            'customer_id' => $customerId,
        ]);
    }


/**
 * Store the survey results.
 */
/**
 * Store the survey results.
 */
public function store(Request $request, $maidId)
{
    $maid = MaidsDB::findOrFail($maidId);

    $validated = $request->validate([
        'customer_id'         => ['required', 'integer', 'exists:customers,id'],
        'satisfied'           => ['required', 'integer', 'in:1,2,3,4,5'],
        'perf_cleaning'       => ['required', 'integer', 'in:0,2,3,4,5'],
        'perf_cooking'        => ['required', 'integer', 'in:0,2,3,4,5'],
        'perf_childcare'      => ['required', 'integer', 'in:0,2,3,4,5'],
        'perf_communication'  => ['required', 'integer', 'in:0,2,3,4,5'],
        'note'                => ['nullable', 'string', 'max:1000'],
    ]);

    $validated['maid_id'] = $maid->id;

    CustomerSurviMaid::create($validated);

    // ✅ Return simple HTML response
    return response()->make("
        <html>
            <head>
                <title>Thank You</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background: #f5f5f5;
                        padding: 40px;
                        text-align: center;
                    }
                    .box {
                        background: white;
                        padding: 30px;
                        border-radius: 12px;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                        max-width: 400px;
                        margin: 80px auto;
                    }
                    h2 { color: #28a745; margin-bottom: 10px; }
                    p  { color: #333; font-size: 16px; }
                </style>
            </head>
            <body>
                <div class='box'>
                    <h2>✅ Thank You!</h2>
                    <p>Your feedback has been submitted successfully.</p>
                </div>
            </body>
        </html>
    ");
}


public function reviews(MaidsDB $maid)
{
    $reviews = CustomerSurviMaid::with('customer')
        ->where('maid_id', $maid->id)
        ->latest('id')
        ->paginate(15);

    $stats = CustomerSurviMaid::where('maid_id', $maid->id)
        ->selectRaw('
            COUNT(*) as votes,
            AVG(satisfied) as avg_satisfied,
            AVG(perf_cleaning) as avg_cleaning,
            AVG(perf_cooking) as avg_cooking,
            AVG(perf_childcare) as avg_childcare,
            AVG(perf_communication) as avg_communication
        ')
        ->first();

    return view('ERP.maids.reviews', compact('maid', 'reviews', 'stats'));
}


} 
