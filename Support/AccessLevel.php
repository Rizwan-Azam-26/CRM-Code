<?php

namespace App\Support;

use App\Model\User;

use App\Repositories\UserRepository;

/**
 * Class Response
 * @package App\Support
 */
class AccessLevel
{
    private $userRepo;
    private $companyRepo;
    private $groupRepo;

    /**
     * Create a new class instance.
     *
     * @param $response
     * @param $transform
     */
    public function __construct(UserRepository $userRepo, $user_id = null)
    {
        $this->response = $response;
        $this->transform = $transform;
    }
}
