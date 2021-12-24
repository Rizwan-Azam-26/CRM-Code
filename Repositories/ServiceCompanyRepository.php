<?php

namespace App\Repositories;

use App\Models\ServiceCompany;
use Illuminate\Support\Facades\DB;

class ServiceCompanyRepository
{
    use BaseRepository;

    const BULLETIN_EMAILS = 'email.bulletins';
    const SHARK_TANK = 'sharktank';
    const TRUST_RELEASE_PAYMENT = 'trust.release';
    const EMAIL_SERVICE = 'email';
    const FINANCING_CHARGEBACKS = 'financing.reports.chargebacks';
    const SMS = 'sms';

    /**
     * ServiceCompany Model
     *
     * @var ServiceCompany
     */
    protected $model;

    /**
     * Constructor
     *
     * @param ServiceCompany $serviceCompany
     */
    public function __construct(ServiceCompany $serviceCompany)
    {
        $this->model = $serviceCompany;
    }

    public function findAllByCompany($company_id, $format = 'form')
    {

        $query = DB::table('services_companies')
            ->select('services.*')
            ->leftJoin('services', 'services.id', '=', 'services_companies.service_id')
            ->where('company_id', '=', $company_id)
            ->get();
        $result = $query->toArray();


        if ($result) {
            if ($format == 'form') {

                foreach ($result as $item) {
                    $ids[] = $item['service_id'];
                }
                return $ids;
            } else {
                return $result;
            }
        }

        return false;

    }

    public function upsert($company_id, $service_ids)
    {

        $this->model->where('company_id', '=', $company_id)->delete();


        // Exclude Dupes
        $ids = array_unique($service_ids);

        foreach ($ids as $id) {
            $query = $this->store(array('service_id' => $id, 'company_id' => $company_id));

        }


    }

    /**
     * Get number of the records
     *
     * @param Request $request
     * @param int $number
     * @param string $sort
     * @param string $sortColumn
     * @return Paginate
     */
    public function pageWithRequest($request, $number = 10, $sort = 'desc', $sortColumn = 'created_at')
    {
        $keyword = $request->get('keyword');

        if ($request->has('limit')) {
            $number = $request->get('limit');
        }

        return $this->model->when($keyword, function ($query) use ($keyword) {
            $query->where('title', 'like', "%{$keyword}%");
        })
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
    }

    public function check($key, $company_id)
    {

        $query = DB::table('services_companies')->select('services_companies.*', 'services.key', 'services.name')
            ->leftJoin('services', 'services.id', '=', 'services_companies.service_id');
        $query->where('key', '=', $key)->where('company_id', '=', $company_id);
        $result = $query->first();
        return $result->toArray();
    }

    public function findByServiceIdCompanyId($service_id, $company_id)
    {
        return optional($this->model->where('service_id', '=', $service_id)->where('company_id', '=', $company_id)->get())->toArray();
    }

    public function getCompanyIdsByServiceId($service_key)
    {

        $query = DB::table('services_companies');
        $query->leftJoin('services', 'services.id', '=', 'services_companies.service_id');
        $query->where('services.key', '=', $service_key);
        $result = $query->get()->toArray();

        if ($result) {
            $ids = array();
            foreach ($result as $id) {
                $ids[] = $id['company_id'];
            }
            return $ids;
        }

        return false;
    }

}
