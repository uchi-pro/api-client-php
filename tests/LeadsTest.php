<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Identity;
use UchiPro\Leads\Lead;
use UchiPro\Users\User;

class LeadsTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    public function setUp()
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        if (!empty($accessToken)) {
            $this->identity = Identity::createByAccessToken($url, $accessToken);
        } else {
            $this->identity = Identity::createByLogin($url, $login, $password);
        }
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create($this->identity);
    }

    public function testSaveMinimalLead()
    {
        $leadsApi = $this->getApiClient()->leads();

        $stamp = date('YmdHms');

        $lead = new Lead();
        $lead->email = "u{$stamp}@uchi.pro";

        $lead = $leadsApi->save($lead);
        $this->assertNotEmpty($lead->id);
    }

    public function testSaveFullLead()
    {
        $me = $this->getApiClient()->users()->getMe();

        $leadsApi = $this->getApiClient()->leads();

        $stamp = date('YmdHms');

        $lead = new Lead();
        $lead->number = "{$stamp}";
        $lead->contactPerson = "Гражданин {$stamp}";
        $lead->email = "u{$stamp}@uchi.pro";
        $lead->phone = "+7{$stamp}";
        $lead->courses = $this->selectMyCoursesForLead($me);

        $lead = $leadsApi->save($lead);
        $this->assertNotEmpty($lead->id);
    }

    /**
     * @param User $me
     *
     * @return array|Course[]
     */
    private function selectMyCoursesForLead(User $me)
    {
        $coursesApi = $this->getApiClient()->courses();
        $courses = [];
        $i = 0;
        $coursesCriteria = $coursesApi->createCriteria();
        $coursesCriteria->vendor = $me->vendor;
        foreach ($coursesApi->findBy($coursesCriteria) as $course) {
            $courses[] = $course;
            $i++;
            if ($i === 3) {
                break;
            }
        }
        return $courses;
    }
}
