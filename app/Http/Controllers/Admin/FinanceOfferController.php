<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\JobApplication;
class FinanceOfferController extends Controller { public function index(){ $offers=JobApplication::where('finance_offer_status','pending_review')->with(['job.user','candidate','candidateUser'])->latest('offer_updated_at')->paginate(30); return view('admin.finance_offers.index',compact('offers')); } public function approve(JobApplication $application){ $application->update(['finance_offer_status'=>'reviewed','finance_reviewed_at'=>now(),'finance_reviewed_by'=>auth()->id()]); return back()->with('success','Offer reviewed and ready for invoice processing.'); }}
