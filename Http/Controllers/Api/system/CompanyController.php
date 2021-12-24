<?php
namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyRequest;

use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Transformers\CompanyTransformer;
use App\Events\CompanyCreated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanyController extends BaseController
{
    protected $company;

    public function __construct(CompanyRepository $company)
    {
        parent::__construct();

        $this->company = $company;
    }

    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->company->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->company->getCompaniesPaginate($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }else{
            $data = array_merge($request->all(), [
                'account_id' => 1
            ]);

            // $data = $request->all();

            $company = $this->company->store($data);
            event(new CompanyCreated($company));

            if ($request->has('accounting_types_ids')) {
                $accounting_types_ids = $request->input('accounting_types_ids');
                if (isset($accounting_types_ids) && count($accounting_types_ids) > 0) {
                    $company->accounting_types()->sync($accounting_types_ids);
                }
            }

            if ($request->has('payment_schedule_types_ids')) {
                $payment_schedule_types_ids = $request->input('payment_schedule_types_ids');
                if (isset($payment_schedule_types_ids) && count($payment_schedule_types_ids) > 0) {
                    $company->payment_schedule_types()->sync($payment_schedule_types_ids);
                }
            }

            if ($request->has('document_templates_ids')) {
                $document_templates_ids = $request->input('document_templates_ids');
                if (isset($document_templates_ids) && count($document_templates_ids) > 0) {
                    $company->document_templates()->sync($document_templates_ids);
                }
            }

            if ($request->has('services_ids')) {
                $services_ids = $request->input('services_ids');
                if (isset($services_ids) && count($services_ids) > 0) {
                    $company->services()->sync($services_ids);
                }
            }

            return $this->response->withNoContent();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = $this->company->getById($id);
        return $this->response->item($company);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = array_merge($request->all(), [
            'updated_by' => Auth::id()
        ]);
        $company = $this->company->update($id, $data);

        if ($request->has('accounting_types_ids')) {
            $accounting_types_ids = $request->input('accounting_types_ids');
            if (isset($accounting_types_ids) && count($accounting_types_ids) > 0) {
                $company->accounting_types()->sync($accounting_types_ids);
            }
        }

        if ($request->has('payment_schedule_types_ids')) {
            $payment_schedule_types_ids = $request->input('payment_schedule_types_ids');
            if (isset($payment_schedule_types_ids) && count($payment_schedule_types_ids) > 0) {
                $company->payment_schedule_types()->sync($payment_schedule_types_ids);
            }
        }

        if ($request->has('document_templates_ids')) {
            $document_templates_ids = $request->input('document_templates_ids');
            if (isset($document_templates_ids) && count($document_templates_ids) > 0) {
                $company->document_templates()->sync($document_templates_ids);
            }
        }

        if ($request->has('services_ids')) {
            $services_ids = $request->input('services_ids');
            if (isset($services_ids) && count($services_ids) > 0) {
                $company->services()->sync($services_ids);
            }
        }

        return $this->response->withNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->company->destroy($id);

        return $this->response->withNoContent();
    }

}
