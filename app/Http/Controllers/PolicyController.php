<?php

namespace App\Http\Controllers;

use App\Http\Requests\attendance\RequestAttendance;
use App\Http\Requests\policy\RequestPolicy;
use App\Models\Loan;
use App\Models\OnlineAttendance;
use App\Models\OfficeDivision;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Exception;
use Intervention\Image\Facades\Image;
use ZipStream\File;

class PolicyController extends Controller
{
    protected $viewPath = 'policy';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Policy $policy
     */
    public function create()
    {
        $orderNumber = Policy::where('status', 1)->count();
        $orderNumber++;

        return view("policy.create", compact('orderNumber', $orderNumber));
    }

    /**
     * @return Factory|View
     * @throws Exception
     */
    public function index(Request $request)
    {
        $data = [];

        if (request()->ajax()) {

            $policies = Policy::where('status', 1)->orderBy("order_no", "desc")->orderBy("id", "desc");

            return datatables($policies)
                ->addColumn('action', function ($policy) use ($request) {
                    $html = '';

                    if (auth()->user()->can("View Policy")) {
                        $html .= '<a data-rowid="' . $policy->id . '"  title="Show" href="' . route('policies.show', $policy->id) . '"><i class="fa fa-eye view_icon"></i></a>';
                    }
                    if (auth()->user()->can("Edit Policy")) {
                        $html .= '<a data-rowid="' . $policy->id . '" title="Edit" href="' . route('policies.edit', $policy->id) . '"><i class="fa fa-edit view_icon"></i></a>';
                    }

                    return $html;
                })
                ->editColumn('attachment', function ($policy) use ($request) {
                    $html = '';
                    if ($policy->attachment) {
                        if (auth()->user()->can("View Policy")) {
                            $html .= '<a href="' . asset('storage/'.$policy->attachment) . '" data-title="' . $policy->title . '" class="file-link bold" data-toggle="modal" data-target="#file-modal">Show</a>';
                        }
                    }
                    return $html;
                })
                ->rawColumns(['title', 'attachment', 'action'])
                ->make(true);
        }
        return view($this->viewPath . ".index", compact('data'));
    }

    /**
     * @param Policy $policy
     * @return Factory|View
     */
    public function edit(Policy $policy)
    {
        return view($this->viewPath . ".edit", compact("policy", "policy"));
    }

    public function store(RequestPolicy $request)
    {
        $data = $request->validated();
        $databasePath = null;

        try {
            $path = null;
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $attachmentName = time() . '_' . $attachment->getClientOriginalName();

                $path = $request->file('attachment')->storeAs('hr-policies', $attachmentName);
                $databasePath = str_replace('public/', '', $path);
            }

            $data['attachment'] = $path;
            $data['created_by'] = auth()->id();

            Policy::create($data);

            session()->flash('message', 'Policy Created Successfully');
            $redirect = redirect()->route("policies.index");

        } catch (Exception $exception) {
            $redirect = redirect()->back()->with('error', 'Failed to create policy')->withInput();
        }

        return $redirect;
    }

    /**
     * @param Policy $policy
     */
    public function update(RequestPolicy $request, Policy $policy)
    {
        $data = $request->validated();
        $databasePath = null;
        $existingAttachmentPath = null;

        try {
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $attachmentName = time() . '_' . $attachment->getClientOriginalName();

                $path = $request->file('attachment')->storeAs('hr-policies', $attachmentName);
                if ($path) {
                    $data['attachment'] = $path; # str_replace('public/', '', $path);
                    $existingAttachmentPath = $policy->attachment;
                } else {
                    return back()->with('error', 'Failed to update policy');
                }
            }

            $data['updated_by'] = auth()->id();

            $policy->update($data);

            /*if ($existingAttachmentPath) {
                unlink(storage_path('app/public/'.$existingAttachmentPath));
            }*/

            session()->flash('message', 'Policy Updated Successfully');
            $redirect = redirect()->route("policies.index");

        } catch (Exception $exception) {
            dd($exception->getMessage());
            $redirect = redirect()->back()->with('error', 'Failed to update policy')->withInput();
        }

        return $redirect;
    }

    public function viewDashboardPolicyCard()
    {
        $policies = Policy::where('status', 1)->orderBy("order_no", "asc");
        return view($this->viewPath . ".policy-card", compact('policies'));
    }

    public function show(Policy $policy)
    {
        try {
            if (empty($policy->id)) {
                throw new \Exception("Missing Policy ID!!!");
            }
            $policy = Policy::where('status', 1)->where('id', $policy->id)->first();
            if (empty($policy)) {
                throw new \Exception("Missing Policy Information!!!");
            }
            return view($this->viewPath . ".view", compact('policy'));
        } catch (\Exception $ex) {
            session()->flash("type", "error");
            session()->flash("message", $ex->getMessage());
            return redirect()->back();
        }
    }
}
