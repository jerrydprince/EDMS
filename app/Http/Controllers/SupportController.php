<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\Faq;
use App\Models\SupportInfo;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function support()
    {
        return Inertia::render('Support/Support');
    }
    public function sendSupportDetails(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'location' => 'required|string',
            'start_day' => 'required|string',
            'end_day' => 'required|string',
            'start_time' => 'required|date_format:h:i A',
            'end_time' => 'required|date_format:h:i A',
        ]);
        $start_time = Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i:s');
        $end_time = Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i:s');
        $supportInfo = SupportInfo::first();
        if ($supportInfo) {
            $supportInfo->update([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'start_time' => $start_time,
                'end_time' => $end_time,
            ]);
        } else {
            $supportInfo = SupportInfo::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'start_time' => $start_time,
                'end_time' => $end_time,
            ]);
        }
        return  response()->json([
            'message' => 'Support created successfully!',
            'data' => $supportInfo
        ]);
    }
    public function getSupportDetails()
    {
        $supportInfo = SupportInfo::first();

        if ($supportInfo) {
            return response()->json([
                'message' => 'Support details retrieved successfully',
                'data' => $supportInfo
            ]);
        } else {
            return response()->json([
                'message' => 'No support details found',
                'data' => null
            ], 404);
        }
    }
    public function SaveFaqs(Request $request)
    {
        $request->validate([
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'faqs.*.image' => 'nullable|image|mimes:jpeg,png,jpg',
        ]);
        foreach ($request->input('faqs', []) as $index => $faqData) {
            $faq = new Faq();
            $faq->question = $faqData['question'];
            $faq->answer = $faqData['answer'];

            if ($request->hasFile("faqs.$index.image")) {
                $imagePath = $request->file("faqs.$index.image")->store('faq_images', 'public');
                $faq->image = $imagePath;
            }

            $faq->save();
        }

        return response()->json(['message' => 'FAQs saved successfully!']);
    }
    public function getFaqs()
    {
        $faqs = Faq::all();
        return response()->json($faqs);
    }
    public function saveDocumentation(Request $request)
    {
        $request->validate([
            'docs.*.name' => 'required|string',
            'docs.*.link' => 'required|url',
        ]);

        foreach ($request->input('docs', []) as $docData) {
            Documentation::create([
                'name' => $docData['name'],
                'link' => $docData['link'],
            ]);
        }

        return response()->json(['message' => 'Documentation saved successfully!']);
    }

    public function getDocumentations()
    {
        $documentations = Documentation::all();
        return response()->json($documentations);
    }
}
