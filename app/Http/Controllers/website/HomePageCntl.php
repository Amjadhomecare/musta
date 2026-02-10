<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomePage;
use Illuminate\Support\Facades\Log;

class HomePageCntl extends Controller
{
    public function index()
    {
        
        $data = HomePage::firstOrCreate(['id' => 'home_page']);

       
        foreach (['metadata', 'hero_section', 'visa_section', 'hire_maid_section', 'direct_sponsorship_section', 'about_section', 'reviews_section', 'qa_section'] as $section) {
            $data->$section = json_decode($data->$section ?? '{}', true);
        }

        return view("website.homepage", compact('data'));
    }

    public function update(Request $request)
    {
       
        $validatedData = $request->validate([
            'metadata' => 'nullable|array',
            'hero_section' => 'nullable|array',
            'visa_section' => 'nullable|array',
            'hire_maid_section' => 'nullable|array',
            'direct_sponsorship_section' => 'nullable|array',
            'about_section' => 'nullable|array',
            'reviews_section' => 'nullable|array',
            'qa_section' => 'nullable|array',
        ]);

      
        $homePage = HomePage::firstOrCreate(['id' => 'home_page']);

   
        foreach ($validatedData as $key => $value) {
            $homePage->$key = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $homePage->save();

        return redirect()->back()->with('success', 'Home Page updated successfully.');
    }
  


}
