<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Identity;
use UchiPro\Users\User;

class LeadsTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    public function setUp(): void
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        $this->identity = !empty($accessToken)
          ? Identity::createByAccessToken($url, $accessToken)
          : Identity::createByLogin($url, $login, $password);
    }

    public function getApiClient(): ApiClient
    {
        return ApiClient::create($this->identity);
    }

    public function testSaveMinimalLead()
    {
        $leadsApi = $this->getApiClient()->leads();

        $stamp = date('YmdHms');

        $lead = $leadsApi->createLead();
        $lead->email = "u$stamp@uchi.pro";

        $lead = $leadsApi->save($lead);
        $this->assertNotEmpty($lead->id);
    }

    public function testSaveFullLead()
    {
        $me = $this->getApiClient()->users()->getMe();

        $leadsApi = $this->getApiClient()->leads();

        $stamp = date('YmdHms');

        $lead = $leadsApi->createLead();
        $lead->number = "$stamp";
        $lead->contactPerson = "Гражданин $stamp";
        $lead->email = "u$stamp@uchi.pro";
        $lead->phone = "+7$stamp";
        $lead->courses = $this->selectMyCoursesForLead($me);

        $mainComment = $leadsApi->createComment(null, 'Первый комментарий.');
        $lead = $leadsApi->save($lead, $mainComment);

        $additionalComment = $leadsApi->createComment(null, "Дополнительный комментарий.\nДля тестирования переносов.");
        $additionalComment = $leadsApi->saveLeadComment($lead, $additionalComment);

        $this->assertNotEmpty($additionalComment->id);
    }

    /**
     * @param User $me
     *
     * @return array|Course[]
     */
    private function selectMyCoursesForLead(User $me): array
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
